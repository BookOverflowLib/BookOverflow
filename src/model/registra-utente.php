<?php
require_once '../src/model/dbAPI.php';
require_once '../src/model/utils.php';
require_once '../src/model/registration-select.php';

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

		$db->register_user($nome, $cognome, $provincia, $comune, $email, $username, $password, $image);

		ensure_session();

		$_SESSION['user'] = $username;
		$_SESSION['path_immagine'] = $image;

		header('Location: /profilo/' . $username);

		exit();
	}
}