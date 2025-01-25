<?php
require_once __DIR__ . '/' . '../model/dbAPI.php';
require_once __DIR__ . '/' . '../model/utils.php';
require_once __DIR__ . '/' . '../model/registration-select.php';

ensure_session();
$db = new DBAccess();

$prefix = getPrefix();
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect('Errore: richiesta non valida');
}

if (!isset($_POST['identifier'], $_POST['password'])) {
    redirect('Errore: dati mancanti');
}

$identifier = $_POST['identifier'];
$password = $_POST['password'];

try {
    $result = $db->login_user($identifier, $password);
    if ($result) {
        $user = ($db->get_user_by_identifier($identifier))[0];
        $_SESSION['user'] = $user['username'];
        $_SESSION['path_immagine'] = $user['path_immagine'];
        redirect();
    }
} catch (Exception $e) {
    $err = exceptionToError($e, "accesso non riuscito");
    redirect($err);
}

function redirect(string $error = null): never
{
    if ($error) {
        $_SESSION['error'] = $error;
        header('Location: ' . $GLOBALS['prefix'] . '/accedi');
    } else {
        header('Location: ' . $GLOBALS['prefix'] . '/profilo/' . $_SESSION['user']);
    }
    exit();
}