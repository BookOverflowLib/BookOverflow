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
$ricerca = <<<HTML
<h2>Ricerca</h2>
<form action='{$prefix}/esplora/esplora-tutti/cerca-libri' method='GET'>
    <input type='search' name='searchInput' id='searchInput' placeholder='cerca per titolo, autore o ISBN ...'/>
        <input id='ricercaButton' type='submit'><div id='results'></div>
    </form>
HTML;
$esplora = str_replace('<!-- [ricerca] -->', $ricerca, $esplora);

$db = new DBAccess();
$tutti = $db->get_libri_offerti();
$esplora = str_replace('<!-- [caroselloTuttiLibri] -->', getLibriCopertinaGrande($tutti, 999), $esplora);

$page = str_replace('<!-- [content] -->', $esplora, $page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;
