<!-- gejala.php --><?php
require_once '../koneksi.php';

// Cek apakah user sudah login dan role petani
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'petani') {
    redirect('../index.php');
}

$message = '';
$hasil_diagnosa = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gejala'])) {
    $selected_gejala = $_POST['gejala'];
    
    if (empty($selected_gejala)) {
        $message = 'Pilih minimal satu gejala!';
    } else {
        try {
            // Ambil skor untuk setiap penyakit berdasarkan gejala yang dipilih
            $gejala_ids = implode(',', array_map('intval', $selected_gejala));
            
            $stmt = $pdo->query("
                SELECT p.*, 
                       SUM(pg.bobot) as total_score,
                       COUNT(pg.id) as matched_symptoms
                FROM penyakit p
                JOIN penyakit_gejala pg ON p.id = pg.id_penyakit
                WHERE pg.id_gejala IN ($gejala_ids)
                GROUP BY p.id
                ORDER BY total_score DESC, matched_symptoms DESC
            ");
            
            $results = $stmt->fetchAll();
            
            if (count($results) > 0) {
                // Ambil hasil dengan skor tertinggi
                $top_result = $results[0];
                $hasil = $top_result['nama'];
                $confidence = min($top_result['total_score'] / count($selected_gejala), 1.0);
                
                // Simpan ke database
                $stmt = $pdo->prepare("INSERT INTO riwayat_diagnosa (id_user, hasil, confidence) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $hasil, $confidence]);
                
                $hasil_diagnosa = [
                    'hasil' => $hasil,
                    'confidence' => $confidence,
                    'all_results' => $results,
                    'selected_count' => count($selected_gejala)
                ];
                
                $message = 'Diagnosa berhasil!';
            } else {
                $message = 'Tidak ditemukan penyakit yang cocok dengan gejala yang dipilih.';
            }
            
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
        }
    }
}

// Ambil semua gejala
try {
    $stmt = $pdo->query("SELECT * FROM gejala ORDER BY kode");
    $all_gejala = $stmt->fetchAll();
} catch(PDOException $e) {
    $all_gejala = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosa Manual - Sistem Diagnosa Kakao</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); }
        .sidebar {
            background: linear-gradient(135deg, #228B22, #32CD32);
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
        .gejala-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .gejala-card:hover {
            border-color: #28a745;
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
        }
        .gejala-card.selected {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }
        .gejala-checkbox {
            transform: scale(1.2);
        }
        .result-card {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-radius: 15px;
            border: none;
        }
        .confidence-bar {
            height: 10px;
            background: rgba(255,255,255,0.3);
            border-radius: 5px;
            overflow: hidden;
        }
        .confidence-fill {
            height: 100%;
            background: white;
            border-radius: 5px;
            transition: width 0.5s ease;
        }
        .alternative-result {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.2);
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
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="upload.php">
                        <i class="fas fa-camera me-2"></i> Diagnosa Gambar
                    </a>
                    <a class="nav-link active" href="gejala.php">
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
                    <h2 class="mb-4">
                        <i class="fas fa-list-check me-2"></i> Diagnosa Manual Berdasarkan Gejala
                    </h2>

                    <?php if ($message): ?>
                        <div class="alert <?php echo $hasil_diagnosa ? 'alert-success' : 'alert-warning'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!$hasil_diagnosa): ?>
                    <!-- Form Pilih Gejala -->
                    <div class="card border-0 shadow mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-check-square me-2"></i> Pilih Gejala yang Terlihat
                            </h5>
                            <small class="text-muted">Centang semua gejala yang Anda amati pada tanaman kakao</small>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="gejalaDiagnosaForm">
                                <?php if (count($all_gejala) > 0): ?>
                                    <div class="row">
                                        <?php foreach ($all_gejala as $gejala): ?>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="gejala-card p-3" onclick="toggleGejala(<?php echo $gejala['id']; ?>)">
                                                    <div class="form-check">
                                                        <input class="form-check-input gejala-checkbox" 
                                                               type="checkbox" 
                                                               name="gejala[]" 
                                                               value="<?php echo $gejala['id']; ?>" 
                                                               id="gejala_<?php echo $gejala['id']; ?>">
                                                        <label class="form-check-label w-100" for="gejala_<?php echo $gejala['id']; ?>">
                                                            <div class="d-flex align-items-start">
                                                                <span class="badge bg-success me-2"><?php echo htmlspecialchars($gejala['kode']); ?></span>
                                                                <div>
                                                                    <div class="fw-bold"><?php echo htmlspecialchars($gejala['nama']); ?></div>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <div>
                                            <span id="selectedCount">0</span> gejala dipilih
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-outline-secondary me-2" onclick="clearAll()">
                                                <i class="fas fa-times me-2"></i> Bersihkan Semua
                                            </button>
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-search me-2"></i> Mulai Diagnosa
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                        <p>Belum ada data gejala. Hubungi administrator.</p>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Hasil Diagnosa -->
                    <?php if ($hasil_diagnosa): ?>
                    <div class="card result-card shadow mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-microscope me-2"></i> Hasil Diagnosa Manual
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 class="mb-3">Penyakit yang Teridentifikasi:</h4>
                                    <h3 class="mb-4"><?php echo htmlspecialchars($hasil_diagnosa['hasil']); ?></h3>
                                    
                                    <h6 class="mb-2">Tingkat Kepercayaan:</h6>
                                    <div class="confidence-bar mb-2">
                                        <div class="confidence-fill" style="width: <?php echo ($hasil_diagnosa['confidence'] * 100); ?>%"></div>
                                    </div>
                                    <p class="mb-3"><?php echo number_format($hasil_diagnosa['confidence'] * 100, 1); ?>%</p>
                                    
                                    <p class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Berdasarkan <?php echo $hasil_diagnosa['selected_count']; ?> gejala yang dipilih
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-grid gap-2">
                                        <a href="../katalog.php" class="btn btn-light">
                                            <i class="fas fa-book me-2"></i> Lihat Detail Penyakit
                                        </a>
                                        <a href="../riwayat.php" class="btn btn-outline-light">
                                            <i class="fas fa-history me-2"></i> Lihat Riwayat
                                        </a>
                                        <a href="gejala.php" class="btn btn-outline-light">
                                            <i class="fas fa-redo me-2"></i> Diagnosa Lagi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kemungkinan Lain -->
                    <?php if (count($hasil_diagnosa['all_results']) > 1): ?>
                    <div class="card border-0 shadow">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i> Kemungkinan Penyakit Lain
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php 
                                $alternative_results = array_slice($hasil_diagnosa['all_results'], 1, 2);
                                foreach ($alternative_results as $result): 
                                    $alt_confidence = min($result['total_score'] / $hasil_diagnosa['selected_count'], 1.0);
                                ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="alternative-result p-3">
                                            <h6 class="text-primary"><?php echo htmlspecialchars($result['nama']); ?></h6>
                                            <div class="progress mb-2" style="height: 6px;">
                                                <div class="progress-bar bg-info" 
                                                     style="width: <?php echo ($alt_confidence * 100); ?>%"></div>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo number_format($alt_confidence * 100, 1); ?>% - 
                                                <?php echo $result['matched_symptoms']; ?> gejala cocok
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- Info Panel -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-lightbulb me-2"></i> Tips Diagnosa Manual
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Amati tanaman dengan teliti
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Pilih semua gejala yang terlihat
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Semakin banyak gejala yang tepat, semakin akurat hasil
                                        </li>
                                        <li class="mb-0">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Konsultasi dengan ahli jika ragu
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Perhatian
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">• Hasil diagnosa bersifat indikasi awal</li>
                                        <li class="mb-2">• Untuk kepastian, konsultasi dengan ahli pertanian</li>
                                        <li class="mb-2">• Kombinasikan dengan diagnosa gambar untuk hasil terbaik</li>
                                        <li class="mb-0">• Tindak lanjuti dengan pengendalian yang tepat</li>
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
    <script>
        function toggleGejala(id) {
            const checkbox = document.getElementById('gejala_' + id);
            const card = checkbox.closest('.gejala-card');
            
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
            
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const selected = document.querySelectorAll('input[name="gejala[]"]:checked');
            document.getElementById('selectedCount').textContent = selected.length;
        }

        function clearAll() {
            const checkboxes = document.querySelectorAll('input[name="gejala[]"]');
            const cards = document.querySelectorAll('.gejala-card');
            
            checkboxes.forEach(checkbox => checkbox.checked = false);
            cards.forEach(card => card.classList.remove('selected'));
            
            updateSelectedCount();
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateSelectedCount();
        });
    </script>
</body>
</html>