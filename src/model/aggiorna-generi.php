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
	redirect('Errore: utente non autenticato');
}
if (!isset($_POST['generi'])) {
	redirect('Errore: generi non specificati');
}

$user = $_SESSION['user'];
$generi = $_POST['generi'];
if($generi !== "[]"){
	$generi = str_replace('[', '', $generi);
	$generi = str_replace(']', '', $generi);
	$generi = array_map('trim', explode('","', $generi));
	$generi = str_replace('"', '', $generi);
	
	$fileGeneri = file_get_contents(__DIR__ . './../../utils/bisac.json');
	$fileGeneri = json_decode($fileGeneri, true);
	
	foreach ($generi as $value) {
		if (!array_key_exists($value, $fileGeneri)) {
			redirect('Errore: genere non valido');
		}
	}
}

try {
	$db->update_user_generi($user, $_POST['generi']);
	redirect();
} catch (Exception $e) {
	$err = exceptionToError($e, "generi non aggiornati");
	redirect($err);
}

function redirect(string $error = null): never
{
	$prefix = getPrefix();
	if ($error) {
		$_SESSION['error'] = $error;
		header('Location: ' . $prefix . '/profilo/' . $_SESSION['user'] . '/seleziona-generi');
	}else{
		header('Location: ' . $prefix . '/profilo/' . $_SESSION['user'] . '#generi');
	}
	exit();
}