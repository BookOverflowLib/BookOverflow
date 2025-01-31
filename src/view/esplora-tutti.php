<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';
require_once $GLOBALS['MODEL_PATH'] . 'nyt-libri.php';

// $pp = showBooksInfo();
//insert_NYT_books();
ensure_session();

$page = getTemplatePage("Esplora tutti");
$esplora = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'esplora-tutti.html');
$esplora = str_replace('<!-- [esploraTuttiTitolo] -->', 'Esplora tutti', $esplora);

$sottotitolo = 'Tutti i libri messi a disposizione dagli utenti nella piattaforma!';
$esplora = str_replace('<!-- [sottotitolo] -->', $sottotitolo, $esplora);

$prefix = getPrefix();


$isFiltroActive = isset($_GET['filtroGenere']) && $_GET['filtroGenere'] === 'true';
if (isset($_GET['filtroGenere'])) {
	$ricercaValue = '';
	unset($_GET['search']);
} else {
	$ricercaValue = $_GET['search'] ?? '';
}
if (!is_logged_in()) {
	$filtroGenereButton = '';
} else if ($isFiltroActive) {
	$filtroGenereButton = <<<HTML
	<button type='submit' name='filtroGenere' id='filtroGenere' class='button-layout secondary' value="false" aria-pressed="true">Filtra per Generi preferiti <span aria-hidden="true"><img src="{$prefix}/assets/imgs/filter.svg" alt=""/></span></button>
	HTML;
} else {
	$filtroGenereButton = <<<HTML
	<button type='submit' name='filtroGenere' id='filtroGenere' class='button-layout secondary-light' value="true" aria-pressed="false">Filtra per Generi preferiti <span aria-hidden="true"><img src="{$prefix}/assets/imgs/filter.svg" alt=""/></span></button>
	HTML;
}

$ricerca = <<<HTML
	<script src="{$prefix}/js/ricercaEsplora.js" defer></script>
	<div class="sezione-stretta">
		<h2>Filtra ricerca</h2>
		<form id="ricercaForm" method='GET'>
			<div class='search-layout'>
				<label for="searchInput" class="sr-only">Cerca tra i libri presenti</label>
				<input type='search' name='search' id='searchInput' value="{$ricercaValue}" placeholder='Cerca per titolo, autore o ISBN ...'>
				<button type='submit' class="button-layout-icon" id='ricercaButton'><span class="sr-only">Cerca</span><img src="{$prefix}/assets/imgs/cerca.svg" alt="" aria-hidden="true"></button>
				<button type='reset' name='reset' id='reset' class='button-layout destructive'>Azzera filtri <span aria-hidden="true"><img src="{$prefix}/assets/imgs/trash.svg" alt=""/></span></button>
				{$filtroGenereButton}
			</div>
		</form>
	</div>
HTML;

$esplora = str_replace('<!-- [ricerca] -->', $ricerca, $esplora);

$db = new DBAccess();
if (isset($_GET) && isset($_GET['filtroGenere']) && $_GET['filtroGenere'] === 'true' && is_logged_in()) {
	try {
		$tutti = $db->get_books_by_preferences($_SESSION['user']);
	} catch (Exception $e) {
		$_SESSION['error'] = exceptionToError($e, "Ricerca per generi preferiti non riuscita");
	}
} elseif (isset($_GET) && isset($_GET['search']) && !empty($_GET['search'])) {
	$searchInput = $_GET['search'];

	try {
		$tutti = $db->search_books($searchInput);
	} catch (Exception $e) {
		$_SESSION['error'] = exceptionToError($e, "ricerca non riuscita");
	}
} else {
	// var_dump("tutti");
	$tutti = $db->get_libri_offerti();
}

$esplora = str_replace('<!-- [caroselloTuttiLibri] -->', getLibriCopertinaGrande($tutti, 999), $esplora);

$page = str_replace('<!-- [content] -->', $esplora, $page);
$page = populateWebdirPrefixPlaceholders($page);
$page = addErrorsToPage($page);
echo $page;
