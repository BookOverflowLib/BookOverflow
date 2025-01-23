<?php
require_once __DIR__ . '/' . '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

$page = getTemplatePage('Errore 404');
$error404 = file_get_contents($GLOBALS['TEMPLATES_PATH'] . '404.html');

$page = str_replace('<!-- [content] -->', $error404, $page);
// print_r($_SESSION['error']);
echo $page;
