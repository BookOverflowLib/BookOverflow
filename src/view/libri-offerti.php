<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

//check if user is logged in
ensure_session();
if ($_GET['user'] != $_SESSION['user']) {
    header('Location: /profilo/' . $_GET['user']);
    exit();
}

$page = getTemplatePage("Libri offerti");

$generi = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'libri-offerti.html');

$page = str_replace('<!-- [content] -->', $generi, $page);
echo $page;