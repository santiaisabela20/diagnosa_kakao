<?php
require_once '../config.php';
requireAdmin();

$conn = getConnection();
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $nama_penyakit = $_POST['nama_penyakit'] ?? '';
        $gejala = $_POST['gejala'] ?? '';
        $solusi = $_POST['solusi'] ?? '';
        
        if (!empty($nama_penyakit) && !empty($gejala) && !empty($solusi)) {
            // Upload gambar jika ada
            $gambar_name = '';
            if (!empty($_FILES['gambar']['name'])) {
                $target_dir = "../images/penyakit/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $gambar_name = time() . '_' . basename($_FILES['gambar']['name']);
                $target_file = $target_dir . $gambar_name;
                move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);
            }
            
            if ($action === 'edit') {
                $id_penyakit = intval($_POST['id_penyakit']);
                if (!empty($gambar_name)) {
                    $stmt = $conn->prepare("UPDATE penyakit SET nama_penyakit=?, gejala=?, solusi=?, gambar=? WHERE id_penyakit=?");
                    $stmt->bind_param("ssssi", $nama_penyakit, $gejala, $solusi, $gambar_name, $id_penyakit);
                } else {
                    $stmt = $conn->prepare("UPDATE penyakit SET nama_penyakit=?, gejala=?, solusi=? WHERE id_penyakit=?");
                    $stmt->bind_param("sssi", $nama_penyakit, $gejala, $solusi, $id_penyakit);
                }
                $message = 'Penyakit berhasil diupdate!';
            } else {
                $stmt = $conn->prepare("INSERT INTO penyakit (nama_penyakit, gejala, solusi, gambar) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $nama_penyakit, $gejala, $solusi, $gambar_name);
                $message = 'Penyakit berhasil ditambahkan!';
            }
            
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Get edit data
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM penyakit WHERE id_penyakit = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Ambil semua data penyakit
$penyakit_list = $conn->query("SELECT * FROM penyakit ORDER BY id_penyakit");
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penyakit - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #1a1d29;
            color: #eee;
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            padding: 20px;
            box-shadow: 4px 0 20px rgba(0,0,0,0.3);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            text-align: center;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            color: #38bdf8;
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            margin-bottom: 8px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .menu-item:hover {
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            transform: translateX(5px);
        }
        
        .menu-item.active {
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.3);
        }
        
        .menu-item .icon {
            margin-right: 12px;
            font-size: 18px;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }
        
        .top-header {
            background: #252830;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .top-header h1 {
            color: #e5e7eb;
            font-size: 28px;
            font-weight: 600;
        }
        
        .btn-back {
            padding: 10px 20px;
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            text-decoration: none;
            border-radius: 8px;
            border: 1px solid rgba(56, 189, 248, 0.3);
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: rgba(56, 189, 248, 0.2);
            border-color: #38bdf8;
        }
        
        /* Card */
        .card {
            background: #252830;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 30px;
        }
        
        .card h2 {
            color: #e5e7eb;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        /* Form */
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #94a3b8;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="text"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 12px;
            background: #1a1d29;
            border: 2px solid rgba(56, 189, 248, 0.2);
            border-radius: 8px;
            color: #e5e7eb;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #38bdf8;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
            font-family: inherit;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(56, 189, 248, 0.4);
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
        }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            padding: 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        td {
            padding: 15px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #d1d5db;
            font-size: 14px;
        }
        
        tr:hover td {
            background: rgba(56, 189, 248, 0.05);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-buttons a, .action-buttons button {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }
        
        .preview-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>🌱 Diagnosa Kakao</h2>
            <p style="color: #64748b; font-size: 12px;">Admin Panel</p>
        </div>
        
        <a href="dashboard.php" class="menu-item">
            <span class="icon">📊</span>
            Dashboard
        </a>
        
        <a href="kelola_penyakit.php" class="menu-item active">
            <span class="icon">🦠</span>
            Kelola Penyakit
        </a>
        
        <a href="kelola_user.php" class="menu-item">
            <span class="icon">👥</span>
            Kelola User
        </a>
        
        <a href="katalog_admin.php" class="menu-item">
            <span class="icon">📋</span>
            Katalog Penyakit
        </a>
        
        <a href="logout.php" class="menu-item" style="margin-top: 30px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
            <span class="icon">🚪</span>
            Logout
        </a>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h1>🦠 Kelola Penyakit</h1>
            <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2><?= $edit_data ? 'Edit Penyakit' : 'Tambah Penyakit Baru' ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'add' ?>">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id_penyakit" value="<?= $edit_data['id_penyakit'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Nama Penyakit</label>
                    <input type="text" name="nama_penyakit" value="<?= $edit_data['nama_penyakit'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Gejala (Visual)</label>
                    <textarea name="gejala" required><?= $edit_data['gejala'] ?? '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Solusi Penanganan</label>
                    <textarea name="solusi" required><?= $edit_data['solusi'] ?? '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Gambar Penyakit <?= $edit_data ? '(kosongkan jika tidak ingin mengubah)' : '' ?></label>
                    <input type="file" name="gambar" accept="image/*">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?= $edit_data ? '💾 Update Penyakit' : '➕ Tambah Penyakit' ?>
                </button>
                <?php if ($edit_data): ?>
                    <a href="kelola_penyakit.php" class="btn btn-warning" style="margin-left: 10px;">Batal Edit</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="card">
            <h2>Daftar Penyakit</h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Penyakit</th>
                        <th>Gejala</th>
                        <th>Solusi</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($penyakit = $penyakit_list->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($penyakit['nama_penyakit']) ?></td>
                            <td><?= substr(htmlspecialchars($penyakit['gejala']), 0, 100) ?>...</td>
                            <td><?= substr(htmlspecialchars($penyakit['solusi']), 0, 100) ?>...</td>
                            <td>
                                <?php if ($penyakit['gambar']): ?>
                                    <img src="../images/penyakit/<?= $penyakit['gambar'] ?>" class="preview-img">
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?= $penyakit['id_penyakit'] ?>" class="btn btn-warning">
                                        ✏️ Edit
                                    </a>
                                    <a href="hapus_penyakit.php?id=<?= $penyakit['id_penyakit'] ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Yakin ingin menghapus?')">
                                        🗑️ Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>