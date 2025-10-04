<!-- dashboard.php --><?php
require_once '../koneksi.php';

// Cek apakah user sudah login dan role petani
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'petani') {
    redirect('../index.php');
}

// Ambil statistik petani
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM riwayat_diagnosa WHERE id_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_diagnosa = $stmt->fetch()['total'];
    
    // Ambil riwayat diagnosa terbaru
    $stmt = $pdo->prepare("
        SELECT * FROM riwayat_diagnosa 
        WHERE id_user = ? 
        ORDER BY tanggal DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $riwayat_terbaru = $stmt->fetchAll();
    
    // Hitung diagnosa per penyakit
    $stmt = $pdo->prepare("
        SELECT hasil, COUNT(*) as jumlah 
        FROM riwayat_diagnosa 
        WHERE id_user = ? 
        GROUP BY hasil 
        ORDER BY jumlah DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $statistik_penyakit = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $total_diagnosa = 0;
    $riwayat_terbaru = [];
    $statistik_penyakit = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petani - Sistem Diagnosa Kakao</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .sidebar {
            background: linear-gradient(135deg, #228B22, #32CD32);
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
        .feature-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .stat-card {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-radius: 15px;
            border: none;
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
                        <i class="fas fa-seedling"></i> Petani Panel
                    </h4>
                    <div class="text-center mb-4">
                        <small>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></small>
                    </div>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="upload.php">
                        <i class="fas fa-camera me-2"></i> Diagnosa Gambar
                    </a>
                    <a class="nav-link" href="gejala.php">
                        <i class="fas fa-list-check me-2"></i> Diagnosa Manual
                    </a>
                    <a class="nav-link" href="../riwayat.php">
                        <i class="fas fa-history me-2"></i> Riwayat Diagnosa
                    </a>
                    <hr style="border-color: rgba(255,255,255,0.3);">
                    <a class="nav-link" href="../katalog.php">
                        <i class="fas fa-book me-2"></i> Katalog Penyakit
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
                        <h2>Dashboard Petani</h2>
                        <span class="text-muted"><?php echo date('d F Y'); ?></span>
                    </div>

                    <!-- Welcome Card -->
                    <div class="card mb-4 border-0 shadow" style="background: linear-gradient(135deg, #ffffff, #f8f9fa);">
                        <div class="card-body text-center py-5">
                            <h3 class="text-success mb-3">
                                <i class="fas fa-leaf me-2"></i>
                                Selamat Datang di Sistem Diagnosa Kakao
                            </h3>
                            <p class="lead mb-4">Diagnosa penyakit tanaman kakao Anda dengan mudah dan akurat</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <a href="upload.php" class="btn btn-success btn-lg w-100">
                                        <i class="fas fa-camera me-2"></i> Diagnosa dengan Gambar
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="gejala.php" class="btn btn-outline-success btn-lg w-100">
                                        <i class="fas fa-list-check me-2"></i> Diagnosa Manual
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card stat-card text-center p-4">
                                <i class="fas fa-chart-line fa-3x mb-3" style="opacity: 0.8;"></i>
                                <h3><?php echo $total_diagnosa; ?></h3>
                                <p class="mb-0">Total Diagnosa</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card feature-card text-center p-4">
                                <i class="fas fa-camera feature-icon text-primary"></i>
                                <h5>Diagnosa Gambar</h5>
                                <p class="text-muted">Upload foto tanaman kakao untuk diagnosa otomatis menggunakan AI</p>
                                <a href="upload.php" class="btn btn-primary">Mulai Diagnosa</a>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card feature-card text-center p-4">
                                <i class="fas fa-clipboard-check feature-icon text-success"></i>
                                <h5>Diagnosa Manual</h5>
                                <p class="text-muted">Pilih gejala yang terlihat untuk mendapatkan diagnosa berbasis aturan</p>
                                <a href="gejala.php" class="btn btn-success">Pilih Gejala</a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent History -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card border-0 shadow">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clock me-2"></i> Riwayat Diagnosa Terbaru
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($riwayat_terbaru) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <th>Metode</th>
                                                        <th>Hasil</th>
                                                        <th>Akurasi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($riwayat_terbaru as $riwayat): ?>
                                                        <tr>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($riwayat['tanggal'])); ?></td>
                                                            <td>
                                                                <?php if ($riwayat['gambar']): ?>
                                                                    <span class="badge bg-primary">
                                                                        <i class="fas fa-camera me-1"></i> Gambar
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-success">
                                                                        <i class="fas fa-list me-1"></i> Manual
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($riwayat['hasil']); ?></td>
                                                            <td>
                                                                <?php if ($riwayat['confidence'] > 0): ?>
                                                                    <span class="badge bg-info"><?php echo number_format($riwayat['confidence'] * 100, 1); ?>%</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-center">
                                            <a href="../riwayat.php" class="btn btn-outline-primary">
                                                Lihat Semua Riwayat
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-history fa-3x mb-3"></i>
                                            <p>Belum ada riwayat diagnosa</p>
                                            <p>Mulai diagnosa pertama Anda!</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-0 shadow">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-pie me-2"></i> Statistik Penyakit
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($statistik_penyakit) > 0): ?>
                                        <?php foreach ($statistik_penyakit as $stat): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($stat['hasil']); ?></small>
                                                </div>
                                                <span class="badge bg-primary"><?php echo $stat['jumlah']; ?>x</span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-chart-pie fa-2x mb-2"></i>
                                            <p class="mb-0">Belum ada data</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Quick Links -->
                            <div class="card border-0 shadow mt-3">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-external-link-alt me-2"></i> Link Berguna
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="../katalog.php" class="list-group-item list-group-item-action border-0">
                                            <i class="fas fa-book text-primary me-2"></i> Katalog Penyakit
                                        </a>
                                        <a href="../riwayat.php" class="list-group-item list-group-item-action border-0">
                                            <i class="fas fa-history text-success me-2"></i> Riwayat Lengkap
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>