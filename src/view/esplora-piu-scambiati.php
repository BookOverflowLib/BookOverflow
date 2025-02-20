<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';
require_once $GLOBALS['MODEL_PATH'] . 'nyt-libri.php';

// $pp = showBooksInfo();
//insert_NYT_books();
ensure_session();

$page = getTemplatePage("Più scambiati");
$page = str_replace('<!-- [keywords] -->', 'libri più scambiati Italia, più scambiati, libri Italia, trova libri, BookOverflow', $page);
$esplora = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'esplora-tutti.html');
$esplora = str_replace('<!-- [esploraTuttiTitolo] -->', 'Più scambiati', $esplora);

$sottotitolo = 'La collezione dei libri più scambiati tra gli utenti!';
$esplora = str_replace('<!-- [sottotitolo] -->', $sottotitolo, $esplora);
$esplora = str_replace('<!-- [ricerca] -->', '', $esplora);

$db = new DBAccess();
$piu_scambiati = $db->get_piu_scambiati();
$esplora = str_replace('<!-- [caroselloTuttiLibri] -->', getLibriCopertinaGrande($piu_scambiati, 999), $esplora);

$page = str_replace('<!-- [content] -->', $esplora, $page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;
