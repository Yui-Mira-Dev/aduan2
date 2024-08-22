<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Database.php';

function isTokenValid($token)
{
    global $pdo;

    $sql = "SELECT id_user, expires_at FROM tokens WHERE token = ? AND expires_at > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return $result['id_user'];
    } else {
        return false;
    }
}

$token = isset($_GET['key']) ? $_GET['key'] : '';

if (!isset($_SESSION['id_user'], $_SESSION['username'], $_SESSION['role']) || !isTokenValid($token)) {
    header('Location: index');
    exit();
}

date_default_timezone_set('Asia/Jakarta');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About</title>
    <link rel="icon" href="logopusri">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="headercss">
    <style>
        @media (max-width: 1000px) {
            .scrollable-table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>

<body class="bg-gray-100">

    <?php
    include __DIR__ . '/../components/header.php';
    ?>

    <main class="content-wrapper transition-all duration-300 ease-in-out ml-0 mt-10 lg:ml-64">
        <div>
            <button id="toggleSidebar" class="lg:hidden fixed top-4 mt-2 left-4 bg-blue-500 text-white px-4 py-2 rounded focus:outline-none">
                <i class="bi bi-chevron-double-right text-lg"></i>
            </button>
            <div id="loading" class="fixed inset-0 flex items-center justify-center bg-white">
                <div class="loading-spinner"></div>
            </div>
            <div class="p-8 mt-20 text-center" id="content">
                <h1 class="text-4xl font-bold text-center mb-8">Aduan TI Pusri</h1>
                <div class="flex justify-center mb-8">
                    <div class="text-center mx-4">
                        <img src="deeimg" alt="Muhammad Ade Ilham Wahyudi" class="rounded-full w-40 h-40 mx-auto mb-4 object-cover">
                        <p class="text-xl font-semibold">Muhammad Ade Ilham Wahyudi</p>
                    </div>
                    <div class="text-center mx-4">
                        <img src="sabimg1" alt="Sabrina Citra" class="rounded-full w-40 h-40 mx-auto mb-4 object-cover">
                        <p class="text-xl font-semibold">Sabrina Citra</p>
                    </div>
                </div>
                <p class="text-lg leading-7">
                    Selamat datang di "Aduan TI Pusri", sebuah platform yang kami kembangkan untuk mempermudah pencatatan dan pengelolaan aduan terkait Teknologi Informasi di PT Pusri. Platform ini dirancang agar hanya admin yang dapat mengisi data aduan, sehingga setiap aduan yang masuk dapat dikelola dengan baik dan terstruktur.
                </p>
                <p class="text-lg leading-7 mt-4">
                    Admin aplikasi akan mencatat setiap aduan yang diterima dari berbagai sumber, dan data tersebut dapat dicetak dalam bentuk file Excel untuk keperluan dokumentasi dan analisis lebih lanjut. Dengan demikian, efisiensi dan efektivitas dalam penanganan masalah TI di lingkungan PT Pusri dapat meningkat.
                </p>
                <p class="text-lg leading-7 mt-4">
                    Terima kasih telah mempercayakan kami dalam pengelolaan aduan TI Anda. Kami berkomitmen untuk memberikan pelayanan terbaik demi kemajuan bersama.
                </p>
            </div>
        </div>
    </main>

    <script src="headerjs"></script>
    <?php
    require_once __DIR__ . '/../../assets/js/manualjs.html';
    ?>
</body>

</html>