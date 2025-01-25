<?php
require_once __DIR__ . '/' . '../model/dbAPI.php';
require_once __DIR__ . '/' . '../model/utils.php';
require_once __DIR__ . '/' . '../model/registration-select.php';

ensure_session();

$db = new DBAccess();
$prefix = getPrefix();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	redirect("Errore: richiesta non valida");
}

if (!isset($_POST['nome'], $_POST['cognome'], $_POST['provincia'], $_POST['comune'], $_POST['email'], $_POST['username'], $_POST['password'], $_POST['conferma_password'])) {
	redirect("Errore: dati mancanti");
}

$nome = htmlspecialchars($_POST['nome']);
$cognome = htmlspecialchars($_POST['cognome']);
$id_provincia = $_POST['provincia'];
$id_comune = $_POST['comune'];
$email = $_POST['email'];
$username = htmlspecialchars($_POST['username']);
$password = $_POST['password'];
$password2 = $_POST['conferma_password'];
$image = getUserImageUrlByEmail($email);


if ($password !== $password2) {
	redirect("Le password non corrispondono");
}
if (!preg_match("/^[a-zA-Z ]{2,50}$/", $nome)) {
	redirect("Nome non valido");
}
if (!preg_match("/^[a-zA-Z ]{2,50}$/", $cognome)) {
	redirect("Cognome non valido");
}
if (!preg_match("/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/", $email)) {
	redirect("Email non valida");
}
if (!preg_match("/^[^\s\r\n]{2,50}$/", $username)) {
	redirect("Username non valido");
}

try {
	$db->register_user($nome, $cognome, $id_provincia, $id_comune, $email, $username, $password, $image);
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
		header('Location: ' . $prefix . '/registrati');
	} else {
		header('Location: ' . $prefix . '/profilo/' . $_SESSION['user']);
	}
	exit();
}