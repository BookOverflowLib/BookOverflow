<?php

$TEMPLATES_PATH = __DIR__ . '/' . '/../src/templates/';
$MODEL_PATH = __DIR__ . '/' . '/../src/model/';
$PAGES_PATH = __DIR__ . '/' . '/../src/view/';

require_once $GLOBALS['MODEL_PATH'] . 'utils.php';
$prefix = getPrefix();

//// Print the file that called this script
//$backtrace = debug_backtrace();
//$caller = $backtrace[0]['file'];
//echo "Called by: " . $caller . "<br>";
