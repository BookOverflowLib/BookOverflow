<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
if ($_GET['user'] != $_SESSION['user']) {
	$prefix = getPrefix();
	header('Location: ' . $prefix . '/profilo/' . $_SESSION['user']);
	exit;
}

$db = new DBAccess();

$fileGeneri = file_get_contents('../utils/bisac.json');
$fileGeneri = json_decode($fileGeneri, true);

$generiUtente = $db->get_generi_by_username($_SESSION['user']);
if ($generiUtente[0]['generi_preferiti'] != null) {
	$generiUtente = json_decode($generiUtente[0]['generi_preferiti'], true);
}

$buttonsGeneri = '';
foreach ($fileGeneri as $key => $value) {
	if (in_array($key, $generiUtente)) {
		$buttonsGeneri .= '<button type="button" class="button-genere button-pressed" aria-pressed="true" value="' . $key . '"><span aria-hidden="true">' . $value['emoji'] . "</span> " . $value['name'] . '</button>';
	} else {
		$buttonsGeneri .= '<button type="button" class="button-genere" aria-pressed="false" value="' . $key . '"><span aria-hidden="true">' . $value['emoji'] . "</span> " . $value['name'] . '</button>';
	}
}

$page = getTemplatePage("Generi preferiti");

$generi = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'seleziona-generi.html');

$page = str_replace('<!-- [content] -->', $generi, $page);
$page = str_replace('<!-- [opzioniGeneri] -->', $buttonsGeneri, $page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;