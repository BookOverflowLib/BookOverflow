<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

$db = new DBAccess();
$dbOK = $db->open_connection();
ensure_session();

$piu_scambiati = $db->get_piu_scambiati();
$mostTradedCoversHTML = getLibriCopertinaGrande($piu_scambiati, 4);

$page = getTemplatePage();
$index = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'index.html');

$page = str_replace('<!-- [content] -->', $index, $page);
$page = str_replace('<!-- [piuScambiati] -->', $mostTradedCoversHTML, $page);
$page = str_replace('<!-- [keywords] -->', 'scambio libri in Italia, libri, scambio, libri Italia, trova libri, BookOverflow', $page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;