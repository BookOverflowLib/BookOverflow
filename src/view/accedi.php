<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

$page = getTemplatePage('Accedi');
$accedi = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'accedi.html');

$page = str_replace('<!-- [content] -->', $accedi, $page);
echo $page;