<!-- upload.php --><?php
require_once '../koneksi.php';

// Cek apakah user sudah login dan role petani
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'petani') {
    redirect('../index.php');
}

$message = '';
$hasil_diagnosa = null;

// Handle upload gambar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['gambar'])) {
    $upload_dir = '../uploads/';
    
    // Buat folder uploads jika belum ada
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file = $_FILES['gambar'];
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Validasi file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = 'Error saat upload file!';
    } elseif (!in_array($file['type'], $allowed_types)) {
        $message = 'Format file harus JPG, JPEG, atau PNG!';
    } elseif ($file['size'] > $max_size) {
        $message = 'Ukuran file maksimal 5MB!';
    } else {
        // Generate nama file unik
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('kakao_') . '.' . $file_extension;
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Di sini seharusnya memanggil model AI untuk prediksi
            // Untuk demo, kita simulasikan hasil prediksi
            $predicted_diseases = [
                'Busuk Buah Kakao (Black Pod Disease)',
                'Penyakit Pembuluh Kayu (Vascular-Streak Dieback)',
                'Busuk Buah Kering (Monilia Pod Rot)'
            ];
            
            // Simulasi hasil random
            $hasil = $predicted_diseases[array_rand($predicted_diseases)];
            $confidence = rand(75, 95) / 100; // 0.75 - 0.95
            
            try {
                // Simpan ke database
                $stmt = $pdo->prepare("INSERT INTO riwayat_diagnosa (id_user, gambar, hasil, confidence) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $filename, $hasil, $confidence]);
                
                $hasil_diagnosa = [
                    'gambar' => $filename,
                    'hasil' => $hasil,
                    'confidence' => $confidence
                ];
                
                $message = 'Diagnosa berhasil dilakukan!';
                
            } catch(PDOException $e) {
                $message = 'Error saat menyimpan hasil diagnosa!';
                // Hapus file jika gagal simpan ke database
                unlink($filepath);
            }
        } else {
            $message = 'Gagal upload file!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosa Gambar - Sistem Diagnosa Kakao</title>
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
        .upload-area {
            border: 3px dashed #28a745;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            background: rgba(40, 167, 69, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .upload-area:hover {
            background: rgba(40, 167, 69, 0.1);
            border-color: #20c997;
        }
        .upload-area.dragover {
            background: rgba(40, 167, 69, 0.15);
            border-color: #20c997;
        }
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
                    <a class="nav-link active" href="upload.php">
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
                    <h2 class="mb-4">
                        <i class="fas fa-camera me-2"></i> Diagnosa dengan Gambar
                    </h2>

                    <?php if ($message): ?>
                        <div class="alert <?php echo $hasil_diagnosa ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-8">
                            <!-- Upload Form -->
                            <div class="card border-0 shadow mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-upload me-2"></i> Upload Gambar Tanaman Kakao
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                                        <div class="upload-area" id="uploadArea">
                                            <i class="fas fa-cloud-upload-alt fa-4x text-success mb-3"></i>
                                            <h5>Klik atau drag & drop gambar di sini</h5>
                                            <p class="text-muted mb-3">Format: JPG, JPEG, PNG (Maksimal 5MB)</p>
                                            <input type="file" class="d-none" id="gambar" name="gambar" accept=".jpg,.jpeg,.png" required>
                                            <button type="button" class="btn btn-success" onclick="document.getElementById('gambar').click()">
                                                <i class="fas fa-folder-open me-2"></i> Pilih Gambar
                                            </button>
                                        </div>
                                        
                                        <div id="preview" class="mt-4 text-center" style="display: none;">
                                            <img id="previewImage" class="preview-image" alt="Preview">
                                            <div class="mt-3">
                                                <button type="submit" class="btn btn-primary btn-lg me-2">
                                                    <i class="fas fa-search me-2"></i> Mulai Diagnosa
                                                </button>
                                                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                                    <i class="fas fa-times me-2"></i> Batal
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Hasil Diagnosa -->
                            <?php if ($hasil_diagnosa): ?>
                            <div class="card result-card shadow">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-microscope me-2"></i> Hasil Diagnosa
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <img src="../uploads/<?php echo htmlspecialchars($hasil_diagnosa['gambar']); ?>" 
                                                 class="img-fluid rounded" alt="Gambar diagnosa">
                                        </div>
                                        <div class="col-md-6">
                                            <h4 class="mb-3">Penyakit Terdeteksi:</h4>
                                            <h5 class="mb-3"><?php echo htmlspecialchars($hasil_diagnosa['hasil']); ?></h5>
                                            
                                            <h6 class="mb-2">Tingkat Kepercayaan:</h6>
                                            <div class="confidence-bar mb-2">
                                                <div class="confidence-fill" style="width: <?php echo ($hasil_diagnosa['confidence'] * 100); ?>%"></div>
                                            </div>
                                            <p class="mb-3"><?php echo number_format($hasil_diagnosa['confidence'] * 100, 1); ?>%</p>
                                            
                                            <div class="d-grid gap-2">
                                                <a href="../katalog.php" class="btn btn-light">
                                                    <i class="fas fa-book me-2"></i> Lihat Detail Penyakit
                                                </a>
                                                <a href="../riwayat.php" class="btn btn-outline-light">
                                                    <i class="fas fa-history me-2"></i> Lihat Riwayat
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <!-- Tips -->
                            <div class="card border-0 shadow mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-lightbulb me-2"></i> Tips Mengambil Foto
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Ambil foto dengan pencahayaan yang cukup
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Fokus pada bagian yang terinfeksi
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Pastikan gambar tidak buram
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Ambil dari jarak yang tidak terlalu jauh
                                        </li>
                                        <li class="mb-0">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Hindari bayangan yang berlebihan
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Contoh Gambar -->
                            <div class="card border-0 shadow">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-images me-2"></i> Contoh Gambar
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-3">Berikut contoh gambar yang baik untuk diagnosa:</p>
                                    
                                    <div class="mb-3">
                                        <h6 class="text-success">✓ Gambar Baik:</h6>
                                        <ul class="list-unstyled small">
                                            <li>• Fokus jelas pada gejala</li>
                                            <li>• Pencahayaan merata</li>
                                            <li>• Tidak ada blur</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="mb-0">
                                        <h6 class="text-danger">✗ Hindari:</h6>
                                        <ul class="list-unstyled small">
                                            <li>• Gambar terlalu gelap</li>
                                            <li>• Terlalu jauh dari objek</li>
                                            <li>• Gambar buram atau tidak fokus</li>
                                        </ul>
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
    <script>
        const fileInput = document.getElementById('gambar');
        const uploadArea = document.getElementById('uploadArea');
        const preview = document.getElementById('preview');
        const previewImage = document.getElementById('previewImage');

        // Handle file input change
        fileInput.addEventListener('change', function(e) {
            handleFile(e.target.files[0]);
        });

        // Handle drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFile(files[0]);
            }
        });

        function handleFile(file) {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    preview.style.display = 'block';
                    uploadArea.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        }

        function resetForm() {
            fileInput.value = '';
            preview.style.display = 'none';
            uploadArea.style.display = 'block';
        }
    </script>
</body>
</html>