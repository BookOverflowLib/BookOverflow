<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();

if (isset($_POST) && isset($_SESSION['user'])) {
	$user = $_SESSION['user'];
	$isbn = $_POST['ISBN'];
	$titolo = $_POST['titolo'];
	$autore = $_POST['autore'];
	$editore = $_POST['editore'];
	$anno = $_POST['anno'];
	if (strlen($anno) > 4) {
		$anno = substr($anno, 0, 4);
	}

	$genere = $_POST['genere'];
	$descrizione = $_POST['descrizione'];
	$lingua = $_POST['lingua'];
	$path_copertina = $_POST['path_copertina'];

	try {
		$db->insert_new_book($isbn, $titolo, $autore, $editore, $anno, $genere, $descrizione, $lingua, $path_copertina);
		$db->insert_libri_desiderati_by_username($user, $isbn);

	} catch (Exception $e) {
		$_SESSION['error'] = exceptionToError($e, "libro non aggiunto");
	}
} else {
	throw new Exception(message: "Errore: Libro non aggiunto");
}

$prefix = getPrefix();
$previousUrl = $_SERVER['HTTP_REFERER'] ?? $prefix . '/profilo/' . $_SESSION['user'];
$previousUrl = parse_url($previousUrl, PHP_URL_PATH);
header('Location: ' . $previousUrl);
exit();