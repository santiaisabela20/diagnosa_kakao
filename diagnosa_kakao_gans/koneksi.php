<!-- koneksi.php --><?php
// Konfigurasi database
$host = "localhost";        // Host database
$username = "root";         // Username database
$password = "";             // Password database (kosong untuk XAMPP default)
$database = "diagnosa_kakao_db";  // Nama database

try {
    // Membuat koneksi menggunakan PDO
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    
    // Set error mode ke exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi untuk sanitasi input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Fungsi untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Start session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>