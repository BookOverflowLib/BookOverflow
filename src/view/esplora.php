<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
// per il momento solo utenti loggati possono accedere a questa pagina
ensure_login();

$db = new DBAccess();
$dbOK = $db->open_connection();

$page = getTemplatePage("Esplora");
$esplora = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'esplora.html');

$match_per_te = $db->get_match_per_te_by_user($_SESSION['user']);
$esplora = str_replace('<!-- [matchPerTe] -->', getLibriCopertinaGrande($match_per_te, 4), $esplora);

$match_potrebbe_piacerti = $db->get_potrebbe_piacerti_by_user($_SESSION['user']);
$esplora = str_replace('<!-- [matchPotrebbePiacerti] -->', getLibriCopertinaGrande($match_potrebbe_piacerti, 4), $esplora);

$page = str_replace('<!-- [content] -->', $esplora, $page);
echo $page;