<?php

require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';
require_once $GLOBALS['MODEL_PATH'] . 'nyt-libri.php';

// $pp = showBooksInfo();
//insert_NYT_books();
ensure_session();
ensure_login();

$page = getTemplatePage("Potrebbe piacerti");
$esplora = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'esplora-tutti.html');
$esplora = str_replace('<!-- [esploraTuttiTitolo] -->', 'Potrebbe piacerti', $esplora);

$db = new DBAccess();
$match_potrebbe_piacerti = $db->get_potrebbe_piacerti_by_user($_SESSION['user']);

$esplora = str_replace('<!-- [caroselloTuttiLibri] -->', getLibriCopertinaGrande($match_potrebbe_piacerti, 999), $esplora);

$page = str_replace('<!-- [content] -->', $esplora, $page);
echo $page;