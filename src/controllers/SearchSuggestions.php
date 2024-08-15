<?php
require_once __DIR__ . '/../models/Database.php';

$term = isset($_GET['term']) ? $_GET['term'] : '';

// Cek jika parameter term tidak ada dan referer tidak ada
if (empty($term) && !isset($_SERVER['HTTP_REFERER'])) {
    // Tambahkan JavaScript untuk redirect
    echo '<script>
        window.onload = function() {
            window.history.back();
        };
    </script>';
} else {
    if ($term) {
        // Query untuk mencari dalam kolom nama_pengadu, title_aduan, dan tempat_aduan
        $sql = "SELECT DISTINCT artifact_id, nama_pengadu, title_aduan, tempat_aduan 
                FROM daftar_aduan 
                WHERE (artifact_id LIKE ? 
                OR nama_pengadu LIKE ? 
                OR title_aduan LIKE ? 
                OR tempat_aduan LIKE ?) 
                AND Status = 1 
                LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["%$term%", "%$term%", "%$term%", "%$term%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            echo '<div class="suggestion-item px-4 py-2 cursor-pointer hover:bg-gray-200">'
                . htmlspecialchars($result['artifact_id']) . ' - '
                . htmlspecialchars($result['nama_pengadu']) . ' - '
                . htmlspecialchars($result['title_aduan']) . ' - '
                . htmlspecialchars($result['tempat_aduan']) .
                '</div>';
        }
    }
}
