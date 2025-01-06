<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

if (!isset($_SESSION['user'])) {
    session_start();
}
if ($_GET['user'] != $_SESSION['user']) {
    header('Location: /profilo/' . $_SESSION['user']);
    exit;
}

$fileGeneri = file_get_contents('../utils/bisac.json');
$fileGeneri = json_decode($fileGeneri, true);


$buttonsGeneri = '';
foreach ($fileGeneri as $key => $value) {
    $buttonsGeneri .= '<button type="button" class="button-genere" aria-pressed="false"><span aria-hidden="true">' . $value['emoji'] . "</span> " . $value['name'] . '</button>';
}

$page = getTemplatePage("Impostazioni profilo");

$generi = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'seleziona-generi.html');

$page = str_replace('<!-- [content] -->', $generi, $page);
$page = str_replace('<!-- [opzioniGeneri] -->', $buttonsGeneri, $page);
echo $page;