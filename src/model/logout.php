<?php
ensure_session();
session_destroy();
header('Location: /accedi');
exit();