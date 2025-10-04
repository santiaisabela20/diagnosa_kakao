<?php
require_once '../koneksi.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('../index.php');
}

$message = '';

// Handle delete user
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Jangan hapus admin yang sedang login
    if ($user_id == $_SESSION['user_id']) {
        $message = "Tidak dapat menghapus akun yang sedang digunakan!";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = "User berhasil dihapus!";
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Handle reset password
if (isset($_POST['reset_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = 'password123'; // Password default
    
    try {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);
        $message = "Password berhasil direset ke: password123";
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Ambil semua data user
try {
    $stmt = $pdo->query("
        SELECT u.*, 
               COUNT(rd.id) as total_diagnosa,
               MAX(rd.tanggal) as last_diagnosa
        FROM users u 
        LEFT JOIN riwayat_diagnosa rd ON u.id = rd.id_user 
        GROUP BY u.id 
        ORDER BY u.role, u.username
    ");
    $users_list = $stmt->fetchAll();
} catch(PDOException $e) {
    $users_list = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .sidebar {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="p-3">
                    <h4 class="text-center mb-4">
                        <i class="fas fa-user-shield"></i> Admin Panel
                    </h4>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="penyakit_crud.php">
                        <i class="fas fa-bug me-2"></i> Kelola Penyakit
                    </a>
                    <a class="nav-link" href="gejala_crud.php">
                        <i class="fas fa-list me-2"></i> Kelola Gejala
                    </a>
                    <a class="nav-link" href="relasi_crud.php">
                        <i class="fas fa-project-diagram me-2"></i> Kelola Relasi
                    </a>
                    <a class="nav-link active" href="users.php">
                        <i class="fas fa-users me-2"></i> Kelola User
                    </a>
                    <hr style="border-color: rgba(255,255,255,0.3);">
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Manajemen User</h2>
                        <a href="../register.php" class="btn btn-primary" target="_blank">
                            <i class="fas fa-user-plus me-2"></i> Tambah Petani Baru
                        </a>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Tabel Data User -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i> Daftar User
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($users_list) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Role</th>
                                                <th>Terdaftar</th>
                                                <th>Total Diagnosa</th>
                                                <th>Diagnosa Terakhir</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users_list as $user): ?>
                                                <tr>
                                                    <td>
                                                        <i class="fas fa-user me-2"></i>
                                                        <?php echo htmlspecialchars($user['username']); ?>
                                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                            <span class="badge bg-info ms-2">Anda</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($user['role'] == 'admin'): ?>
                                                            <span class="badge bg-danger">
                                                                <i class="fas fa-shield-alt me-1"></i> Admin
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-seedling me-1"></i> Petani
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo $user['total_diagnosa']; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($user['last_diagnosa']): ?>
                                                            <?php echo date('d/m/Y H:i', strtotime($user['last_diagnosa'])); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">Belum pernah</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <!-- Reset Password -->
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                <button type="submit" name="reset_password" 
                                                                        class="btn btn-outline-warning" 
                                                                        title="Reset Password"
                                                                        onclick="return confirm('Reset password ke password123?')">
                                                                    <i class="fas fa-key"></i>
                                                                </button>
                                                            </form>
                                                            
                                                            <!-- Delete User -->
                                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                                <a href="?delete=<?php echo $user['id']; ?>" 
                                                                   class="btn btn-outline-danger" 
                                                                   title="Hapus User"
                                                                   onclick="return confirm('Yakin ingin menghapus user ini? Semua data diagnosa akan ikut terhapus!')">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <p>Belum ada data user</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-shield-alt fa-2x text-danger mb-2"></i>
                                    <h4><?php echo count(array_filter($users_list, function($u) { return $u['role'] == 'admin'; })); ?></h4>
                                    <p class="mb-0">Total Admin</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-seedling fa-2x text-success mb-2"></i>
                                    <h4><?php echo count(array_filter($users_list, function($u) { return $u['role'] == 'petani'; })); ?></h4>
                                    <p class="mb-0">Total Petani</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                                    <h4><?php echo array_sum(array_column($users_list, 'total_diagnosa')); ?></h4>
                                    <p class="mb-0">Total Diagnosa</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Panel -->
                    <div class="card mt-4 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informasi</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Fitur Reset Password:</h6>
                                    <ul class="list-unstyled">
                                        <li>• Password akan direset ke: <code>password123</code></li>
                                        <li>• User disarankan mengganti password setelah login</li>
                                        <li>• Notifikasi akan ditampilkan kepada admin</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Fitur Hapus User:</h6>
                                    <ul class="list-unstyled">
                                        <li>• Semua riwayat diagnosa akan ikut terhapus</li>
                                        <li>• Admin tidak dapat menghapus akun sendiri</li>
                                        <li>• Tindakan ini tidak dapat dibatalkan</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html><!-- users.php -->