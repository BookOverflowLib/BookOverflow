<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';
require_once $GLOBALS['MODEL_PATH'] . 'registration-select.php';


$page = getTemplatePage('Registrati');
$registrati = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'registrati.html');

$provinceList = optionProvince();
$registrati = str_replace('<!-- [province] -->', $provinceList, $registrati);
$page = str_replace('<!-- [content] -->', $registrati, $page);