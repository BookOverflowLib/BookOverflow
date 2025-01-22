<?php

/**
 * Assicura che la sessione sia attiva
 * @return void
 */
function ensure_session()
{
	if (!isset($_SESSION)) {
		session_start();
	}
}

/**
 * Genera una stringa HTML per visualizzare il rating sotto forma di stelle
 *
 * @param float $rating Valore del rating (compreso tra 0.0 e 5.0)
 * @return string HTML con gli svg delle stelle
 * @throws Exception Se il rating non è compreso tra 0 e 5
 */
function ratingStars($rating): string
{
	if ($rating > 5 || $rating < 0) {
		throw new Exception('Rating non nei vincoli', 1);
	}

	$n_full_star = floor($rating); //PARTE INTERA
	$n_partial_star = $rating - $n_full_star; //PARTE FRAZIONARIA
	$star_svg = file_get_contents('../public/assets/imgs/star.svg');

	$total_star = 5;
	$rating_stars = '';

	// STELLE PIENE
	for ($i = 0; $i < $n_full_star; $i++) {
		$tmp_star = str_replace('{{star-offset}}', '100', $star_svg);
		$tmp_star = str_replace('{{id}}', '1', $tmp_star);

		$rating_stars .= $tmp_star;
		$total_star--;
	}

	// STELLA PERCENTUALE
	$par_star = str_replace(
		'{{star-offset}}',
		strval($n_partial_star * 100),
		$star_svg
	);
	$par_star = str_replace('{{id}}', '2', $par_star);
	$rating_stars .= $par_star;
	$total_star--;

	//STELLE VUOTE
	while ($total_star > 0) {
		$tmp_star = str_replace('{{star-offset}}', '0', $star_svg);
		$tmp_star = str_replace('{{id}}', '3', $tmp_star);
		$rating_stars .= $tmp_star;
		$total_star--;
	}

	return $rating_stars;
}

/**
 * Genera la pagina HTML con il template
 * aggiunge header, breadcrumb e footer
 * aggiorna il titolo della pagina
 *
 * @param string $title Titolo della pagina (se null, viene messo "BookOverflow")
 * @return string HTML della pagina
 */
function getTemplatePage($title = null): string
{
	$template = file_get_contents(
		$GLOBALS['TEMPLATES_PATH'] . 'templatePage.html'
	);
	$header = getHeaderSection($_SERVER['REQUEST_URI']);
	$breadcrumb = getBreadcrumb($_SERVER['REQUEST_URI']);
	$footer = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'footer.html');

	$PAGE_TITLE = '';
	if ($title == null) {
		$PAGE_TITLE = 'BookOverflow';
	} else {
		$PAGE_TITLE = ucfirst($title) . ' - BookOverflow';
	}

	$page = str_replace('<!-- [pageTitle] -->', $PAGE_TITLE, $template);

	$page = str_replace('<!-- [header] -->', $header, $page);
	$page = str_replace('<!-- [breadcrumb] -->', $breadcrumb, $page);
	$page = str_replace('<!-- [footer] -->', $footer, $page);

	return $page;
}

/**
 * Genera gli elementi \<li\> della navbar rimuovendo il link dalla pagina corrente (circolari!)
 *
 * @return string HTML contenente i vari elementi \<li\>
 */
function getNavBarLi($path): string
{
	$currentPage = $path;

	$navbarReferences = [
		['href' => '/', 'text' => 'Home'],
		['href' => '/esplora', 'text' => 'Esplora'],
		['href' => '/come-funziona', 'text' => 'Come funziona'],
	];

	$li = '';
	foreach ($navbarReferences as $ref) {
		if ($currentPage != $ref['href']) {
			$li .=
				'<li><a href="' .
				$ref['href'] .
				'">' .
				$ref['text'] .
				'</a></li>';
		} else {
			$li .= '<li class="activePage">' . $ref['text'] . '</li>';
		}
	}
	return $li;
}

function getHeaderButtons($path): string
{
	$scura =
		'<span class="active"><img class="theme-icon" src="/assets/imgs/moon.svg" alt="" aria-hidden="true"><span class="visually-hidden">Modalità scura</span></span>';
	$chiara =
		'<span><img class="theme-icon" src="/assets/imgs/sun.svg" alt="" aria-hidden="true"><span class="visually-hidden">Modalità chiara</span></span>';
	$themeToggleButton =
		'<button class="theme-toggle" aria-pressed="false">' .
		$chiara .
		$scura .
		'</button>';

	// Se la pagina corrente è /accedi, il pulsante deve portare a /registrati
	$accediButton = '';
	if ($path != '/accedi') {
		$accediButton = '<a class="button-layout" href="/accedi">Accedi</a>';
	} else {
		$accediButton =
			'<a class="button-layout" href="/registrati">Registrati</a>';
	}

	$ris = '';
	ensure_session();

	if (isset($_SESSION['user'])) {
		$ris = <<<HTML
			<div class="header-buttons">
				{$themeToggleButton}
				<a class="profile-button" href="/profilo/{$_SESSION['user']}" aria-label="Vai al tuo profilo">
					{$_SESSION['user']}<img src="{$_SESSION['path_immagine']}" alt="" width="40">
				</a>
			</div>
			HTML;
	} else {
		$ris =
			'<div class="header-buttons">' .
			$themeToggleButton .
			$accediButton .
			'</div>';
	}

	return $ris;
}

