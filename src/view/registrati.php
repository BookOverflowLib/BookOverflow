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
    // TODO
    $errorMessage = '';
    switch ($_GET['error']) {
        case 'missing':
            $errorMessage = '<p class="input-error-regular">Inserire tutti i campi richiesti</p>';
            break;
        case 'password-mismatch':
            $errorMessage = '<p class="input-error-regular">Le password non corrispondono</p>';
            break;
        case 'username-taken':
            $errorMessage = '<p class="input-error-regular">Username già in uso</p>';
            break;
        case 'email-taken':
            $errorMessage = '<p class="input-error-regular">Email già in uso</p>';
            break;
        case 'generic':
            $errorMessage = '<p class="input-error-regular">Errore generico</p>';
            break;
    }
}

echo $page;
