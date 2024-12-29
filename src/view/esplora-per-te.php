<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';
require_once $GLOBALS['MODEL_PATH'] . 'nyt-libri.php';

// $pp = showBooksInfo();
insert_NYT_books();

$page = getTemplatePage("Per Te");
//$esplora = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'matchperte.html');

// $page = str_replace('<!-- [content] -->', $pp, $page);
echo $page;