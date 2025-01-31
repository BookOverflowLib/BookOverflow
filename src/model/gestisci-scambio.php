<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();
$prefix = getPrefix();
const VALID_ENDPOINTS = [
	'/api/accetta-scambio',
	'/api/rifiuta-scambio',
	'/api/rimuovi-scambio',
	$prefix . '/api/accetta-scambio',
	$prefix . '/api/rifiuta-scambio',
	$prefix . '/api/rimuovi-scambio'
];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	redirect("Errore: richiesta non valida");
}
if (!isset($_SESSION['user'])) {
	redirect('Errore: utente non autenticato');
}
if (!isset($_POST['id_scambio'])) {
	redirect('Errore: id_scambio non specificato');
}
if (!is_numeric($_POST['id_scambio'])) {
	redirect("Errore: id_scambio non valido");
}
if (!in_array($_SERVER['REQUEST_URI'], VALID_ENDPOINTS, true)) {
	redirect("Errore: endpoint non valido");
}

$id = (int) $_POST['id_scambio'];

try {
	switch ($_SERVER['REQUEST_URI']) {
		case '/api/accetta-scambio':
			$db->accetta_scambio_by_id($id);
			break;
		case '/api/rifiuta-scambio':
			$db->rifiuta_scambio_by_id($id);
			break;
		case '/api/rimuovi-scambio':
			$db->remove_scambio_by_id($id);
			break;
		default:
			redirect("Errore: endpoint non valido");
	}
	redirect();
} catch (Exception $e) {
	$err = exceptionToError($e, "scambio non riuscito");
	redirect($err);
}

function redirect(string $error = null): never
{
	$prefix = getPrefix();
	if ($error) {
		$_SESSION['error'] = $error;
	}
	$previousUrl = parse_url($_SERVER['HTTP_REFERER'] ?? $prefix . '/', PHP_URL_PATH);
	header('Location: ' . $previousUrl);
	exit();
}