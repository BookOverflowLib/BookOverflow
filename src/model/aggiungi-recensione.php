<?php
//TODO: MAYBE NOT THE BEST WAY TO DO THIS
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();


if (!isset($_POST) || !isset($_SESSION['user'])) {
	$_SESSION['error'] = 'Errore: recensione non aggiunta';
	throw new Exception(message: "Errore: recensione non aggiunta");
}
if (!isset($_POST['testo']) || !isset($_POST['valutazione']) || !isset($_POST['id-scambio']) || !isset($_POST['user-ricevente'])) {
	$_SESSION['error'] = 'Errore: recensione non aggiunta';
	throw new Exception(message: "Errore: recensione non aggiunta");
}

$testo = htmlspecialchars($_POST['testo']);
$userRicevente = htmlspecialchars($_POST['user-ricevente']);

$valutazione = filter_input(INPUT_POST, 'valutazione', FILTER_VALIDATE_INT, [
	'options' => ['min_range' => 1, 'max_range' => 5]
]);
$idScambio = filter_input(INPUT_POST, 'id-scambio', FILTER_VALIDATE_INT);

if ($valutazione === false || $idScambio === false) {
	$_SESSION['error'] = 'Errore: dati non validi';
	throw new Exception(message: "Errore: dati non validi");
}

$previousUrl = $_SERVER['HTTP_REFERER'] ?? '/profilo/' . $_SESSION['user'];
$previousUrl = parse_url($previousUrl, PHP_URL_PATH);
header('Location: ' . $previousUrl);
exit();