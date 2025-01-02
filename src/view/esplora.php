<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

$db = new DBAccess();
$dbOK = $db->open_connection();

$page = getTemplatePage("Esplora");
$esplora = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'esplora.html');

$page = str_replace('<!-- [content] -->', $esplora, $page);
echo $page;