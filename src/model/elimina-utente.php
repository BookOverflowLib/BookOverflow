<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$db = new DBAccess();

if (isset($_POST) && isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    try {
        $db->delete_user($user);
    } catch (Exception $e) {
        $_SESSION['error'] = exceptionToError($e, "utente non rimosso");
    }

    session_destroy();
    $prefix = getPrefix();
    header('Location: ' . $prefix . '/accedi');
    exit();
} else {
    $_SESSION['error'] = "Errore: utente non esistente";
}

exit();