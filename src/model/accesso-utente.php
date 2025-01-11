<?php
require_once '../src/model/dbAPI.php';
require_once '../src/model/utils.php';
require_once '../src/model/registration-select.php';

ensure_session();
$db = new DBAccess();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'], $_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        try {
            $result = $db->login_user($email, $password);
            if ($result) {
                $user = ($db->get_user_by_email($email))[0];
                $_SESSION['user'] = $user['username'];
                $_SESSION['path_immagine'] = $user['path_immagine'];
                header('Location: /profilo/' . $user['username']);
                exit();
            }
        } catch (Exception $e) {
            header('Location: /accedi?error=invalid');
            exit();
        }
    }
}

header('Location: /accedi?error=missing');
exit();
