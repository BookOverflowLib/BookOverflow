<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();

if (isset($_POST) && isset($_SESSION['user'])) {
	$user = $_SESSION['user'];
	$isbn = $_POST['isbn'];
	try {
		$db->delete_libro_offerto($user, $isbn);
	} catch (Exception $e) {
		$_SESSION['error'] = exceptionToError($e, 'Libro non rimosso, controlla che non sia parte di uno scambio in corso.');
	}
} else {
	$_SESSION['error'] = 'Errore: libro non rimosso';
}

$prefix = getPrefix();
$previousUrl = $_SERVER['HTTP_REFERER'] ?? $prefix . '/profilo/' . $_SESSION['user'];
$previousUrl = parse_url($previousUrl, PHP_URL_PATH);
header('Location: ' . $previousUrl);
exit();