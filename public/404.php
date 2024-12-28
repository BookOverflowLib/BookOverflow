<?php
require_once '../src/model/dbAPI.php';
require_once '../src/model/utils.php';

$db = new DBAccess();

$PAGE_TITLE = "Errore 404 - BookOverflow";

$template = file_get_contents('./html/templatePage.html');
$header = getHeaderSection();
$footer = file_get_contents('./html/footer.html');
$error404 = file_get_contents('./html/404.html');

$page = str_replace('<!-- [pageTitle] -->', $PAGE_TITLE, $template);
$page = str_replace('<!-- [header] -->', $header, $page);
$page = str_replace('<!-- [footer] -->', $footer, $page);
$page = str_replace('<!-- [content] -->', $error404, $page);
echo $page;




echo "ERROR 404: PAGE NOT FOUND";