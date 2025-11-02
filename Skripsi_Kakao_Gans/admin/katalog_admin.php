<?php
require_once '../config.php';
requireAdmin();

$conn = getConnection();

// Ambil semua data penyakit
$penyakit_list = $conn->query("SELECT * FROM penyakit ORDER BY id_penyakit");
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Penyakit - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f6fa;
            color: #333;
            display: flex;
            min-height: 100vh;
        }
        
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
        
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .disease-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            transition: all 0.3s;
        }
        
        .disease-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, #c2753d 0%, #8b5a2b 100%);
            padding: 25px;
            position: relative;
        }
        
        .card-id {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: white;
            margin-bottom: 12px;
        }
        
        .card-title {
            color: white;
            font-size: 20px;
            font-weight: 600;
            line-height: 1.4;
        }
        
        .card-body {
            padding: 25px;
            background: white;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .section-title .icon {
            font-size: 18px;
            background: #e0f2fe;
            color: #0284c7;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }
        
        .description {
            color: #374151;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        
        .solution-list {
            color: #374151;
            font-size: 14px;
            line-height: 1.8;
        }
        
        .solution-list .check {
            color: #10b981;
            margin-right: 8px;
        }
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        
        .no-data-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
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
        
        <a href="kelola_user.php" class="menu-item">
            <span class="icon">👥</span>
            Kelola User
        </a>
        
        <a href="katalog_admin.php" class="menu-item active">
            <span class="icon">📋</span>
            Katalog Penyakit
        </a>
        
        <a href="logout.php" class="menu-item" style="margin-top: 30px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
            <span class="icon">🚪</span>
            Logout
        </a>
    </div>
    
    <div class="main-content">
        <div class="top-header">
            <h1>📋 Katalog Penyakit Kakao</h1>
            <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
        </div>
        
        <?php if ($penyakit_list->num_rows > 0): ?>
            <div class="catalog-grid">
                <?php while ($penyakit = $penyakit_list->fetch_assoc()): ?>
                    <div class="disease-card">
                        <div class="card-header">
                            <div class="card-id">P<?= str_pad($penyakit['id_penyakit'], 3, '0', STR_PAD_LEFT) ?></div>
                            <h3 class="card-title"><?= htmlspecialchars($penyakit['nama_penyakit']) ?></h3>
                        </div>
                        
                        <div class="card-body">
                            <div class="section-title">
                                <span class="icon">📘</span>
                                <span>Deskripsi</span>
                            </div>
                            <div class="description">
                                <?= nl2br(htmlspecialchars($penyakit['gejala'])) ?>
                            </div>
                            
                            <div class="section-title">
                                <span class="icon">💡</span>
                                <span>Solusi Pengendalian</span>
                            </div>
                            <div class="solution-list">
                                <?php
                                $solusi_text = $penyakit['solusi'];
                                if (preg_match('/\d+\./', $solusi_text)) {
                                    $solusi_items = preg_split('/\d+\.\s*/', $solusi_text);
                                    foreach ($solusi_items as $item) {
                                        $item = trim($item);
                                        if (!empty($item)) {
                                            echo '<div style="margin-bottom: 8px;"><span class="check">✓</span>' . htmlspecialchars($item) . '</div>';
                                        }
                                    }
                                } else {
                                    echo '<div><span class="check">✓</span>' . nl2br(htmlspecialchars($solusi_text)) . '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-data">
                <div class="no-data-icon">📭</div>
                <h3>Belum Ada Data Penyakit</h3>
                <p style="margin-top: 10px;">Silakan tambahkan data penyakit terlebih dahulu</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>