<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/Database.php';

// Function to calculate the time difference up to minutes
function calculateAge($startDateTime, $endDateTime)
{
    $start = new DateTime($startDateTime);
    $end = new DateTime($endDateTime);
    $interval = $start->diff($end);

    $parts = [];
    if ($interval->y > 0) $parts[] = $interval->y . ' years';
    if ($interval->m > 0) $parts[] = $interval->m . ' months';
    if ($interval->d > 0) $parts[] = $interval->d . ' days';
    if ($interval->h > 0) $parts[] = $interval->h . ' hours';
    if ($interval->i > 0) $parts[] = $interval->i . ' minutes';

    return implode(' ', $parts);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idAduan = $_POST['id_aduan'] ?? null;
    $status = $_POST['status'] ?? null;
    $hasilKonfirmasiTeknisi = $_POST['hasil_konfirmasi_teknisi'] ?? '';
    $teknisiPenindaklanjut = $_POST['teknisi_penindaklanjut'] ?? '';

    // Validate input
    if (empty($idAduan) || empty($status)) {
        echo "Error: Missing data.";
        exit;
    }

    try {
        // Get PDO instance from Database model
        $pdo = getPDOInstance();

        // Fetch the `tanggal_aduan` for calculating `Umur_aduan`
        $stmt = $pdo->prepare("SELECT tanggal_aduan FROM daftar_aduan WHERE id_aduan = ?");
        $stmt->execute([$idAduan]);
        $aduan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$aduan) {
            echo "Error: Complaint not found.";
            exit;
        }

        $tanggalAduan = $aduan['tanggal_aduan'];
        $currentDateTime = date('Y-m-d H:i:s');

        // Prepare update query
        $sql = "UPDATE daftar_aduan SET 
                    Status = ?, 
                    Hasil_konfrimasi_teknisi = ?, 
                    Teknisi_penindaklanjut_aduan = ?";

        // If status is 1 (complete), update Umur_aduan
        if ($status == 1) {
            $age = calculateAge($tanggalAduan, $currentDateTime);
            $sql .= ", Umur_aduan = ?";
        }

        $sql .= " WHERE id_aduan = ?";

        $stmt = $pdo->prepare($sql);

        // Bind parameters
        if ($status == 1) {
            $stmt->execute([$status, $hasilKonfirmasiTeknisi, $teknisiPenindaklanjut, $age, $idAduan]);
        } else {
            $stmt->execute([$status, $hasilKonfirmasiTeknisi, $teknisiPenindaklanjut, $idAduan]);
        }

        // Redirect to the dashboard with the token
        header('Location: dashboard?key=' . urlencode($_SESSION['token']));
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
