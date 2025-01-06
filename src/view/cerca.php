<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

$page = getTemplatePage();
$search = "<h1>cerca</h1><input type='search' name='cerca' id='cerca' placeholder='cerca un libro ...'/><input id='cercabutton' type='submit'><div id='results'></div><script src='/js/cercalibri.js'></script>";
$page = str_replace('<!-- [content] -->', $search, $page);
echo $page;
?>
