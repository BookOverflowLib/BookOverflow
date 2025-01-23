<?php
require_once 'src/paths.php';
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
} elseif (preg_match('#^/profilo/([^/]+)/scambi#', $path, $matches)) {
	$_GET['user'] = $matches[1];
	$path = '/profilo/scambi';
} elseif (preg_match("#^/profilo/([^/]+)$#", $path, $matches)) {
	$_GET['user'] = $matches[1];
	$path = '/profilo';
}

// Controlla se il percorso è "/libro/*"
if (preg_match("#^/libro/([^/]+)$#", $path, $matches)) {
	$_GET['ISBN'] = $matches[1];
	$path = '/libro';
}

// parse prefix from .env file
// DB_HOST is likely to always be present, even if the website has no prefix
if (! isset($_ENV['DB_HOST']))
{
	$env_path = __DIR__ . '/.env';
	if (!file_exists($env_path)) {
		throw new Exception('.env file not found');
	}

	$env = parse_ini_file($env_path);
	if ($env === false) {
		throw new Exception('Error parsing .env file');
	}

	foreach ($env as $key => $value) {
		if (!array_key_exists($key, $_ENV)) {
			$_ENV[$key] = $value;
		}
	}
}
isset($_ENV['PREFIX']) ? $prefix = $_ENV['PREFIX'] : $prefix = '';


#echo $path . "<br>";
// Remove the prefix from the path
if (strpos($path, $prefix) === 0) {
    $path = substr($path, strlen($prefix));
}
#echo $path . "<br>";

switch ($path) {
	case '/':
		require $GLOBALS['PAGES_PATH'] . 'index.php';
		break;
	case '/esplora':
		require $GLOBALS['PAGES_PATH'] . 'esplora.php';
		break;
	case '/esplora/per-te':
		require $GLOBALS['PAGES_PATH'] . 'esplora-per-te.php';
		break;
	case '/esplora/potrebbe-piacerti':
		require $GLOBALS['PAGES_PATH'] . 'esplora-potrebbe-piacerti.php';
		break;
	case '/piu-scambiati':
		require $GLOBALS['PAGES_PATH'] . 'esplora-piu-scambiati.php';
		break;
	case '/accedi':
		require $GLOBALS['PAGES_PATH'] . 'accedi.php';
		break;
	case '/registrati':
		require $GLOBALS['PAGES_PATH'] . 'registrati.php';
		break;
	case '/profilo':
		require $GLOBALS['PAGES_PATH'] . 'profilo.php';
		break;
	case '/cerca':
		require $GLOBALS['PAGES_PATH'] . 'cerca.php';
		break;
	case '/come-funziona':
		require $GLOBALS['PAGES_PATH'] . 'come-funziona.php';
		break;
	case '/profilo/seleziona-generi':
		require $GLOBALS['PAGES_PATH'] . 'seleziona-generi.php';
		break;
	case '/profilo/libri-offerti':
		require $GLOBALS['PAGES_PATH'] . 'libri-offerti.php';
		break;
	case '/profilo/libri-desiderati':
		require $GLOBALS['PAGES_PATH'] . 'libri-desiderati.php';
		break;
	case '/profilo/scambi':
		require $GLOBALS['PAGES_PATH'] . 'scambi.php';
		break;
	case '/libro':
		require $GLOBALS['PAGES_PATH'] . 'libro.php';
		break;
	// API
	case '/api/ottieni-comuni':
		require $GLOBALS['MODEL_PATH'] . 'ottieni-comuni.php';
		break;
	case '/api/registra-utente':
		require $GLOBALS['MODEL_PATH'] . 'registra-utente.php';
		break;
	case '/api/accesso-utente':
		require $GLOBALS['MODEL_PATH'] . 'accesso-utente.php';
		break;
	case '/api/logout':
		require $GLOBALS['MODEL_PATH'] . 'logout.php';
		break;
	case '/api/aggiorna-generi':
		require $GLOBALS['MODEL_PATH'] . 'aggiorna-generi.php';
		break;
	case '/api/aggiungi-libro-offerto':
		require $GLOBALS['MODEL_PATH'] . 'aggiungi-libro-offerto.php';
	case '/api/aggiungi-libro-desiderato':
		require $GLOBALS['MODEL_PATH'] . 'aggiungi-libro-desiderato.php';
	case '/api/rimuovi-libri-offerti':
		require $GLOBALS['MODEL_PATH'] . 'rimuovi-libri-offerti.php';
	case '/api/rimuovi-libri-desiderati':
		require $GLOBALS['MODEL_PATH'] . 'rimuovi-libri-desiderati.php';
		break;
	case '/api/proponi-scambio':
		require $GLOBALS['MODEL_PATH'] . 'proponi-scambio.php';
		break;
	case '/api/accetta-scambio':
		require $GLOBALS['MODEL_PATH'] . 'accetta-scambio.php';
		break;
	case '/api/rifiuta-scambio':
		require $GLOBALS['MODEL_PATH'] . 'rifiuta-scambio.php';
		break;
	case '/api/rimuovi-scambio':
		require $GLOBALS['MODEL_PATH'] . 'rimuovi-scambio.php';
		break;

	default:
		require $GLOBALS['PAGES_PATH'] . '404.php';
		break;
}
