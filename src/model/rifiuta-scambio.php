<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();

if (isset($_POST) && isset($_SESSION['user'])) {
	$id = $_POST['id_scambio'];

	try {
		$db->rifiuta_scambio_by_id($id);
	} catch (Exception $e) {
		$_SESSION['error'] = 'Errore: scambio non rifiutato';
	}
} else {
	throw new Exception(message: "Errore: scambio non rifiutato");
}

$previousUrl = $_SERVER['HTTP_REFERER'];
$previousUrl = parse_url($previousUrl, PHP_URL_PATH);
header('Location: ' . $previousUrl);
exit();