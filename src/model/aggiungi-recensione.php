<?php
//TODO: MAYBE NOT THE BEST WAY TO DO THIS
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();

if (isset($_POST) && isset($_SESSION['user'])) {
	//TODO: recensione
} else {
	$_SESSION['error'] = 'Errore: recensione non aggiunta';
	throw new Exception(message: "Errore: recensione non aggiunta");
}

$previousUrl = $_SERVER['HTTP_REFERER'] ?? '/profilo/' . $_SESSION['user'];
$previousUrl = parse_url($previousUrl, PHP_URL_PATH);
header('Location: ' . $previousUrl);
exit();