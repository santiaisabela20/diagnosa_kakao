<!-- index.php --><?php
require_once 'koneksi.php';

// Jika user sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('petani/dashboard.php');
    }
}

$error = '';
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Diagnosa Penyakit Kakao</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .login-header {
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
            <div class="col-md-6 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <div class="kakao-icon">🍫</div>
                        <h3>Sistem Diagnosa</h3>
                        <p class="mb-0">Penyakit Tanaman Kakao</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="proses_login.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, #8B4513, #D2691E); border: none;">
                                    Login
                                </button>
                            </div>
                        </form>
                        
                        <hr>
                        
                        <div class="text-center">
                            <p class="mb-2">Belum punya akun?</p>
                            <a href="register.php" class="btn btn-outline-secondary">
                                Daftar Sebagai Petani
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="katalog.php" class="btn btn-link">
                                Lihat Katalog Penyakit
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-white">
                        <strong>Login Default:</strong><br>
                        Admin: admin / admin123<br>
                        Petani: petani1 / admin123
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>