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

$db = new DBAccess();
$libri_offerti = $db->get_libri_offerti();

$esplora = str_replace('<!-- [caroselloTuttiLibri] -->', getLibriCopertinaGrande($libri_offerti, 999), $esplora);

$page = str_replace('<!-- [content] -->', $esplora, $page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;