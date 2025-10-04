<!-- proses_login.php --><?php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username dan password harus diisi!';
        redirect('index.php');
    }
    
    try {
        // Cari user berdasarkan username
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect berdasarkan role
            if ($user['role'] == 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('petani/dashboard.php');
            }
        } else {
            $_SESSION['error'] = 'Username atau password salah!';
            redirect('index.php');
        }
        
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Terjadi kesalahan sistem!';
        redirect('index.php');
    }
} else {
    redirect('index.php');
}
?>