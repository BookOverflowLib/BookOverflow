<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

$page = getTemplatePage();
$search = "<h1>Cerca</h1><input type='search' name='cerca' id='cerca' placeholder='Cerca un libro ...'/><input id='cercaButton' type='submit'><div id='results'></div><script src='/js/cercaLibri.js'></script>";
$page = str_replace('<!-- [content] -->', $search, $page);
echo $page;
?>
