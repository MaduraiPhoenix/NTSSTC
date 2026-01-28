<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function checkAdminSession() {
    if (!isset($_SESSION['admin'])) {
        header("Location: login.php");
        exit;
    }
}
?>