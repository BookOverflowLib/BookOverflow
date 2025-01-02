<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

$fileGeneri = file_get_contents('../utils/bisac.json');
$fileGeneri = json_decode($fileGeneri, true);

$db = new DBAccess();
$dbOK = $db->open_connection();

$page = getTemplatePage("Impostazioni profilo");

// $esplora = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'esplora.html');

// $page = str_replace('<!-- [content] -->', $esplora, $page);
echo $page;