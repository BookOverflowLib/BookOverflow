<?php
require_once __DIR__ . '/' . '../model/dbAPI.php';
require_once __DIR__ . '/' . '../model/utils.php';
require_once __DIR__ . '/' . '../model/registration-select.php';

ensure_session();

$db = new DBAccess();
$prefix = getPrefix();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	header('Location: ' . $prefix . '/registrati');
	$_SESSION['error'] = 'Errore: metodo non valido';
	exit();
}

if (!isset($_POST['nome'], $_POST['cognome'], $_POST['provincia'], $_POST['comune'], $_POST['email'], $_POST['username'], $_POST['password'], $_POST['conferma_password'])) {
	header('Location: ' . $prefix . '/registrati');
	$_SESSION['error'] = 'Errore: non tutti i campi del form sono stati riempiti';
	//exceptionToError($e, "registrazione non riuscita");
	exit();
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
	header('Location: ' . $prefix . '/registrati');
	$_SESSION['error'] = "Le password non corrispondono";
	exit();
}
if (!preg_match("/^[a-zA-Z ]{2,50}$/", $nome)) {
	print_r("nome non valido");
	header('Location: ' . $prefix . '/registrati');
	$_SESSION['error'] = "Errore: il nome può contenere solo lettere e spazi, e deve essere lungo almeno 2 caratteri";
	exit();
}
if (!preg_match("/^[a-zA-Z ]{2,50}$/", $cognome)) {
	print_r("cognome non valido");
	header('Location: ' . $prefix . '/registrati');
	$_SESSION['error'] = "Errore: il cognome può contenere solo lettere e spazi, e deve essere lungo almeno 2 caratteri";
	exit();
}
if (!$db->check_provincia_exists($id_provincia)) {
	print_r("provincia non valida");
	header('Location: ' . $prefix . '/registrati');
	$_SESSION['error'] = "Errore: la provincia non esiste";
	exit();
}
if (!$db->check_comune_exists($id_comune)) {
	header('Location: ' . $prefix . '/registrati');
	$_SESSION['error'] = "Errore: il comune non esiste";
	exit();
}
if (!preg_match("/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/", $email)) {
	header('Location: ' . $prefix . '/registrati');
	$_SESSION['error'] = "Errore: email non valida";
	exit();
}
if ($db->check_email_exists($email)) {
	header('Location: ' . $prefix . '/registrati');
	$_SESSION['error'] = "Errore: email già registrata";
	exit();
}
if (!preg_match("/^[^\s\r\n]{2,50}$/", $username)) {
	header('Location: ' . $prefix . '/registrati');
	$_SESSION['error'] = "Errore: lo username deve essere lungo almeno 2 caratteri e non può contenere spazi";
	exit();
}
if ($db->check_username_exists($username)) {
	header('Location: ' . $prefix . '/registrati');
	$_SESSION['error'] = "Errore: username già esistente";
	exit();
}

try {
	$db->register_user($nome, $cognome, $id_provincia, $id_comune, $email, $username, $password, $image);
} catch (Exception $e) {
	header('Location: ' . $prefix . '/registrati');
	$_SESSION['error'] = exceptionToError($e, "registrazione non riuscita");
	exit();
}

$_SESSION['user'] = $username;
$_SESSION['path_immagine'] = $image;

header('Location: ' . $prefix . '/profilo/' . $username);

exit();
