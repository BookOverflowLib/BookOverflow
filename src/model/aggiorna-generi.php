<?php
require_once '../src/paths.php';
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
		$_SESSION['error'] = 'Errore: generi non aggiornati';
	}
} else {
	throw new Exception(message: "Errore: generi non impostati");
}

header('Location: /profilo/' . $_SESSION['user'] . '#generi');
exit();