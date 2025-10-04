<!-- katalog.php --><?php
require_once 'koneksi.php';

// Ambil semua data penyakit
try {
    $stmt = $pdo->query("SELECT * FROM penyakit ORDER BY kode");
    $penyakit_list = $stmt->fetchAll();
} catch(PDOException $e) {
    $penyakit_list = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Penyakit Kakao</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(135deg, #8B4513, #D2691E) !important;
        }
        .disease-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 2rem;
        }
        .disease-card:hover {
            transform: translateY(-5px);
        }
        .disease-header {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }
        .disease-code {
            background: rgba(255,255,255,0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
        }
        .hero-section {
            background: linear-gradient(135deg, rgba(139, 69, 19, 0.8), rgba(210, 105, 30, 0.8)), 
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><rect width="1000" height="1000" fill="%23f8f9fa"/><circle cx="200" cy="200" r="50" fill="%23e9ecef" opacity="0.3"/><circle cx="800" cy="300" r="80" fill="%23e9ecef" opacity="0.2"/><circle cx="500" cy="600" r="60" fill="%23e9ecef" opacity="0.4"/></svg>');
            background-size: cover;
            background-position: center;
            padding: 4rem 0;
            color: white;
            text-align: center;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <span style="font-size: 1.5rem;">🍫</span>
                Katalog Penyakit Kakao
            </a>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Katalog Penyakit Tanaman Kakao</h1>
            <p class="lead mb-4">Referensi lengkap untuk mengenal berbagai penyakit yang menyerang tanaman kakao</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="alert alert-light border-0 shadow-sm" style="background: rgba(255,255,255,0.9);">
                        <div class="text-dark">
                            <strong>📚 Panduan Penggunaan:</strong><br>
                            Jelajahi berbagai penyakit kakao di bawah ini untuk mempelajari gejala, deskripsi, dan solusi pengendaliannya.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row">
            <?php if (count($penyakit_list) > 0): ?>
                <?php foreach ($penyakit_list as $penyakit): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card disease-card">
                            <div class="disease-header">
                                <div class="disease-code"><?php echo htmlspecialchars($penyakit['kode']); ?></div>
                                <h5 class="card-title mt-2 mb-0"><?php echo htmlspecialchars($penyakit['nama']); ?></h5>
                            </div>
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">
                                    <i class="text-info">ℹ️</i> Deskripsi
                                </h6>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($penyakit['deskripsi'])); ?></p>
                                
                                <h6 class="card-subtitle mb-3 text-muted">
                                    <i class="text-success">💡</i> Solusi Pengendalian
                                </h6>
                                <div class="solution-text">
                                    <?php 
                                    $solusi_lines = explode('\n', $penyakit['solusi']);
                                    echo '<ul class="list-unstyled">';
                                    foreach ($solusi_lines as $line) {
                                        if (trim($line)) {
                                            echo '<li class="mb-1"><i class="text-success">✓</i> ' . htmlspecialchars(trim($line)) . '</li>';
                                        }
                                    }
                                    echo '</ul>';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center border-0 shadow-sm" role="alert">
                        <div class="display-1 mb-3">🔍</div>
                        <h4 class="alert-heading">Belum Ada Data Penyakit</h4>
                        <p class="mb-0">Katalog penyakit sedang dalam proses pengembangan. Silakan kembali lagi nanti.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(139, 69, 19, 0.05), rgba(210, 105, 30, 0.05));">
                    <div class="card-body text-center py-4">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="display-1">🌱</div>
                            </div>
                            <div class="col-md-9 text-md-start">
                                <h4 class="card-title mb-3">Tentang Katalog Ini</h4>
                                <p class="card-text mb-0">
                                    Katalog ini berisi informasi komprehensif tentang berbagai penyakit yang dapat menyerang tanaman kakao. 
                                    Setiap entri dilengkapi dengan deskripsi detail dan solusi pengendalian yang dapat diterapkan oleh petani.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="display-4 text-primary mb-2">📊</div>
                        <h5 class="card-title">Total Penyakit</h5>
                        <p class="card-text display-6 fw-bold text-primary"><?php echo count($penyakit_list); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="display-4 text-success mb-2">🎯</div>
                        <h5 class="card-title">Solusi Tersedia</h5>
                        <p class="card-text display-6 fw-bold text-success"><?php echo count($penyakit_list); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="display-4 text-warning mb-2">📚</div>
                        <h5 class="card-title">Referensi</h5>
                        <p class="card-text display-6 fw-bold text-warning">Lengkap</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-5 py-5" style="background: linear-gradient(135deg, #2c3e50, #34495e); color: white;">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <span style="font-size: 1.2rem;">🍫</span>
                        Katalog Penyakit Kakao
                    </h5>
                    <p class="mb-0">
                        Sumber informasi terpercaya untuk pengenalan dan pengendalian penyakit tanaman kakao.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-2">
                        <strong>Disclaimer:</strong> Informasi ini bersifat edukatif dan sebaiknya dikonsultasikan dengan ahli pertanian.
                    </p>
                    <p class="mb-0">
                        © <?php echo date('Y'); ?> Katalog Penyakit Tanaman Kakao
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>