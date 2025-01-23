<?php
require_once __DIR__ . '/' . '../model/dbAPI.php';
require_once __DIR__ . '/' . '../src/model/utils.php';
require_once __DIR__ . '/' . '../src/model/registration-select.php';

ensure_session();
$db = new DBAccess();

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
                header('Location: /profilo/' . $user['username']);
                exit();
            }
        } catch (Exception $e) {
            header('Location: /accedi?error=invalid');
            $_SESSION['error'] = "invalid-credentials";
            exit();
        }
    }
}

$_SESSION['error'] = "missing-fields";
header('Location: /accedi?error=missing');
exit();
