<!-- relasi_crud.php -->
<?php
require_once '../koneksi.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('../index.php');
}

$message = '';
$edit_data = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        if ($action == 'add') {
            $id_penyakit = $_POST['id_penyakit'];
            $id_gejala = $_POST['id_gejala'];
            $bobot = floatval($_POST['bobot']);
            
            // Cek apakah relasi sudah ada
            $stmt = $pdo->prepare("SELECT id FROM penyakit_gejala WHERE id_penyakit = ? AND id_gejala = ?");
            $stmt->execute([$id_penyakit, $id_gejala]);
            
            if ($stmt->fetch()) {
                $message = "Relasi sudah ada!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO penyakit_gejala (id_penyakit, id_gejala, bobot) VALUES (?, ?, ?)");
                $stmt->execute([$id_penyakit, $id_gejala, $bobot]);
                $message = "Relasi berhasil ditambahkan!";
            }
        }
        
        // Handle UPDATE
        elseif ($action == 'edit') {
            $id = $_POST['id'];
            $id_penyakit = $_POST['id_penyakit'];
            $id_gejala = $_POST['id_gejala'];
            $bobot = floatval($_POST['bobot']);
            
            // Cek apakah kombinasi penyakit-gejala sudah ada untuk ID lain
            $stmt = $pdo->prepare("SELECT id FROM penyakit_gejala WHERE id_penyakit = ? AND id_gejala = ? AND id != ?");
            $stmt->execute([$id_penyakit, $id_gejala, $id]);
            
            if ($stmt->fetch()) {
                $message = "Relasi penyakit-gejala tersebut sudah ada!";
            } else {
                $stmt = $pdo->prepare("UPDATE penyakit_gejala SET id_penyakit = ?, id_gejala = ?, bobot = ? WHERE id = ?");
                $stmt->execute([$id_penyakit, $id_gejala, $bobot, $id]);
                $message = "Relasi berhasil diperbarui!";
            }
        }
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM penyakit_gejala WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Relasi berhasil dihapus!";
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Handle edit request
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM penyakit_gejala WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_data = $stmt->fetch();
        
        if (!$edit_data) {
            $message = "Data relasi tidak ditemukan!";
        }
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Ambil data untuk dropdown
try {
    $stmt = $pdo->query("SELECT * FROM penyakit ORDER BY kode");
    $penyakit_list = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM gejala ORDER BY kode");
    $gejala_list = $stmt->fetchAll();
    
    // Ambil data relasi dengan join
    $stmt = $pdo->query("
        SELECT pg.*, p.kode as kode_penyakit, p.nama as nama_penyakit, 
               g.kode as kode_gejala, g.nama as nama_gejala
        FROM penyakit_gejala pg
        JOIN penyakit p ON pg.id_penyakit = p.id
        JOIN gejala g ON pg.id_gejala = g.id
        ORDER BY p.kode, g.kode
    ");
    $relasi_list = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $penyakit_list = [];
    $gejala_list = [];
    $relasi_list = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Relasi - Admin Panel</title>
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
        .bobot-bar {
            height: 8px;
            border-radius: 4px;
            background: #e9ecef;
            overflow: hidden;
        }
        .bobot-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }
        .edit-form {
            background: linear-gradient(135deg, #17a2b8, #138496);
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
                    <a class="nav-link active" href="relasi_crud.php">
                        <i class="fas fa-project-diagram me-2"></i> Kelola Relasi
                    </a>
                    <a class="nav-link" href="users.php">
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
                        <h2>Kelola Relasi Penyakit & Gejala</h2>
                        <?php if ($edit_data): ?>
                            <a href="relasi_crud.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form Tambah/Edit Relasi -->
                    <div class="card mb-4">
                        <div class="card-header <?php echo $edit_data ? 'edit-form' : ''; ?>">
                            <h5 class="mb-0">
                                <?php if ($edit_data): ?>
                                    <i class="fas fa-edit me-2"></i> Edit Relasi
                                <?php else: ?>
                                    <i class="fas fa-link me-2"></i> Tambah Relasi Baru
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit' : 'add'; ?>">
                                <?php if ($edit_data): ?>
                                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="id_penyakit" class="form-label">Penyakit</label>
                                        <select class="form-select" id="id_penyakit" name="id_penyakit" required>
                                            <option value="">Pilih Penyakit</option>
                                            <?php foreach ($penyakit_list as $penyakit): ?>
                                                <option value="<?php echo $penyakit['id']; ?>" 
                                                    <?php echo ($edit_data && $edit_data['id_penyakit'] == $penyakit['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($penyakit['kode'] . ' - ' . $penyakit['nama']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="id_gejala" class="form-label">Gejala</label>
                                        <select class="form-select" id="id_gejala" name="id_gejala" required>
                                            <option value="">Pilih Gejala</option>
                                            <?php foreach ($gejala_list as $gejala): ?>
                                                <option value="<?php echo $gejala['id']; ?>"
                                                    <?php echo ($edit_data && $edit_data['id_gejala'] == $gejala['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($gejala['kode'] . ' - ' . $gejala['nama']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="bobot" class="form-label">Bobot (0.1 - 1.0)</label>
                                        <input type="number" class="form-control" id="bobot" name="bobot" 
                                               min="0.1" max="1.0" step="0.1" 
                                               value="<?php echo $edit_data ? $edit_data['bobot'] : '0.5'; ?>" required>
                                        <small class="form-text text-muted">Semakin tinggi bobot, semakin kuat hubungannya</small>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <?php if ($edit_data): ?>
                                            <i class="fas fa-save me-2"></i> Update Relasi
                                        <?php else: ?>
                                            <i class="fas fa-plus me-2"></i> Tambah Relasi
                                        <?php endif; ?>
                                    </button>
                                    
                                    <?php if ($edit_data): ?>
                                        <a href="relasi_crud.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i> Batal
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabel Data Relasi -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2"></i> Data Relasi Penyakit & Gejala
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($relasi_list) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Penyakit</th>
                                                <th>Gejala</th>
                                                <th>Bobot</th>
                                                <th>Visual Bobot</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($relasi_list as $relasi): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary me-2"><?php echo htmlspecialchars($relasi['kode_penyakit']); ?></span>
                                                        <?php echo htmlspecialchars($relasi['nama_penyakit']); ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success me-2"><?php echo htmlspecialchars($relasi['kode_gejala']); ?></span>
                                                        <?php echo htmlspecialchars($relasi['nama_gejala']); ?>
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold"><?php echo number_format($relasi['bobot'], 1); ?></span>
                                                    </td>
                                                    <td style="width: 150px;">
                                                        <div class="bobot-bar">
                                                            <div class="bobot-fill" style="width: <?php echo ($relasi['bobot'] * 100); ?>%"></div>
                                                        </div>
                                                        <small class="text-muted"><?php echo number_format($relasi['bobot'] * 100, 0); ?>%</small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="?edit=<?php echo $relasi['id']; ?>" 
                                                               class="btn btn-outline-warning btn-sm" 
                                                               title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="?delete=<?php echo $relasi['id']; ?>" 
                                                               class="btn btn-outline-danger btn-sm" 
                                                               title="Hapus"
                                                               onclick="return confirm('Yakin ingin menghapus relasi ini?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-project-diagram fa-3x mb-3"></i>
                                    <p>Belum ada relasi penyakit & gejala</p>
                                    <small>Tambahkan relasi untuk memungkinkan diagnosa berbasis gejala</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Info Panel -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Tentang Bobot</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>0.9 - 1.0:</strong> Gejala sangat khas untuk penyakit</li>
                                        <li><strong>0.7 - 0.8:</strong> Gejala cukup khas</li>
                                        <li><strong>0.5 - 0.6:</strong> Gejala umum/biasa</li>
                                        <li><strong>0.1 - 0.4:</strong> Gejala jarang/tidak khas</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Tips</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li>• Pastikan data penyakit dan gejala sudah ada</li>
                                        <li>• Gunakan bobot tinggi untuk gejala yang sangat spesifik</li>
                                        <li>• Relasi ini digunakan untuk diagnosa manual</li>
                                        <li>• Satu gejala bisa terkait dengan beberapa penyakit</li>
                                        <li>• <strong>Klik tombol Edit untuk mengubah relasi</strong></li>
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
</html>