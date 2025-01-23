<?php
require_once __DIR__ . '/' . '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
session_destroy();
header('Location: /accedi');
exit();