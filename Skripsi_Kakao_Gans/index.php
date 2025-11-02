<?php
require_once 'config.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: petani/upload.php');
    }
    exit();
}

// Redirect ke halaman login
header('Location: login.php');
exit();
?>