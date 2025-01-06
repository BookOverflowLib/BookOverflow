<?php
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
		'<button id="theme-toggle" class="button-layout-light" aria-pressed="false">' .
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
	if(!isset($_SESSION)){
		session_start();
	}
	
	if (isset($_SESSION['user'])) {
		$ris =
			'<div class="header-buttons">' .
			$themeToggleButton .
			'<a class="profile-button" href="/profilo/' .
			$_SESSION['user'] .
			'">' .
			$_SESSION['user'] .
			'<img src="' .
			$_SESSION['path_immagine'] .
			'" alt="" width="40">' .
			'</a>' .
			'</div>';
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
	//TODO: sistemare quando ci saranno più pagine
	$path = parse_url($path, PHP_URL_PATH);
	$breadcrumb = '';
	if ($path == '/') {
		$breadcrumb =
			'<p>Ti trovi in : <span lang="en" class="bold">Home</span></p>';
	} else {
		$path = explode('/', $path);
		$path = array_filter($path);
		$path = array_values($path);
		$breadcrumb =
			'<p>Ti trovi in : <span lang="en"><a href="/">Home</a></span> > ';
		$last = count($path) - 1;
		$currentUrl = '';
		for ($i = 0; $i < $last; $i++) {
			$currentUrl .= '/' . $path[$i];
			$currentPath = str_replace('-', ' ', ucfirst($path[$i]));
			$breadcrumb .=
				'<a href="' . $currentUrl . '">' . $currentPath . '</a> > ';
		}
		$breadcrumb .=
			'<span class="bold">' .
			str_replace('-', ' ', ucfirst($path[$i])) .
			'</span></p>';
	}
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
