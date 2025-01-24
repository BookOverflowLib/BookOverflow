<?php

require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();

if (isset($_POST) && isset($_SESSION['user'])) {
	$id = $_POST['id_scambio'];

	try {
		$db->remove_scambio_by_id($id);
	} catch (Exception $e) {
		$_SESSION['error'] = exceptionToError($e, "scambio non rimosso");
	}
} else {
	throw new Exception(message: "Errore: scambio non rimosso");
}
//
$previousUrl = $_SERVER['HTTP_REFERER'];
$previousUrl = parse_url($previousUrl, PHP_URL_PATH);
header('Location: ' . $previousUrl);
exit();