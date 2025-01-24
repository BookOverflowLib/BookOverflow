<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();

if (isset($_SESSION['user'])) {
	header('Location: /profilo/' . $_SESSION['user']);
	exit();
}

require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

$page = getTemplatePage('Accedi');
$accedi = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'accedi.html');

$page = str_replace('<!-- [content] -->', $accedi, $page);
$page = addErrorsToPage($page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;