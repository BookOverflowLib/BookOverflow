<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

//check if user is logged in
ensure_session();
if ($_GET['user'] === $_SESSION['user']) {
    
}

$page = getTemplatePage("Lista dei desideri");

$generi = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'seleziona-generi.html');

$page = str_replace('<!-- [content] -->', $generi, $page);
echo $page;