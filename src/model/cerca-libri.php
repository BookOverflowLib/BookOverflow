<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();

if (isset($_GET) && isset($_GET['searchInput'])) {
    $searchInput = $_GET['searchInput'];

    try {
        $db->search_books($searchInput);
    } catch (Exception $e) {
        $_SESSION['error'] = exceptionToError($e, "libro non aggiunto, controlla che non sia già presente nella lista");
    }
} else {
    throw new Exception(message: "Errore: Libro non aggiunto");
}

$prefix = getPrefix();
$previousUrl =  $prefix . '/esplora/esplora-tutti' . $_SESSION['user'];
$previousUrl = parse_url($previousUrl, PHP_URL_PATH);
header('Location: ' . $previousUrl);
exit();
