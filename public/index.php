<?php
require_once '../src/paths.php';
$request = $_SERVER['REQUEST_URI'];

// Rimuovi eventuali query string dal percorso principale
$path = parse_url($request, PHP_URL_PATH);

// Controlla se il percorso è "/profilo/*"
if (preg_match("#^/profilo/([^/]+)/seleziona-generi$#", $path, $matches)) {
	$_GET['user'] = $matches[1];
	$path = '/profilo/seleziona-generi';
} elseif (preg_match('#^/profilo/([^/]+)/libri-offerti#', $path, $matches)) {
	$_GET['user'] = $matches[1];
	$path = '/profilo/libri-offerti';
} elseif (preg_match('#^/profilo/([^/]+)/libri-desiderati#', $path, $matches)) {
	$_GET['user'] = $matches[1];
	$path = '/profilo/libri-desiderati';
} elseif (preg_match("#^/profilo/([^/]+)$#", $path, $matches)) {
	$_GET['user'] = $matches[1];
	$path = '/profilo';
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
	case '/profilo/seleziona-generi':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'seleziona-generi.php';
		break;
	case '/profilo/libri-offerti':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'libri-offerti.php';
		break;
	case '/profilo/libri-desiderati':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'libri-desiderati.php';
		break;
	// API
	case '/api/ottieni-comuni':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'ottieni-comuni.php';
		break;
	case '/api/registra-utente':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'registra-utente.php';
		break;
	case '/api/accesso-utente':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'accesso-utente.php';
		break;
	case '/api/logout':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'logout.php';
		break;
	case '/api/aggiorna-generi':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'aggiorna-generi.php';
		break;
	case '/api/aggiungi-libro':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'aggiungi-libro.php';
	case '/api/rimuovi-libro':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'rimuovi-libro.php';
		break;
		
	default:
		require __DIR__ . $GLOBALS['PAGES_PATH'] . '404.php';
		break;
}
