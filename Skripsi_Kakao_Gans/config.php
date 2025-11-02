<?php
// Prevent double-inclusion of this config file
if (defined('APP_CONFIG_INCLUDED')) {
    return;
}
define('APP_CONFIG_INCLUDED', true);
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'diagnosa_kakao_gans');

// Koneksi Database
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    return $conn;
}

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk cek role admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi untuk cek role petani
function isPetani() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'petani';
}

// Fungsi untuk redirect jika tidak login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

// Fungsi untuk redirect jika bukan admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

// Fungsi untuk redirect jika bukan petani
function requirePetani() {
    requireLogin();
    if (!isPetani()) {
        header('Location: index.php');
        exit();
    }
}
// ==========================================
// PYTHON & MODEL CONFIGURATION
// ==========================================

// PYTHON & MODEL CONFIG (gunakan guard supaya define tidak duplikat)
// Path Python - sesuaikan dengan versi/path pada sistem Anda
if (!defined('PYTHON_PATH')) {
    define('PYTHON_PATH', 'C:\\Users\\a c e r\\AppData\\Local\\Programs\\Python\\Python311\\python.exe'); // detected Python location
}

// Path script predict.py
if (!defined('MODEL_SCRIPT')) {
    define('MODEL_SCRIPT', __DIR__ . '/model/predict.py');
}

// Enable error logging untuk debugging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Cek apakah file script ada (untuk debugging)
if (!file_exists(MODEL_SCRIPT)) {
    error_log("ERROR: predict.py tidak ditemukan di: " . MODEL_SCRIPT);
}

// Cek apakah Python executable ada
if (!file_exists(PYTHON_PATH)) {
    error_log("ERROR: Python tidak ditemukan di: " . PYTHON_PATH);
}

// Base URL
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/diagnosa_kakao_gans/');
}
?>