<?php
if(!isset($_SESSION)) {
    session_start();
}

if(isset($_SESSION['user'])) {
    header('Location: /profilo/' . $_SESSION['user']);
    exit();
}

require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

$page = getTemplatePage('Accedi');
$accedi = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'accedi.html');

// Handle error messages
if (isset($_GET['error'])) {
    $errorMessage = '';
    switch ($_GET['error']) {
        case 'wrong-password':
            $errorMessage = '<p class="input-error-regular">La password inserita non è valida</p>';
            break;
        case 'missing':
            $errorMessage = '<p class="input-error-regular">Inserire tutti i campi richiesti</p>';
            break;
        case 'user-not-found':
            $errorMessage = '<p class="input-error-regular">Utente non registrato</p>';
            break;
    }
    // Insert error message after the h1
    $accedi = str_replace(    
        '</h1>',
        '</h1>' . $errorMessage,
        $accedi
    );
}

$page = str_replace('<!-- [content] -->', $accedi, $page);
echo $page;