//TODO: sta roba non ha niente di dinamico quindi forse non va qui??
function getHamburgerButton(): string
{
	$chiuso =
		'<span class="active"><img class="hamburger-icon" src="/assets/imgs/hamburger.svg" alt=""><span class="visually-hidden">Apri l\'<span lang="en">hamburger</span> menù</span></span>';
	$aperto =
		'<span><img class="hamburger-icon" src="/assets/imgs/cross.svg" alt=""><span class="visually-hidden">Chiudi l\'<span lang="en">hamburger</span> menù</span></span>';
	$hamburgerIcon =
		'<button id="hamburger" aria-pressed="false">' .
		$chiuso .
		$aperto .
		'</button>';

	return $hamburgerIcon;
}

/**
 * Restituisce l'header con la navbar aggiornata
 *
 * @return string HTML dell'header con la navbar sostituita
 */
function getHeaderSection($path): string
{
	$header = file_get_contents('../src/templates/header.html');

	$myHeader = str_replace('<!-- [navbar] -->', getNavBarLi($path), $header);
	$myHeader = str_replace(
		'<!-- [header-buttons] -->',
		getHeaderButtons($path),
		$myHeader
	);
	$myHeader = str_replace(
		'<!-- [hamburger-button] -->',
		getHamburgerButton(),
		$myHeader
	);

	return $myHeader;
}

/**
 * Restituisce il breadcrumb della pagina
 *
 * @param string $path Path della pagina corrente
 * @return string HTML del breadcrumb
 */
function getBreadcrumb($path): string
{
	ensure_session();
	$path = parse_url($path, PHP_URL_PATH);
	$elements = '';
	if ($path == '/') {
		$elements = '<li><span lang="en" class="bold">Home</span></li>';
	} else {
		$path = explode('/', $path);
		$path = array_filter($path);
		$path = array_values($path);
		$elements = '<li><a href="/"><span lang="en">Home</span></a></li>';
		$last = count($path) - 1;
		$currentUrl = '';
		for ($i = 0; $i < $last; $i++) {
			$currentUrl .= '/' . $path[$i];
			$currentPath = str_replace('-', ' ', ucfirst($path[$i])); // remove -
			if (isset($_SESSION['user']) && $i > 0 && $path[$i - 1] == 'profilo' && $path[$i] == $_SESSION['user']) {
				$currentPath = 'Il mio profilo';
			}
			if ($currentPath !== 'Profilo' && $currentPath !== 'Libro') {
				$elements .= '<li><a href="' . $currentUrl . '">' . $currentPath . '</a></li>';
			}

		}
		$currentPath = str_replace('-', ' ', ucfirst($path[$i]));

		if (isset($_SESSION['user']) && $i > 0 && $path[$i - 1] == 'profilo' && $path[$i] == $_SESSION['user']) {
			$currentPath = 'Il mio profilo';
		}

		// Check if the URL is /libro/{isbn} and replace it with /{titolo-libro}
		if ($i > 0 && $path[$i - 1] == 'libro') {
			$isbn = $path[$i];
			$db = new DBAccess();
			$bookTitle = $db->get_book_title_by_ISBN($isbn);
			$bookTitle = $bookTitle[0]['titolo'];
			$currentPath = ucfirst($bookTitle);
		}

		if ($currentPath !== 'Profilo') {
			$elements .= '<li aria-current="page" class="bold">' . $currentPath . '</li>';
		}
	}
	$breadcrumb = "<ol>$elements</ol>";

	return $breadcrumb;
}

/**
 * Restituisce l'URL dell'immagine dell'utente generata usando come seed la sua email
 *
 * @param string $email Email dell'utente
 * @return string URL dell'immagine
 */
function getUserImageUrlByEmail($email): string
{
	$image = 'https://picsum.photos/seed/' . $email . '/500';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $image);
	curl_setopt($ch, CURLOPT_HEADER, true); // true to include the header in the output.
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true true to follow any "Location: " header that the server sends as part of the HTTP header.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // true to return the transfer as a string of the return value of curl_exec() instead of outputting it directly.

	$a = curl_exec($ch); // $a will contain all headers

	$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL

	return $finalUrl; // Voila
}

