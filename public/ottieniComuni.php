<?php

require_once '../src/model/dbAPI.php';
require_once '../src/model/registrationSelect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['provinciaSelezionata'])) {
    $provinciaSelezionata = $_POST['provinciaSelezionata'];
    $db = new DBAccess();
    $jsonList = array();
    $jsonList['comuni'] = optionComuni($provinciaSelezionata);
    exit(json_encode($jsonList));
}
