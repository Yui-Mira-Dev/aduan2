<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Pastikan path benar
use Dotenv\Dotenv;

// Load konfigurasi dari .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../..'); // Sesuaikan path .env
$dotenv->load();

// Include db.php untuk koneksi PDO
require_once __DIR__ . '/../models/Database.php';

// Fungsi untuk registrasi pengguna dengan enkripsi password
function registerUser($pdo, $username, $email, $password)
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Enkripsi password
    $role = 'deskjob'; // Default role

    $sql = "INSERT INTO Users (username, email, password, role) VALUES (:username, :email, :password, :role)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword); // Menggunakan password yang sudah di-hash
    $stmt->bindParam(':role', $role);

    if ($stmt->execute()) {
        echo "Registrasi berhasil!";
    } else {
        echo "Registrasi gagal!";
    }
}

// Setup PDO
try {
    $pdo = getPDOInstance(); // Mendapatkan koneksi PDO dari fungsi getPDOInstance() di db.php
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $email && $password) {
        registerUser($pdo, $username, $email, $password); // Memanggil fungsi registerUser() dengan $pdo yang sudah didefinisikan
    } else {
        echo "Harap isi semua field!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Registrasi Pengguna</title>
</head>

<body>
    <h2>Form Registrasi</h2>
    <form method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Daftar</button>
    </form>
</body>

</html>