/**
 * Rimanda l'utente alla pagina di login se non è loggato
 */
function require_login()
{
	ensure_session();
	if (!isset($_SESSION['user'])) {
		header('Location: /accedi');
		exit();
	}
}

function check_ownership(): bool
{
	if (!isset($_SESSION['user'])) {
		return false;
	}
	if ($_GET['user'] === $_SESSION['user']) {
		return true;
	} else {
		return false;
	}
}

function is_logged_in(): bool
{
	ensure_session();
	return isset($_SESSION['user']);
}

/**
 * Restituisce una stringa HTML con i generi preferiti dell'utente
 * @param array $generi Array con i generi preferiti dell'utente
 * @return string Stringa HTML con i generi preferiti
 */
function getGeneriPreferiti($generi)
{
	if (
		!$generi || $generi == null ||
		$generi[0]['generi_preferiti'] == null ||
		$generi[0]['generi_preferiti'] == '[]'
	) {
		return '<p>Non c\'è ancora nessun genere preferito!</p>';
	}

	$fileGeneri = json_decode(file_get_contents('../utils/bisac.json'), true);
	$generiPreferiti = json_decode($generi[0]['generi_preferiti'], true);

	$output = '';
	foreach ($generiPreferiti as $genereKey) {
		$genere = $fileGeneri[$genereKey];
		$output .= sprintf(
			'<div class="button-genere"><span aria-hidden="true">%s</span> %s</div>',
			$genere['emoji'],
			$genere['name']
		);
	}
	return $output;
}

/**
 * Restituisce una stringa HTML con i libri in formato carosello con copertina grande
 * @param array $libri Array con i libri da visualizzare dal database
 * @param int $max_risultati Numero massimo di risultati da visualizzare
 * @return string
 */
function getLibriCopertinaGrande($libri, $max_risultati): string
{
	$output = '';
	if (!$libri || $libri == null) {
		return '<p class="carosello-libri-vuoto">Non ci sono ancora libri in questa lista!</p>';
	}
	$num_libri = count(value: $libri) > $max_risultati ? $max_risultati : count(value: $libri);
	for ($i = 0; $i < $num_libri; $i++) {
		$libroTemplate = <<<HTML
		<div class="libro">
			<a href="/libro/{$libri[$i]['ISBN']}" aria-label="Libro {$libri[$i]["titolo"]} di {$libri[$i]["autore"]}" ">
				<img alt="" src="{$libri[$i]['path_copertina']}" width="150" />
				<p class="titolo-libro">{$libri[$i]["titolo"]}</p>
				<p class="autore-libro">{$libri[$i]["autore"]}</p>
			</a>
		</div>
		HTML;
		$output .= $libroTemplate;
	}
	return $output;
}

/**
 * Restituisce una stringa HTML con i libri in formato lista
 * @param mixed $libri_utente
 * @param mixed $list_name
 * @return string
 */
function getLibriList($libri_utente, $list_name): string
{
	if ($list_name != 'libri-offerti' && $list_name != 'libri-desiderati') {
		throw new TypeError("list_name deve essere 'libri-desiderati' o 'libri-offerti'");
	}


	$libri_html = '';
	if (!$libri_utente) {
		$libri_html = '<p>Non ci sono ancora libri qui!</p>';
	} else {
		foreach ($libri_utente as $libro) {
			$isbn = $libro['ISBN'];
			$titolo = $libro['titolo'];
			$autore = $libro['autore'];
			$path_copertina = $libro['path_copertina'];

			$book_copy_info = '';
			if ($list_name === 'libri-offerti') {
				$condizioni = ucfirst($libro['condizioni']);
				$disponibileClass = $libro['disponibile'] ? 'disponibile' : 'non-disponibile';
				$disponibileLabel = $libro['disponibile'] ? 'Disponibile' : 'Non disponibile';
				$book_copy_info = <<<HTML
				<div>
					<div class="libro-stato-{$disponibileClass}" aria-hidden="true"></div>
					<span class="sr-only">Stato</span> {$disponibileLabel}
				</div>
				<p>Condizioni: {$condizioni}</p>
				HTML;
			}

			if (isTuoProfilo($_GET['user'])) {
				$bookButtons = getLibriListBookButtons($list_name, $isbn, $titolo);
			} else {
				$bookButtons = '';
			}

			$libroRowTemplate = <<<HTML
			<div class="book-row">
				<div class="book-info">
					<a href="/libro/{$isbn}" aria-label="Libro {$titolo} di {$autore}" ">
						<img
							src="{$path_copertina}"
							alt=""
							width="50" />
						<div>
							<p><span class="sr-only">Titolo </span>{$titolo}</p>
							<p class="italic"><span class="sr-only">Autore </span>{$autore}</p>
						</div>
					</a>
				</div>
				<div class="book-copy-info">
					{$book_copy_info}
				</div>
				<div class="book-buttons">
					{$bookButtons}
				</div>
			</div>
			HTML;
			$libri_html .= $libroRowTemplate;
		}
	}
	return $libri_html;
}

