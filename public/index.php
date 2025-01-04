<?php
require_once '../src/paths.php';
$request = $_SERVER['REQUEST_URI'];

// Rimuovi eventuali query string dal percorso principale
$path = parse_url($request, PHP_URL_PATH);

// Controlla se il percorso è "/profilo/*"
if (preg_match("#^/profilo/([^/]+)$#", $path, $matches)) {
	$_GET['user'] = $matches[1]; // "*" diventa $_GET['user']
	$path = '/profilo'; // Reimposta il path come se fosse "/profilo"
}

switch ($path) {
	case '/':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'index.php';
		break;
	case '/esplora':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'esplora.php';
		break;
	case '/esplora/per-te':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'esplora-per-te.php';
		break;
	case '/accedi':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'accedi.php';
		break;
	case '/registrati':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'registrati.php';
		break;
	case '/profilo':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'profilo.php';
		break;
	case '/cerca':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'cerca.php';
		break;
	case '/come-funziona':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'come-funziona.php';
		break;
	case '/impostazioni':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'impostazioni-profilo.php';

	case '/api/ottieni-comuni':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'ottieni-comuni.php';
		break;
	case '/api/registra-utente':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'registra-utente.php';
		break;

	default:
		require __DIR__ . $GLOBALS['PAGES_PATH'] . '404.php';
		break;
}
