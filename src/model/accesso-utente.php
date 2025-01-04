<?php
require_once '../src/model/dbAPI.php';
require_once '../src/model/utils.php';
require_once '../src/model/registration-select.php';

session_start();
$db = new DBAccess();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'], $_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        try {
            $result = $db->login_user($email, $password);
            if ($result) {
                $_SESSION['user'] = $email;
                //FIXME: fetch username da db è temopraneo
                $username = ($db->get_user_by_email($email))['username'];
                header('Location: /profilo/' . $username);
                exit();
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            switch($errorMessage) {
                case 'Wrong password':
                    header('Location: /accedi?error=wrong-password');
                    break;
                case 'User not registered':
                    header('Location: /accedi?error=user-not-found');
                    break;
                default:
                    header('Location: /accedi?error=unknown');
            }
            exit();
        }
    }
}

header('Location: /accedi?error=missing');
exit();