<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

//check if user is logged in
if (!isset($_SESSION['user'])) {
    session_start();
}
if ($_GET['user'] != $_SESSION['user']) {
    header('Location: /profilo/' . $_GET['user']);
    exit();
}

$page = getTemplatePage("Lista dei desideri");

$generi = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'seleziona-generi.html');

$page = str_replace('<!-- [content] -->', $generi, $page);
echo $page;