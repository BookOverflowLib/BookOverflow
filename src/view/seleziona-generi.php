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

$db = new DBAccess();

$fileGeneri = file_get_contents('../utils/bisac.json');
$fileGeneri = json_decode($fileGeneri, true);

$generiUtente = $db->get_generi_by_username($_SESSION['user']);
$generiUtente = json_decode($generiUtente[0]['generi_preferiti'], true);

$buttonsGeneri = '';
foreach ($fileGeneri as $key => $value) {
    if(in_array($key, $generiUtente)){
        $buttonsGeneri .= '<button type="button" class="button-genere button-pressed" aria-pressed="true" value="'.$key.'"><span aria-hidden="true">' . $value['emoji'] . "</span> " . $value['name'] . '</button>';
    } else {
        $buttonsGeneri .= '<button type="button" class="button-genere" aria-pressed="false" value="'.$key.'"><span aria-hidden="true">' . $value['emoji'] . "</span> " . $value['name'] . '</button>';
    }
}

$page = getTemplatePage("Impostazioni profilo");

$generi = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'seleziona-generi.html');

$page = str_replace('<!-- [content] -->', $generi, $page);
$page = str_replace('<!-- [opzioniGeneri] -->', $buttonsGeneri, $page);
echo $page;