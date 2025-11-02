<?php
require_once '../config.php';
requireAdmin();

if (isset($_GET['id'])) {
    $id_penyakit = intval($_GET['id']);
    
    $conn = getConnection();
    
    // Hapus gambar jika ada
    $result = $conn->query("SELECT gambar FROM penyakit WHERE id_penyakit = $id_penyakit");
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        if (!empty($data['gambar'])) {
            $file_path = "../images/penyakit/" . $data['gambar'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    
    // Hapus data penyakit
    $stmt = $conn->prepare("DELETE FROM penyakit WHERE id_penyakit = ?");
    $stmt->bind_param("i", $id_penyakit);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header('Location: kelola_penyakit.php');
exit();
?>