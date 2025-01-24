<?php
require_once __DIR__ . '/' . '../model/dbAPI.php';
require_once __DIR__ . '/' . '../model/utils.php';
require_once __DIR__ . '/' . '../model/registration-select.php';

ensure_session();
$db = new DBAccess();

$prefix=getPrefix();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['identifier'], $_POST['password'])) {
        $identifier = $_POST['identifier'];
        $password = $_POST['password'];

        try {
            $result = $db->login_user($identifier, $password);
            if ($result) {
                $user = ($db->get_user_by_identifier($identifier))[0];
                $_SESSION['user'] = $user['username'];
                $_SESSION['path_immagine'] = $user['path_immagine'];
                header('Location: ' . $prefix . '/profilo/' . $user['username']);
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . $prefix . '/accedi');
            $_SESSION['error'] = "Le credenziali inserite non sono corrette";
            exit();
        }
    }
}

$_SESSION['error'] = "Inserire tutti i campi richiesti";
header('Location: ' . $prefix . '/accedi');
exit();
