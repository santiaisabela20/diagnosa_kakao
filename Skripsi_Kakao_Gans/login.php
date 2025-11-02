<?php
require_once 'config.php';

// ... (Blok PHP Anda dari baris 5 - 101 tetap sama) ...
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: petani/dashboard.php');
    }
    exit();
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        if (!empty($username) && !empty($password)) {
            $conn = getConnection();
            $stmt = $conn->prepare("SELECT id_user, nama, password, role FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['role'] = $user['role'];
                    if ($user['role'] === 'admin') {
                        header('Location: admin/dashboard.php');
                    } else {
                        header('Location: petani/dashboard.php');
                    }
                    exit();
                } else {
                    $error = 'Username atau password salah!';
                }
            } else {
                $error = 'User tidak ditemukan!';
            }
            $stmt->close();
            $conn->close();
        } else {
            $error = 'Username dan password harus diisi!';
        }
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
        $nama = $_POST['nama'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        if (empty($nama) || empty($username) || empty($password) || empty($confirm_password)) {
            $error = 'Semua field harus diisi!';
        } elseif ($password !== $confirm_password) {
            $error = 'Password dan konfirmasi password tidak cocok!';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter!';
        } else {
            $conn = getConnection();
            $stmt = $conn->prepare("SELECT id_user FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error = 'Username sudah digunakan!';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, 'petani')");
                $stmt->bind_param("sss", $nama, $username, $hashed_password);
                if ($stmt->execute()) {
                    $success = 'Registrasi berhasil! Silakan login.';
                } else {
                    $error = 'Terjadi kesalahan saat registrasi!';
                }
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register - Diagnosa Kakao</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        /* Kontainer utama yang menggabungkan gambar dan form */
        .main-container {
            display: flex;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 900px;
            width: 100%;
            overflow: hidden; 
        }
        
        /* Kolom kiri untuk gambar */
        .login-image {
            width: 50%;
            background: url('images/bg-tempat.jpg') no-repeat center center;
            background-size: cover;
        }
        
        /* Kolom kanan untuk form */
        .form-container {
            width: 50%;
            padding: 40px;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #333;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-register {
            background: #28a745;
            color: white;
        }
        .btn-register:hover {
            background: #218838;
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
            font-size: 14px;
            text-align: center;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            text-align: center;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer; /* Ubah jadi pointer */
        }
        
        .links a:hover {
            text-decoration: underline;
        }

        /* Styling untuk input icon */
        .input-icon {
            position: relative;
        }
        .input-icon input {
            padding-left: 40px;
        }
        .input-icon:before {
            content: attr(data-icon);
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #999;
        }

        /* --- INI BAGIAN BARU UNTUK RESPONSIVE --- */
        @media (max-width: 900px) {
            /* Ubah layout jadi vertikal */
            .main-container {
                flex-direction: column;
                max-width: 500px; /* Buat kotaknya lebih pas di HP */
                width: 100%;
            }
            
            /* Kolom gambar pindah ke atas */
            .login-image {
                width: 100%;
                height: 250px; /* Beri tinggi tetap untuk gambar */
                order: -1; /* Pindahkan gambar ke atas */
            }
            
            /* Kolom form jadi lebar penuh */
            .form-container {
                width: 100%;
                padding: 30px; /* Sedikit kurangi padding di HP */
            }

            .logo {
                font-size: 60px;
                margin-bottom: 15px;
            }

            h2 {
                font-size: 24px;
            }
        }

        @media (max-width: 500px) {
            /* CSS Khusus untuk layar sangat kecil */
            body {
                /* Hapus padding body agar kotak bisa nempel */
                padding: 0; 
            }

            .main-container {
                /* Hapus radius di layar HP agar nempel */
                border-radius: 0; 
                /* Pastikan mengisi tinggi layar */
                min-height: 100vh;
            }

            .form-container {
                padding: 30px 20px; /* Kurangi padding lagi */
            }
        }
        /* --- AKHIR BAGIAN BARU --- */

    </style>
</head>
<body>
    
    <div class="main-container">
        <div class="login-image">
            </div>
        
        <div class="form-container">
            <div class="logo-container">
                <div class="logo">🌱</div>
                <h2>Sistem Diagnosa Penyakit Kakao</h2>
                <p class="subtitle">Desa Atar Lebar</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" id="login-form">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-icon" data-icon="👤">
                        <input type="text" id="username" name="username" required placeholder="Masukkan username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon" data-icon="🔒">
                        <input type="password" id="password" name="password" required placeholder="Masukkan password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login">🚀 Login</button>
                
                <div class="links">
                    <a id="show-register-link">📝 Belum punya akun? Daftar di sini</a>
                </div>
            </form>
            
            <form method="POST" action="login.php" id="register-form" style="display:none;">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <div class="input-icon" data-icon="👤">
                        <input type="text" id="nama" name="nama" required placeholder="Masukkan nama lengkap">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username_reg">Username</label>
                    <div class="input-icon" data-icon="📧">
                        <input type="text" id="username_reg" name="username" required placeholder="Masukkan username baru">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password_reg">Password</label>
                    <div class="input-icon" data-icon="🔒">
                        <input type="password" id="password_reg" name="password" required minlength="6" placeholder="Minimal 6 karakter">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <div class="input-icon" data-icon="🔒">
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ulangi password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-register">Daftar</button>

                <div class="links">
                    <a id="show-login-link">✅ Sudah punya akun? Login di sini</a>
                </div>
            </form>

        </div>
    </div>

    <script>
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const showRegisterLink = document.getElementById('show-register-link');
        const showLoginLink = document.getElementById('show-login-link');

        // Fungsi untuk membersihkan pesan error/sukses
        const clearMessages = () => {
            const errorMsg = document.querySelector('.error');
            const successMsg = document.querySelector('.success');
            if (errorMsg) errorMsg.style.display = 'none';
            if (successMsg) successMsg.style.display = 'none';
        };

        showRegisterLink.addEventListener('click', (e) => {
            e.preventDefault();
            clearMessages();
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
        });

        showLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            clearMessages();
            registerForm.style.display = 'none';
            loginForm.style.display = 'block';
        });

        // Tampilkan form yang benar jika ada error (setelah PHP reload)
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
            echo "loginForm.style.display = 'none'; registerForm.style.display = 'block';";
        }
        ?>
    </script>
</body>
</html>