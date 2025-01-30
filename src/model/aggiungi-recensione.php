<?php
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
$userRecensito = htmlspecialchars($_POST['user-ricevente']);

$valutazione = filter_input(INPUT_POST, 'valutazione', FILTER_VALIDATE_INT, [
	'options' => ['min_range' => 1, 'max_range' => 5]
]);
$idScambio = filter_input(INPUT_POST, 'id-scambio', FILTER_VALIDATE_INT);

try {
	$canAdd = $db->check_if_user_can_add_review($userRecensito, $_SESSION['user'], $idScambio);
} catch (Exception $e) {
	$_SESSION['error'] = 'Errore: recensione non aggiunta';
	throw new Exception(message: "Errore: recensione non aggiunta");
}
if (!$canAdd) {
	$_SESSION['error'] = 'Errore: recensione già aggiunta';
	throw new Exception(message: "Errore: recensione già aggiunta");
}

if ($valutazione === false || $idScambio === false) {
	$_SESSION['error'] = 'Errore: dati non validi';
	throw new Exception(message: "Errore: dati non validi");
}


try {
	$db->insert_review($userRecensito, $idScambio, $valutazione, $testo);
} catch (Exception $e) {
	$_SESSION['error'] = 'Errore: recensione non aggiunta';
	throw new Exception(message: "Errore: recensione non aggiunta");
}
$prefix = getPrefix();
$previousUrl = $_SERVER['HTTP_REFERER'] ?? $prefix . '/profilo/' . $_SESSION['user'] . '/scambi';
$previousUrl = parse_url($previousUrl, PHP_URL_PATH);
header('Location: ' . $previousUrl);
exit();