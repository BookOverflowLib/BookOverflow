<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';
require_once $GLOBALS['MODEL_PATH'] . 'nyt-libri.php';

// $pp = showBooksInfo();
//insert_NYT_books();
ensure_session();
ensure_login();

$page = getTemplatePage("Per Te");
$esplora = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'esplora-tutti.html');
$esplora = str_replace('<!-- [esploraTuttiTitolo] -->', 'Match per te', $esplora);

$sottotitolo = 'Scopri i match perfetti! Qui trovi i libri che desideri e che altri utenti offrono, la bella notizia è che anche tu hai qualcosa che loro desiderano!';
$esplora = str_replace('<!-- [sottotitolo] -->', $sottotitolo, $esplora);
$esplora = str_replace('<!-- [ricerca] -->', '', $esplora);

$db = new DBAccess();
$match_per_te = $db->get_match_per_te_by_user($_SESSION['user']);
$esplora = str_replace('<!-- [caroselloTuttiLibri] -->', getLibriCopertinaGrande($match_per_te, 999), $esplora);

$page = str_replace('<!-- [content] -->', $esplora, $page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;