function isTuoProfilo($profileId)
{
	return isset($_SESSION['user']) && $profileId == $_SESSION['user'];
}

function getLibriListBookButtons($list_name, $isbn, $titolo)
{

	$api = $list_name === 'libri-offerti' ? '/api/rimuovi-libro-offerto' : '/api/rimuovi-libro-desiderato';
	$bookButtons = <<<HTML
	<form action="{$api}" method="post">
		<input type="hidden" name="isbn" value="{$isbn}">
		<input type="submit" class="button-layout danger bold" value="Elimina" aria-label="Elimina {$titolo} dalla lista">
	</form>
	HTML;
	return $bookButtons;
}

/**
 * Aggiunge i bottoni per aggiungere un libro e per cercare un libro,
 * aggiunge anche il dialog per la ricerca
 * @param string $libri_page Pagina HTML template con i libri
 * @param string $list_name "libri-desiderati" o "libri-offerti"
 * @return string
 */
function addButtonsLibriList($libri_page, $list_name): string
{
	if ($list_name != 'libri-offerti' && $list_name != 'libri-desiderati') {
		throw new TypeError("list_name deve essere 'libri-desiderati' o 'libri-offerti'");
	}

	$form_action = $list_name === 'libri-offerti' ? '/api/aggiungi-libro-offerto' : '/api/aggiungi-libro-desiderato';

	$libri_utente = $libri_page;
	$aggiungiLibro = '<button class="button-layout" id="aggiungi-libro-button" aria-label="Aggiungi un libro ai libri offerti">Aggiungi un libro</button>';
	$libri_utente = str_replace('<!-- [aggiungiLibroButton] -->', $aggiungiLibro, $libri_utente);

	$select_condizioni = '';
	if ($list_name === 'libri-offerti') {
		$select_condizioni = <<<HTML
		<div class="select-wrapper">
		<label for="condizioni">Seleziona le condizioni del libro</label>
		<select name="condizioni" id="condizioni" required>
			<option value="" disabled selected>Seleziona le condizioni</option>
			<hr>
			<option value="nuovo">Nuovo</option>
			<option value="come nuovo">Come nuovo</option>
			<option value="usato ma ben conservato">Usato ma ben conservato</option>
			<option value="usato">Usato</option>
			<option value="danneggiato">Danneggiato</option>
		</select>
		</div>
		HTML;
	}

	$cercaLibriDialog = <<<HTML
	<dialog id="aggiungi-libro-dialog">
		<div class="dialog-window">
			<h2>Cerca un libro</h2>
			<form action={$form_action} method="post">
				<label for="titolo" class="visually-hidden">Cerca un libro</label>
				<input type="search"
					name="cerca"
					id="cerca"
					placeholder="Cerca un libro ..." 
					autocomplete="off"
					/>
				<span class="sr-only" role="alert" aria-atomic="false" id="sr-risultati"></span>
				<div id="book-results">
					<p>Nessun risultato</p>
				</div>
				{$select_condizioni}
				<input type="hidden" name="ISBN" value="">
				<input type="hidden" name="titolo" value="">
				<input type="hidden" name="autore" value="">
				<input type="hidden" name="editore" value="">
				<input type="hidden" name="anno" value="">
				<input type="hidden" name="genere" value="">
				<input type="hidden" name="descrizione" value="">
				<input type="hidden" name="lingua" value="">
				<input type="hidden" name="path_copertina" value="">
				<div class="dialog-buttons">
					<button id="close-dialog" class="button-layout-light" type="reset" formnovalidate>Annulla</button>
					<input type="submit" id="aggiungi-libro" class="button-layout" value="Aggiungi libro">
				</div>
			</form>
		</div>
	</dialog>
	HTML;
	return str_replace('<!-- [cercaLibriDialog] -->', $cercaLibriDialog, $libri_utente);
}

function getItaGenere($genere): string
{
	$fileGeneri = json_decode(file_get_contents('../utils/bisac.json'), true);
	$genere = strtolower($genere);
	return $fileGeneri[$genere]['name'];
}

function getLocationName($provincia, $comune): string
{
	$db = new DBAccess();
	$location = $db->get_comune_provincia_sigla_by_ids($comune, $provincia);
	return <<<HTML
		{$location['comune']}, <abbr title="{$location['provincia']}">{$location['provincia_sigla']}</abbr>
		HTML;
}

function ensure_login(): void
{
	ensure_session();
	if (!isset($_SESSION['user'])) {
		header('Location: /accedi');
		exit();
	}
}