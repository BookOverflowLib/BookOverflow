<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$prefix = getPrefix();

if (!isset($_GET['ISBN'])) {
	$_SESSION['error'] = "Nessun libro specificato";
	header('Location: ' . $prefix . '/404');
	exit();
}

$db = new DBAccess();
try {
	$dbOK = $db->open_connection();
} catch (Exception $e) {
	$_SESSION['error'] = "Errore durante la connessione al database";
}
try {
	$libro = $db->get_book_by_ISBN($_GET['ISBN']);
	$libro = $libro[0];
} catch (Exception $e) {
	$_SESSION['error'] = "Errore nessun libro trovato";
	header('Location: ' . $prefix . '/404');
	exit();
}

$page = getTemplatePage("{$libro['titolo']} - {$libro['autore']}");
$libro_page = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'libro.html');

function parseLibroInfo($libro)
{
	$libro['autore'] = $libro['autore'] == 'undefined' ? "Sconosciuto" : $libro['autore'];
	$libro['genere'] = $libro['genere'] == 'undefined' ? "Sconosciuto" : getItaGenere($libro['genere']);
	$libro['editore'] = $libro['editore'] == 'undefined' ? "Sconosciuto" : $libro['editore'];
	$libro['anno'] = $libro['anno'] == 'undefined' ? "Sconosciuto" : $libro['anno'];
	$libro['descrizione'] = $libro['descrizione'] == 'undefined' ? "Nessuna descrizione per questo libro" : $libro['descrizione'];
	return $libro;
}

$libro = parseLibroInfo($libro);
// Sostituzione dati libro
$sostituzioni = [
	'<!-- [titoloLibro] -->' => $libro['titolo'],
	'<!-- [autoreLibro] -->' => $libro['autore'],
	'<!-- [descrizioneLibro] -->' => $libro['descrizione'],
	'<!-- [pathCopertinaLibro] -->' => $libro['path_copertina'],
	'<!-- [isbnLibro] -->' => $libro['ISBN'],
	'<!-- [genereLibro] -->' => $libro['genere'],
	'<!-- [editoreLibro] -->' => $libro['editore'],
	'<!-- [annoLibro] -->' => $libro['anno'],

];

foreach ($sostituzioni as $placeholder => $value) {
	$libro_page = str_replace($placeholder, $value, $libro_page);
}

// Aggiungi scambi disponibili
function viewScambioDisponibileDiUtente($utente, $libro, $isProposto): string
{
	$prefix = getPrefix();
	$location = getLocationName($utente['provincia'], $utente['comune']);

	$scambioButtons = "";
	if ($isProposto) {
		$scambioButtons = <<<HTML
		<p>Scambio già proposto</p>
		HTML;
	} else {
		$scambioButtons = <<<HTML
		<form action="{$prefix}/api/proponi-scambio" method="post">
			<input type="hidden" name="utente_proponente" value="{$_SESSION['user']}" />
			<input type="hidden" name="utente_accettatore" value="{$utente['username']}" />
			<input type="hidden" name="ISBN_proponente" value="{$libro['ISBN']}" />
			<input type="hidden" name="ISBN_accettatore" value="{$_GET['ISBN']}" />
			<input type="submit" class="button-layout danger bold" value="Proponi scambio" />
		</form>
		HTML;

	}
	$scambio = <<<HTML
	<div class="scambio"> <!-- uno scambio -->
	    <div class="scambio-utente"> <!-- info utente -->
			<a href="{$prefix}/profilo/{$utente['username']}">
				<img alt="" src="{$utente['path_immagine']}" width="100"/>
				<div>
					<p class="bold">{$utente['nome']} {$utente['cognome']}</p>
					<p>@{$utente['username']}</p>
					<p class="small">{$location}</p>
				</div>
			</a>    
		</div>
		<div class="scambio-info">
			<p>Vorrebbe in cambio: </p>
			<div class="scambio-libro">
				<a href="{$prefix}/libro/{$libro['ISBN']}">
					<img alt="" src="{$libro['path_copertina']}" width="70"/>
					<div>
						<p class="bold">{$libro['titolo']}</p>
						<p class="italic">{$libro['autore']}</p>
					</div>
				</a>
			</div>
		</div>
		<div class="scambio-button">
			{$scambioButtons}
		</div>
	</div>
	HTML;
	return $scambio;
}

$scambi_html = "";
if (is_logged_in()) {
	$utentiInteressati = $db->get_users_with_that_book_and_interested_in_my_books($_SESSION['user'], $_GET['ISBN']);
	$numUtentiInteressati = count($utentiInteressati);
	$libro_page = str_replace('<!-- [numUtentiInteressati] -->', $numUtentiInteressati . ' utenti lo scambiano con i tuoi libri', $libro_page);

	if ($numUtentiInteressati == 0) {
		$scambi_html = "<p>Nessuno scambia ancora questo libro!</p>";
	}

	foreach ($utentiInteressati as $utente) {
		try {
			$libriDesiderati = $db->get_desiderati_che_offro($_SESSION['user'], $utente['username']);

		} catch (Exception $e) {
			$_SESSION['error'] = "Errore durante la connessione al database";
			header('Location: ' . $prefix . '/404');
			exit();
		}
		foreach ($libriDesiderati as $libroDes) {
			$isProposto = $db->check_scambio_proposto($_SESSION['user'], $utente['username'], $libroDes['ISBN'], $_GET['ISBN']);
			$scambi_html .= viewScambioDisponibileDiUtente($utente, $libroDes, $isProposto);

		}
	}
} else {
	$prefix = getPrefix();
	$scambi_html = "<p>Per vedere gli scambi disponibili devi fare accesso. <a href='" . $prefix . "/accedi'>Accedi</a></p>";
}

$libro_page = str_replace('<!-- [scambiPossibili] -->', $scambi_html, $libro_page);

$page = str_replace('<!-- [keywords] -->', $libro['titolo'] . ', ' . $libro['ISBN'] . ',' . ' libro, scambio libro in Italia, BookOverflow', $page);
$page = str_replace('<!-- [content] -->', $libro_page, $page);
$page = populateWebdirPrefixPlaceholders($page);
$page = addErrorsToPage($page);
echo $page;