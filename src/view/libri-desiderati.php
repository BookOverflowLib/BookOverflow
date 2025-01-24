<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

//check if user is logged in
ensure_session();

$page = getTemplatePage("Lista dei desideri");

$libri_desiderati = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'libri-offerti-desiderati.html');

$db = new DBAccess();

$libri_desiderati_utente = $db->get_libri_desiderati_by_username($_GET['user']);

$titoloIntestazione = 'Lista dei desideri';
$libri_desiderati = str_replace('<!-- [titoloIntestazione] -->', $titoloIntestazione, $libri_desiderati);
$libri_desiderati_html = getLibriList($libri_desiderati_utente, 'libri-desiderati');
$libri_desiderati = str_replace('<!-- [listaLibri] -->', $libri_desiderati_html, $libri_desiderati);

// aggiungi bottoni solo se è il suo profilo
if(check_ownership()) {
    $libri_desiderati = addButtonsLibriList($libri_desiderati, 'libri-desiderati');
}

$page = str_replace('<!-- [content] -->', $libri_desiderati, $page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;