<?php
require_once __DIR__ . '/' . '../model/dbAPI.php';
require_once __DIR__ . '/' . '../model/utils.php';
require_once __DIR__ . '/' . '../model/registration-select.php';

ensure_session();
ensure_login();

$db = new DBAccess();
$prefix = getPrefix();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	redirect("Errore: richiesta non valida");
}

if (!isset($_SESSION['user'])) {
	redirect("Errore: utente non loggato");
}

$datiUtentePre = ($db->get_user_by_identifier($_SESSION['user']))[0];

if (!isset($_POST['nome'], $_POST['cognome'], $_POST['provincia'], $_POST['comune'])) {
	redirect("Errore: dati mancanti");
}

$nome = htmlspecialchars($_POST['nome']);
$cognome = htmlspecialchars($_POST['cognome']);
$id_provincia = $_POST['provincia'];
$id_comune = $_POST['comune'];
if (!preg_match("/^[a-zA-Z ]{2,50}$/", $nome)) {
	redirect("Nome non valido");
}
if (!preg_match("/^[a-zA-Z ]{2,50}$/", $cognome)) {
	redirect("Cognome non valido");
}

if (isset($_POST['password'], $_POST['conferma_password']) && $_POST['password'] !== "" && $_POST['conferma_password'] !== "") {
	$password = $_POST['password'];
	$password2 = $_POST['conferma_password'];
	if ($password !== $password2) {
		redirect("Le password non corrispondono");
	}
	if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
		redirect("Password non valida");
	}
}


try {
	// $db->register_user($nome, $cognome, $id_provincia, $id_comune, $email, $username, $password, $image);
	if ($datiUtentePre['provincia'] !== $id_provincia) {
		$db->update_user_provincia($_SESSION['user'], $id_provincia);
	}
	if ($datiUtentePre['comune'] !== $id_comune) {
		$db->update_user_comune($_SESSION['user'], $id_comune);
	}
	if ($datiUtentePre['nome'] !== $nome) {
		$db->update_user_nome($_SESSION['user'], $nome);
	}
	if ($datiUtentePre['cognome'] !== $cognome) {
		$db->update_user_cognome($_SESSION['user'], $cognome);
	}
	if (isset($password)) {
		$db->update_user_password($_SESSION['user'], $password);
	}
	redirect();
} catch (Exception $e) {
	$err = exceptionToError($e, "registrazione non riuscita");
	redirect($err);
}

function redirect(string $error = null): never
{
	$prefix = getPrefix();
	if ($error) {
		$_SESSION['error'] = $error;
		header('Location: ' . $prefix . '/profilo/' . $_SESSION['user'] . '/modifica-utente');
	} else {
		header('Location: ' . $prefix . '/profilo/' . $_SESSION['user']);
	}
	exit();
}