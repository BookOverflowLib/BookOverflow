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

$prefix = getPrefix();
$previousUrl = $_SERVER['HTTP_REFERER'] ?? $prefix . '/';
$admin = is_admin();

if ($admin || $_SESSION['user'] === $_POST['username']) {
	$db = new DBAccess();
	try {
		$db->delete_user($username);
	} catch (Exception $e) {
		// in caso di errore torna alla pagina precedente in modo da poter visualizzare l'errore
		redirect($previousUrl, exceptionToError($e, "utente non rimosso"));
		exit();
	}

	if ($admin) {
		// nel caso in cui l'eliminazione venga fatta dal pannello di amministrazione
		redirect($previousUrl);
		exit();
	} else {
		// nel caso in cui l'eliminazione venga fatta dal proprio profilo
		session_destroy();
		redirect($prefix . '/');
		exit();
	}
} else {
	redirect($previousUrl, "Errore: operazione non valida");
}

function redirect(string $url, string $error = null): never
{
	if ($error) {
		$_SESSION['error'] = $error;
	}
	header('Location: ' . $url);
	exit();
}