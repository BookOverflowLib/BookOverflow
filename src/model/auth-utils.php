<?php
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /accedi");
        exit();
    }
}

function logout() {
    session_start();
    session_destroy();
    header("Location: /accedi");
    exit();
}