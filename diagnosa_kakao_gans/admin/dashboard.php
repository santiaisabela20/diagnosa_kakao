<!-- dashboard.php --><?php
require_once '../koneksi.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('../index.php');
}

// Ambil statistik
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'petani'");
    $total_petani = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM penyakit");
    $total_penyakit = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM gejala");
    $total_gejala = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM riwayat_diagnosa");
    $total_diagnosa = $stmt->fetch()['total'];
    
    // Ambil diagnosa terbaru
    $stmt = $pdo->query("
        SELECT rd.*, u.username 
        FROM riwayat_diagnosa rd 
        JOIN users u ON rd.id_user = u.id 
        ORDER BY rd.tanggal DESC 
        LIMIT 5
    ");
    $diagnosa_terbaru = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $total_petani = $total_penyakit = $total_gejala = $total_diagnosa = 0;
    $diagnosa_terbaru = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Diagnosa Kakao</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .sidebar {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
            border-radius: 0;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
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
                    <div class="text-center mb-4">
                        <small>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></small>
                    </div>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php">
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
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users me-2"></i> Kelola User
                    </a>
                    <hr style="border-color: rgba(255,255,255,0.3);">
                    <a class="nav-link" href="../katalog.php">
                        <i class="fas fa-book me-2"></i> Lihat Katalog
                    </a>
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Dashboard Admin</h2>
                        <span class="text-muted"><?php echo date('d F Y'); ?></span>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card text-center p-3" style="background: linear-gradient(135deg, #17a2b8, #20c997);">
                                <div class="text-white">
                                    <i class="fas fa-users stat-icon"></i>
                                    <h3 class="mt-2"><?php echo $total_petani; ?></h3>
                                    <p class="mb-0">Total Petani</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card text-center p-3" style="background: linear-gradient(135deg, #fd7e14, #e83e8c);">
                                <div class="text-white">
                                    <i class="fas fa-bug stat-icon"></i>
                                    <h3 class="mt-2"><?php echo $total_penyakit; ?></h3>
                                    <p class="mb-0">Data Penyakit</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card text-center p-3" style="background: linear-gradient(135deg, #6f42c1, #6610f2);">
                                <div class="text-white">
                                    <i class="fas fa-list stat-icon"></i>
                                    <h3 class="mt-2"><?php echo $total_gejala; ?></h3>
                                    <p class="mb-0">Data Gejala</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card text-center p-3" style="background: linear-gradient(135deg, #28a745, #20c997);">
                                <div class="text-white">
                                    <i class="fas fa-chart-line stat-icon"></i>
                                    <h3 class="mt-2"><?php echo $total_diagnosa; ?></h3>
                                    <p class="mb-0">Total Diagnosa</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Diagnoses -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 shadow">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clock me-2"></i> Diagnosa Terbaru
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($diagnosa_terbaru) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <th>Petani</th>
                                                        <th>Gambar</th>
                                                        <th>Hasil Diagnosa</th>
                                                        <th>Confidence</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($diagnosa_terbaru as $diagnosa): ?>
                                                        <tr>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($diagnosa['tanggal'])); ?></td>
                                                            <td><?php echo htmlspecialchars($diagnosa['username']); ?></td>
                                                            <td>
                                                                <?php if ($diagnosa['gambar']): ?>
                                                                    <span class="badge bg-success">Ada Gambar</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">Manual</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($diagnosa['hasil']); ?></td>
                                                            <td>
                                                                <?php if ($diagnosa['confidence'] > 0): ?>
                                                                    <span class="badge bg-info"><?php echo number_format($diagnosa['confidence'] * 100, 1); ?>%</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                                            <p>Belum ada diagnosa yang dilakukan</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-0 shadow">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-bolt me-2"></i> Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <a href="penyakit_crud.php" class="btn btn-outline-primary w-100">
                                                <i class="fas fa-plus me-2"></i> Tambah Penyakit
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="gejala_crud.php" class="btn btn-outline-success w-100">
                                                <i class="fas fa-plus me-2"></i> Tambah Gejala
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="relasi_crud.php" class="btn btn-outline-warning w-100">
                                                <i class="fas fa-link me-2"></i> Atur Relasi
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="users.php" class="btn btn-outline-info w-100">
                                                <i class="fas fa-user-plus me-2"></i> Kelola User
                                            </a>
                                        </div>
                                    </div>
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
</html>