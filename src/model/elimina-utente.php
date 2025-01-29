<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	redirect("Errore: richiesta non valida");
}

if (!isset($_SESSION['user'])) {
	redirect('Errore: richiesta non valida');
}
if (!isset($_POST, $_POST['username'])) {
	redirect("Errore: dati mancanti");
}


if (is_admin() || $_SESSION['user'] === $_POST['username']) {
	deleteUser($_POST['username']);
} else {
	redirect("Errore: operazine non valida");
}

function deleteUser($username)
{
	$db = new DBAccess();
	try {
		$db->delete_user($username);
	} catch (Exception $e) {
		$_SESSION['error'] = exceptionToError($e, "utente non rimosso");
	}
	session_destroy();
	$prefix = getPrefix();
	header('Location: ' . $prefix . '/accedi');
	exit();
}

function redirect(string $error = null): never
{
	$prefix = getPrefix();
	if ($error) {
		$_SESSION['error'] = $error;
	}
	$previousUrl = parse_url($_SERVER['HTTP_REFERER'] ?? $prefix . '/', PHP_URL_PATH);
	header('Location: ' . $previousUrl);
	exit();
}