<?php
require_once __DIR__ . '/' . '../model/dbAPI.php';
require_once __DIR__ . '/' . '../model/utils.php';
require_once __DIR__ . '/' . '../model/registration-select.php';

$db = new DBAccess();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (isset($_POST['nome'], $_POST['cognome'], $_POST['provincia'], $_POST['comune'], $_POST['email'], $_POST['username'], $_POST['password'], $_POST['conferma-password'])) {
		$nome = $_POST['nome'];
		$cognome = $_POST['cognome'];
		$provincia = $_POST['provincia'];
		$comune = $_POST['comune'];
		$email = $_POST['email'];
		$username = $_POST['username'];
		$password = $_POST['password'];
		$password2 = $_POST['conferma-password'];
		$image = getUserImageUrlByEmail($email);

		$prefix = getPrefix();
		// TODO: errore se username già esistente
		if ($password !== $password2) {
			header('Location: ' . $prefix . '/registrati?error=password-mismatch');
			exit();
		}
		try {
			$db->register_user($nome, $cognome, $provincia, $comune, $email, $username, $password, $image);
		} catch (Exception $e) {
			header('Location: ' . $prefix . '/registrati?error=generic');
			// TODO
			exit();
		}
	
		ensure_session();

		$_SESSION['user'] = $username;
		$_SESSION['path_immagine'] = $image;

		header('Location: ' . $prefix . '/profilo/' . $username);

		exit();
	}
}