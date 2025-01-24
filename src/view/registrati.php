<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';
require_once $GLOBALS['MODEL_PATH'] . 'registration-select.php';


$page = getTemplatePage('Registrati');
$registrati = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'registrati.html');

$provinceList = optionProvince();
$registrati = str_replace('<!-- [province] -->', $provinceList, $registrati);
$page = str_replace('<!-- [content] -->', $registrati, $page);

if (isset($_GET['error'])) {
    // TODO: cambiare GET in SESSION
    $errorMessage = '<p class="input-error-regular role="alert">';
    switch ($_GET['error']) {
        case 'missing':
            $errorMessage .= 'Inserire tutti i campi richiesti';
            break;
        case 'password-mismatch':
            $errorMessage .= 'Le password non corrispondono';
            break;
        case 'username-taken':
            $errorMessage .= 'Username già in uso';
            break;
        case 'email-taken':
            $errorMessage .= 'Email già in uso';
            break;
        case 'generic':
            $errorMessage .= 'Errore generico';
            break;
    }
    $errorMessage .= '</p>';

    $page = str_replace('<!-- [error] -->', $errorMessage, $page);
}
$page = populateWebdirPrefixPlaceholders($page);
echo $page;
