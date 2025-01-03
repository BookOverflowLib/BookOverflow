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
    }
    
    if ($db->login_user($email, $password)) {
        error_log("Login successful for user: " . $email);
        $_SESSION['user'] = $email;

		//FIXME: fetch username da db è temopraneo
		$username = $db->get_username_by_email($email);
        header('Location: /profilo/' . $username);
        exit();
    } else {
        header('Location: /accedi?error=invalid');
        exit();
    }
}

header('Location: /accedi?error=missing');
exit();