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
    $kode = sanitize($_POST['kode']);
    $nama = sanitize($_POST['nama']);
    $deskripsi = sanitize($_POST['deskripsi']);
    $solusi = sanitize($_POST['solusi']);
    
    try {
        if ($action == 'add') {
            // Tambah penyakit baru
            $stmt = $pdo->prepare("INSERT INTO penyakit (kode, nama, deskripsi, solusi) VALUES (?, ?, ?, ?)");
            $stmt->execute([$kode, $nama, $deskripsi, $solusi]);
            $message = "Penyakit berhasil ditambahkan!";
            
        } elseif ($action == 'edit') {
            // Edit penyakit
            $id = $_POST['id'];
            $stmt = $pdo->prepare("UPDATE penyakit SET kode = ?, nama = ?, deskripsi = ?, solusi = ? WHERE id = ?");
            $stmt->execute([$kode, $nama, $deskripsi, $solusi, $id]);
            $message = "Penyakit berhasil diupdate!";
        }
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM penyakit WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Penyakit berhasil dihapus!";
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Handle edit (get data)
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM penyakit WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_data = $stmt->fetch();
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

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
    <title>Kelola Penyakit - Admin Panel</title>
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
                    <a class="nav-link active" href="penyakit_crud.php">
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
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <h2 class="mb-4">Kelola Data Penyakit</h2>

                    <?php if ($message): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form Tambah/Edit -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-plus me-2"></i>
                                <?php echo $edit_data ? 'Edit Penyakit' : 'Tambah Penyakit Baru'; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit' : 'add'; ?>">
                                <?php if ($edit_data): ?>
                                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="kode" class="form-label">Kode Penyakit</label>
                                        <input type="text" class="form-control" id="kode" name="kode" 
                                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['kode']) : ''; ?>" 
                                               required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nama" class="form-label">Nama Penyakit</label>
                                        <input type="text" class="form-control" id="nama" name="nama" 
                                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['nama']) : ''; ?>" 
                                               required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required><?php echo $edit_data ? htmlspecialchars($edit_data['deskripsi']) : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="solusi" class="form-label">Solusi Pengendalian</label>
                                    <textarea class="form-control" id="solusi" name="solusi" rows="4" required><?php echo $edit_data ? htmlspecialchars($edit_data['solusi']) : ''; ?></textarea>
                                    <small class="form-text text-muted">Pisahkan setiap poin solusi dengan enter</small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo $edit_data ? 'Update' : 'Simpan'; ?>
                                    </button>
                                    <?php if ($edit_data): ?>
                                        <a href="penyakit_crud.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i> Batal
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabel Data Penyakit -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i> Data Penyakit
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($penyakit_list) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Kode</th>
                                                <th>Nama Penyakit</th>
                                                <th>Deskripsi</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($penyakit_list as $penyakit): ?>
                                                <tr>
                                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($penyakit['kode']); ?></span></td>
                                                    <td><?php echo htmlspecialchars($penyakit['nama']); ?></td>
                                                    <td><?php echo substr(htmlspecialchars($penyakit['deskripsi']), 0, 100) . '...'; ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="?edit=<?php echo $penyakit['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="?delete=<?php echo $penyakit['id']; ?>" 
                                                               class="btn btn-outline-danger" 
                                                               title="Hapus"
                                                               onclick="return confirm('Yakin ingin menghapus penyakit ini?')">
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
                                    <i class="fas fa-bug fa-3x mb-3"></i>
                                    <p>Belum ada data penyakit</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html><!-- penyakit_crud.php -->