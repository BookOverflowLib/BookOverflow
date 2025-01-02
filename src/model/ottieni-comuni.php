<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'registration-select.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['provinciaSelezionata'])) {
	$provinciaSelezionata = $_POST['provinciaSelezionata'];
	$db = new DBAccess();
	$jsonList = array();
	$jsonList['comuni'] = optionComuni($provinciaSelezionata);
	exit(json_encode($jsonList));
}
