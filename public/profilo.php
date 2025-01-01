<?php
require_once '../src/model/dbAPI.php';
require_once '../src/model/utils.php';

$profileId = null;
if (isset($_GET['id'])) {
    $profileId = $_GET['id'];
}

$db = new DBAccess();
$dbOK = $db->open_connection();

// GET PROFILE DATA BY ID

$profileName = '';

$PAGE_TITLE = $profileName . " - BookOverflow";

$template = file_get_contents('./html/templatePage.html');
$header = getHeaderSection();
$profilo = file_get_contents('./html/profilo.html');
$footer = file_get_contents('./html/footer.html');

$page = str_replace('<!-- [pageTitle] -->', $PAGE_TITLE, $template);
$page = str_replace('<!-- [header] -->', $header, $page);
$page = str_replace('<!-- [footer] -->', $footer, $page);
$page = str_replace('<!-- [content] -->', $profilo, $page);
echo $page;