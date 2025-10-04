<?php
session_start();

// Koneksi database
$servername = "localhost";  
$username_db = "root";      
$password_db = "";          
$dbname = "diagnosa_kakao_db";

// Buat koneksi
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Cek role user - hanya petani yang bisa akses riwayat pribadi
if ($_SESSION['role'] !== 'petani') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Ambil riwayat diagnosa user yang sedang login
$query = "SELECT rd.*, p.nama as nama_penyakit, p.deskripsi, p.solusi 
          FROM riwayat_diagnosa rd 
          LEFT JOIN penyakit p ON rd.hasil = p.nama 
          WHERE rd.id_user = ? 
          ORDER BY rd.tanggal DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Diagnosa - Sistem Pakar Kakao</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .riwayat-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .header-riwayat {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .riwayat-item {
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .riwayat-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        
        .riwayat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .tanggal-diagnosa {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
        }
        
        .confidence-score {
            background: #17a2b8;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
        }
        
        .riwayat-content {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
            align-items: start;
        }
        
        .gambar-diagnosa {
            text-align: center;
        }
        
        .gambar-diagnosa img {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #ddd;
        }
        
        .hasil-diagnosa {
            flex: 1;
        }
        
        .nama-penyakit {
            font-size: 1.3em;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 10px;
        }
        
        .deskripsi-penyakit {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #8B4513;
        }
        
        .solusi-penyakit {
            background: #d4edda;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .back-button {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        
        .back-button:hover {
            background: #5a6268;
        }
        
        @media (max-width: 768px) {
            .riwayat-content {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .riwayat-header {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="riwayat-container">
        <a href="petani/dashboard.php" class="back-button">← Kembali ke Dashboard</a>
        
        <div class="header-riwayat">
            <h1>Riwayat Diagnosa Saya</h1>
            <p>Selamat datang, <strong><?php echo htmlspecialchars($username); ?></strong></p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="riwayat-item">
                    <div class="riwayat-header">
                        <div class="tanggal-diagnosa">
                            📅 <?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?>
                        </div>
                        <?php if (!empty($row['confidence'])): ?>
                            <div class="confidence-score">
                                🎯 Akurasi: <?php echo number_format($row['confidence'] * 100, 1); ?>%
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="riwayat-content">
                        <div class="gambar-diagnosa">
                            <?php if (!empty($row['gambar']) && file_exists('uploads/' . $row['gambar'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($row['gambar']); ?>" 
                                     alt="Gambar diagnosa">
                                <p style="margin-top: 5px; font-size: 0.9em; color: #666;">
                                    <?php echo htmlspecialchars($row['gambar']); ?>
                                </p>
                            <?php else: ?>
                                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px dashed #ddd;">
                                    <p>📷</p>
                                    <p style="font-size: 0.9em; color: #666;">Gambar tidak tersedia</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="hasil-diagnosa">
                            <div class="nama-penyakit">
                                🦠 <?php echo htmlspecialchars($row['hasil']); ?>
                            </div>
                            
                            <?php if (!empty($row['deskripsi'])): ?>
                                <div class="deskripsi-penyakit">
                                    <h4>📋 Deskripsi Penyakit:</h4>
                                    <p><?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($row['solusi'])): ?>
                                <div class="solusi-penyakit">
                                    <h4>💡 Solusi & Penanganan:</h4>
                                    <p><?php echo nl2br(htmlspecialchars($row['solusi'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-data">
                <h3>📋 Belum Ada Riwayat Diagnosa</h3>
                <p>Anda belum melakukan diagnosa penyakit kakao.</p>
                <p>Silakan mulai diagnosa dari menu dashboard petani.</p>
                <a href="petani/upload.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 15px;">
                    🚀 Mulai Diagnosa
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Fungsi untuk preview gambar yang lebih besar saat diklik
        document.querySelectorAll('.gambar-diagnosa img').forEach(img => {
            img.addEventListener('click', function() {
                // Buat modal sederhana untuk preview gambar
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.8);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 1000;
                    cursor: pointer;
                `;
                
                const imgPreview = document.createElement('img');
                imgPreview.src = this.src;
                imgPreview.style.cssText = `
                    max-width: 90%;
                    max-height: 90%;
                    border-radius: 10px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                `;
                
                modal.appendChild(imgPreview);
                document.body.appendChild(modal);
                
                // Tutup modal saat diklik
                modal.addEventListener('click', function() {
                    document.body.removeChild(modal);
                });
            });
        });
        
        // Auto refresh setiap 30 detik untuk update riwayat terbaru
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>