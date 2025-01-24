<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';


$page = getTemplatePage("Come funziona");
$comefunziona = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'come-funziona.html');

$page = str_replace('<!-- [content] -->', $comefunziona, $page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;