<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();

if (isset($_POST["generi"]) && isset($_SESSION['user'])) {
	$user = $_SESSION['user'];
	$generi = $_POST['generi'];
	try {
		$db->update_user_generi($user, $generi);
	} catch (Exception $e) {
		$_SESSION['error'] = exceptionToError($e, "generi non aggiornati");
	}
} else {
	throw new Exception(message: "Errore: generi non impostati");
}
$prefix = getPrefix();
header('Location: ' . $prefix . '/profilo/' . $_SESSION['user'] . '#generi');
exit();