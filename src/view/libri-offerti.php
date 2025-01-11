<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

//check if user is logged in
ensure_session();

$page = getTemplatePage("Libri offerti");

$libri_offerti = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'libri-offerti-desiderati.html');

$db = new DBAccess();

$libri_offerti_utente = $db->get_libri_offerti_by_username($_GET['user']);

$titoloIntestazione = 'Libri offerti';
$libri_offerti = str_replace('<!-- [titoloIntestazione] -->', $titoloIntestazione, $libri_offerti);
$libri_offerti_html = getLibriList($libri_offerti_utente, 'libri-offerti');
$libri_offerti = str_replace('<!-- [listaLibri] -->', $libri_offerti_html, $libri_offerti);


// aggiungi bottoni solo se è il suo profilo
if (check_ownership()) {
    $libri_offerti = addButtonsLibriList($libri_offerti, 'libri-offerti');
}

$page = str_replace('<!-- [content] -->', $libri_offerti, $page);
echo $page;