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

// Controlla se il percorso è "/libro/*"
if (preg_match("#^/libro/([^/]+)$#", $path, $matches)) {
	$_GET['ISBN'] = $matches[1];
	$path = '/libro';
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
	case '/esplora/potrebbe-piacerti':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'esplora-potrebbe-piacerti.php';
		break;
	case '/piu-scambiati':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'esplora-piu-scambiati.php';
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
	case '/libro':
		require __DIR__ . $GLOBALS['PAGES_PATH'] . 'libro.php';
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
	case '/api/aggiungi-libro-offerto':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'aggiungi-libro-offerto.php';
	case '/api/aggiungi-libro-desiderato':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'aggiungi-libro-desiderato.php';
	case '/api/rimuovi-libri-offerti':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'rimuovi-libri-offerti.php';
	case '/api/rimuovi-libri-desiderati':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'rimuovi-libri-desiderati.php';
		break;
	case '/api/proponi-scambio':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'proponi-scambio.php';
		break;
	case '/api/accetta-scambio':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'accetta-scambio.php';
		break;
	case '/api/rifiuta-scambio':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'rifiuta-scambio.php';
		break;
	case '/api/rimuovi-scambio':
		require __DIR__ . $GLOBALS['MODEL_PATH'] . 'rimuovi-scambio.php';
		break;

	default:
		require __DIR__ . $GLOBALS['PAGES_PATH'] . '404.php';
		break;
}
