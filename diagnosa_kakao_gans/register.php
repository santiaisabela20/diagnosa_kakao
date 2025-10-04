<!-- register.php --><?php
require_once 'koneksi.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi!';
    } elseif (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password != $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        try {
            // Cek apakah username sudah ada
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $error = 'Username sudah digunakan!';
            } else {
                // Insert user baru
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'petani')");
                $stmt->execute([$username, $hashed_password]);
                
                $success = 'Registrasi berhasil! Silakan login.';
            }
        } catch(PDOException $e) {
            $error = 'Terjadi kesalahan sistem!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Petani - Sistem Diagnosa Penyakit Kakao</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .kakao-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="register-card">
                    <div class="register-header">
                        <div class="kakao-icon">🌱</div>
                        <h3>Registrasi Petani</h3>
                        <p class="mb-0">Bergabung dengan sistem diagnosa kakao</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                       required>
                                <small class="form-text text-muted">Minimal 3 karakter</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="form-text text-muted">Minimal 6 karakter</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" 
                                        style="background: linear-gradient(135deg, #8B4513, #D2691E); border: none;">
                                    Daftar
                                </button>
                            </div>
                        </form>
                        
                        <hr>
                        
                        <div class="text-center">
                            <p class="mb-2">Sudah punya akun?</p>
                            <a href="index.php" class="btn btn-outline-secondary">
                                Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>