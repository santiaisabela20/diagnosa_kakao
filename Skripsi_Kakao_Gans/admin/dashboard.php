<?php
require_once '../config.php';
requireAdmin();

$conn = getConnection();

// Hitung statistik
$total_users = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'petani'")->fetch_assoc()['total'];
$total_diagnosa = $conn->query("SELECT COUNT(*) as total FROM diagnosa_gambar")->fetch_assoc()['total'];
$total_penyakit = $conn->query("SELECT COUNT(*) as total FROM penyakit")->fetch_assoc()['total'];

// Data diagnosa terbaru
$recent_diagnosa = $conn->query("SELECT dg.*, u.nama as nama_petani, u.username
                                 FROM diagnosa_gambar dg 
                                 JOIN users u ON dg.id_user = u.id_user 
                                 ORDER BY dg.tanggal DESC 
                                 LIMIT 5");

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Diagnosa Kakao</title>
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
        
        .sidebar-header p {
            color: #64748b;
            font-size: 12px;
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
        
        .logout-btn {
            margin-top: 30px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-color: #ef4444;
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
        
        .header-left h1 {
            color: #e5e7eb;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .header-date {
            color: #9ca3af;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .header-date .icon {
            font-size: 16px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #9ca3af;
            font-size: 14px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.05);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(56, 189, 248, 0.2);
        }
        
        .stat-card.card-pink::before {
            background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
        }
        
        .stat-card.card-cyan::before {
            background: linear-gradient(90deg, #0ea5e9 0%, #0284c7 100%);
        }
        
        .stat-card.card-purple::before {
            background: linear-gradient(90deg, #7dd3fc 0%, #38bdf8 100%);
        }
        
        .stat-card.card-orange::before {
            background: linear-gradient(90deg, #0284c7 0%, #0369a1 100%);
        }
        
        .stat-title {
            font-size: 13px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #e5e7eb;
            margin-bottom: 5px;
        }
        
        .stat-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 50px;
            opacity: 0.1;
        }
        
        /* Recent Diagnosa Section */
        .section {
            background: #252830;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.05);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .section-header .icon {
            font-size: 24px;
            margin-right: 12px;
        }
        
        .section-header h2 {
            color: #e5e7eb;
            font-size: 20px;
            font-weight: 600;
        }
        
        /* Table */
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
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
        
        .btn-view {
            padding: 6px 12px;
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-view:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.4);
        }
        
        .confidence {
            display: inline-block;
            padding: 4px 10px;
            background: rgba(56, 189, 248, 0.2);
            color: #38bdf8;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
        
        .no-data-icon {
            font-size: 60px;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
        }
        
        ::-webkit-scrollbar-thumb {
            background: #38bdf8;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #0ea5e9;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>🌱 Diagnosa Kakao</h2>
            <p>Admin Panel</p>
        </div>
        
        <a href="dashboard.php" class="menu-item active">
            <span class="icon">📊</span>
            Dashboard
        </a>
        
        <a href="kelola_penyakit.php" class="menu-item">
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
        
        <a href="logout.php" class="menu-item logout-btn">
            <span class="icon">🚪</span>
            Logout
        </a>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <div class="top-header">
            <div class="header-left">
                <h1>Dashboard</h1>
                <div class="header-date">
                    <span class="icon">📅</span>
                    <span><?php
                        // Set locale ke Indonesia
                        $hari = array(
                            'Sunday' => 'Minggu',
                            'Monday' => 'Senin', 
                            'Tuesday' => 'Selasa',
                            'Wednesday' => 'Rabu',
                            'Thursday' => 'Kamis',
                            'Friday' => 'Jumat',
                            'Saturday' => 'Sabtu'
                        );
                        $bulan = array(
                            'January' => 'Januari',
                            'February' => 'Februari',
                            'March' => 'Maret',
                            'April' => 'April',
                            'May' => 'Mei',
                            'June' => 'Juni',
                            'July' => 'Juli',
                            'August' => 'Agustus',
                            'September' => 'September',
                            'October' => 'Oktober',
                            'November' => 'November',
                            'December' => 'Desember'
                        );
                        
                        $hari_ini = $hari[date('l')];
                        $tanggal = date('d');
                        $bulan_ini = $bulan[date('F')];
                        $tahun = date('Y');
                        
                        echo "$hari_ini, $tanggal $bulan_ini $tahun";
                    ?></span>
                </div>
            </div>
            <div class="user-info">
                <span>Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card card-pink">
                <div class="stat-title">Total Petani</div>
                <div class="stat-value"><?= $total_users ?></div>
                <div class="stat-icon">👨‍🌾</div>
            </div>
            
            <div class="stat-card card-cyan">
                <div class="stat-title">Data Penyakit</div>
                <div class="stat-value"><?= $total_penyakit ?></div>
                <div class="stat-icon">🦠</div>
            </div>
            
            <div class="stat-card card-orange">
                <div class="stat-title">Total Diagnosa</div>
                <div class="stat-value"><?= $total_diagnosa ?></div>
                <div class="stat-icon">📊</div>
            </div>
        </div>
        
        <!-- Recent Diagnosa -->
        <div class="section">
            <div class="section-header">
                <span class="icon">🕐</span>
                <h2>Diagnosa Terbaru</h2>
            </div>
            
            <?php if ($recent_diagnosa->num_rows > 0): ?>
                <div class="table-container">
                    <table>
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
                            <?php while ($row = $recent_diagnosa->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['nama_petani']) ?></strong><br>
                                        <small style="color: #666;">@<?= htmlspecialchars($row['username']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($row['nama_file']): ?>
                                            <button class="btn-view" onclick="window.open('../uploads/<?= htmlspecialchars($row['nama_file']) ?>')">
                                                Ada Gambar
                                            </button>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['hasil_prediksi']) ?></td>
                                    <td>
                                        <span class="confidence"><?= number_format($row['akurasi'], 1) ?>%</span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">📭</div>
                    <p>Belum ada diagnosa</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>