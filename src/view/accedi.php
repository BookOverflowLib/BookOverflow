<?php
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
            $errorMessage = '<p class="error">La password inserita non è valida</p>';
            break;
        case 'missing':
            $errorMessage = '<p class="error">Inserire tutti i campi richiesti</p>';
            break;
        case 'user-not-found':
            $errorMessage = '<p class="error">Utente non registrato</p>';
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