<?php
require_once '../src/model/dbAPI.php';
require_once '../src/model/utils.php';

$db = new DBAccess();
$dbOK = $db->open_connection();

$PAGE_TITLE = "Esplora - BookOverflow";

$template = file_get_contents('./html/templatePage.html');
$header = getHeaderSection();
$breadcrumb = getBreadcrumb($_SERVER['REQUEST_URI']);
$esplora = file_get_contents('./html/esplora.html');
$footer = file_get_contents('./html/footer.html');

$page = str_replace('<!-- [pageTitle] -->', $PAGE_TITLE, $template);
$page = str_replace('<!-- [header] -->', $header, $page);
$page = str_replace('<!-- [breadcrumb] -->', $breadcrumb, $page);
$page = str_replace('<!-- [footer] -->', $footer, $page);
$page = str_replace('<!-- [content] -->', $esplora, $page);
echo $page;