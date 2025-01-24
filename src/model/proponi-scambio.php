<?php

require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();

if (isset($_POST) && isset($_SESSION['user'])) {
	$user_prop = $_POST['utente_proponente'];
	$user_acc = $_POST['utente_accettatore'];
	$isbn_prop = $_POST['ISBN_proponente'];
	$isbn_acc = $_POST['ISBN_accettatore'];

	try {
		$db->insert_scambio($user_prop, $user_acc, $isbn_prop, $isbn_acc);
	} catch (Exception $e) {
		$_SESSION['error'] = exceptionToError($e, "scambio non proposto");
	}
} else {
	throw new Exception(message: "Errore: scambio non proposto");
}

//$previousUrl = $_SERVER['HTTP_REFERER'];
//$previousUrl = parse_url($previousUrl, PHP_URL_PATH);
$prefix = getPrefix();
header('Location: ' . $prefix . '/profilo/' . $_SESSION['user'] . '/scambi');
exit();