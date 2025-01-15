<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();

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
	header('Location: /404');
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
function viewScambioDisponibileDiUtente($utente, $libro): string
{
	$location = getLocationName($utente['provincia'], $utente['comune']);
	$scambio = <<<HTML
	<div class="scambio"> <!-- uno scambio -->
	    <div class="scambio-utente"> <!-- info utente -->
	    	<a href="/profilo/{$utente['username']}">
		        <img alt="" src="{$utente['path_immagine']}" width="100"/>
		        <div>
		            <p>{$utente['nome']} {$utente['cognome']}</p>
		            <p>@{$utente['username']}</p>
		            <p class="bold">{$location}</p>
		        </div>
	        </a>	
	    </div>
	    <div class="scambio-info">
		    <p>Vorrebbe in cambio: </p>
		    <div class="scambio-libro">
		        <a href="/libro/{$libro['ISBN']}">
			        <img alt="" src="{$libro['path_copertina']}" width="100"/>
			        <div>
			            <p>{$libro['titolo']}</p>
			            <p>{$libro['autore']}</p>
			        </div>
			    </a>
		    </div>
		    <a href="/profilo/{$utente['username']}/libri-desiderati">Oppure altri libri</a>
		</div>
	</div>
	HTML;
	return $scambio;
}

$scambi_html = "";
if (is_logged_in()) {
	$utentiInteressati = $db->get_users_with_book_and_interested_in_my_books($_SESSION['user'], $_GET['ISBN']);
	foreach ($utentiInteressati as $utente) {
		try {
			$libriDesiderati = $db->get_desiderati_che_offro($_SESSION['user'], $utente['username']);

		} catch (Exception $e) {
			$_SESSION['error'] = "Errore durante la connessione al database";
			header('Location: /404');
			exit();
		}
		$libroDesiderato = $libriDesiderati[0];
		$scambi_html .= viewScambioDisponibileDiUtente($utente, $libroDesiderato);
	}
	$numUtentiInteressati = count($utentiInteressati);
	$libro_page = str_replace('<!-- [numUtentiInteressati] -->', $numUtentiInteressati . ' utenti lo scambiano', $libro_page);
} else {
	$scambi_html = "<p>Per vedere gli scambi disponibili devi fare accesso. <a href='/accedi'>Accedi</a></p>";
}

$libro_page = str_replace('<!-- [scambiPossibili] -->', $scambi_html, $libro_page);

//TODO: se l'utente non è loggato non mostra un avviso che bisogna loggare per vedere gli scambi

$page = str_replace('<!-- [content] -->', $libro_page, $page);
echo $page;