<?php
require_once '../config.php';
requireAdmin();

$conn = getConnection();
$message = '';

// Handle form submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $nama = $_POST['nama'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'petani';
        
        if (!empty($nama) && !empty($username)) {
            if ($action === 'edit') {
                $id_user = intval($_POST['id_user']);
                
                // Update dengan atau tanpa password
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, password=?, role=? WHERE id_user=?");
                    $stmt->bind_param("ssssi", $nama, $username, $hashed, $role, $id_user);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, role=? WHERE id_user=?");
                    $stmt->bind_param("sssi", $nama, $username, $role, $id_user);
                }
                $message = 'User berhasil diupdate!';
            } else {
                // Tambah user baru
                if (empty($password)) {
                    $message = 'Password harus diisi untuk user baru!';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $nama, $username, $hashed, $role);
                    $message = 'User berhasil ditambahkan!';
                }
            }
            
            if (isset($stmt)) {
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

// Handle delete user
if (isset($_GET['delete'])) {
    $id_user = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    if ($stmt->execute()) {
        $message = 'User berhasil dihapus!';
    }
    $stmt->close();
}

// Get edit data
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Ambil semua user
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin</title>
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
        input[type="password"],
        select {
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
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: #38bdf8;
        }
        
        select {
            cursor: pointer;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(56, 189, 248, 0.4);
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
        }
        
        .btn-danger {
            padding: 6px 12px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 12px;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-edit {
            padding: 6px 12px;
            background: #f59e0b;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 12px;
        }
        
        .btn-edit:hover {
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
        
        .badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-admin {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        .badge-petani {
            background: rgba(56, 189, 248, 0.2);
            color: #38bdf8;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .form-note {
            color: #94a3b8;
            font-size: 12px;
            margin-top: 5px;
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
        
        <a href="kelola_penyakit.php" class="menu-item">
            <span class="icon">🦠</span>
            Kelola Penyakit
        </a>
        
        <a href="kelola_user.php" class="menu-item active">
            <span class="icon">👥</span>
            Kelola User
        </a>
        
        <a href="riwayat_admin.php" class="menu-item">
            <span class="icon">📋</span>
            Lihat Katalog
        </a>
        
        <a href="logout.php" class="menu-item" style="margin-top: 30px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
            <span class="icon">🚪</span>
            Logout
        </a>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <h1>👥 Kelola User</h1>
            <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <!-- Form Add/Edit User -->
        <div class="card">
            <h2><?= $edit_data ? 'Edit User' : 'Tambah User Baru' ?></h2>
            <form method="POST">
                <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'add' ?>">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id_user" value="<?= $edit_data['id_user'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= $edit_data['nama'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= $edit_data['username'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Password <?= $edit_data ? '(Kosongkan jika tidak ingin mengubah)' : '' ?></label>
                    <input type="password" name="password" <?= $edit_data ? '' : 'required' ?>>
                    <?php if ($edit_data): ?>
                        <div class="form-note">Kosongkan jika tidak ingin mengubah password</div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label>Role</label>
                    <select name="role">
                        <option value="petani" <?= ($edit_data && $edit_data['role'] === 'petani') ? 'selected' : '' ?>>Petani</option>
                        <option value="admin" <?= ($edit_data && $edit_data['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?= $edit_data ? '💾 Update User' : '➕ Tambah User' ?>
                </button>
                <?php if ($edit_data): ?>
                    <a href="kelola_user.php" class="btn btn-warning" style="margin-left: 10px;">Batal Edit</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Daftar User -->
        <div class="card">
            <h2>Daftar User Terdaftar</h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                        <?php $no = 1; while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($user['nama']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $user['role'] ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?= $user['id_user'] ?>" class="btn-edit">
                                            ✏️ Edit
                                        </a>
                                        <a href="?delete=<?= $user['id_user'] ?>" 
                                           class="btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus user ini? Semua data diagnosa terkait akan ikut terhapus!')">
                                            🗑️ Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #6b7280; padding: 40px;">
                                Belum ada user terdaftar
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>