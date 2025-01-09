<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();

if (isset($_POST) && isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $isbn = $_POST['isbn'];
    $ris = $db->delete_libro_offerto($user, $isbn); 
    if (!$ris) {
        $_SESSION['error'] = 'Errore: libro non rimosso';
    }
} else {
    $_SESSION['error'] = 'Errore: libro non rimosso';
}

$previousUrl = $_SERVER['HTTP_REFERER'] ?? '/profilo/' . $_SESSION['user'];
$previousUrl = parse_url($previousUrl, PHP_URL_PATH);
header('Location: ' . $previousUrl);
exit();