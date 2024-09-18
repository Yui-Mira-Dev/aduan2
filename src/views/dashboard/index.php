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

function generateAge($date)
{
    $now = new DateTime();
    $date = new DateTime($date);
    $interval = $now->diff($date);
    return $interval->format('%y years %m months %d days %h hours %i minutes');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['create'])) {
            $artifact_id = $_POST['artifact_id'];
            $nama_pengadu = $_POST['nama_pengadu'];
            $title_aduan = $_POST['title_aduan'];
            $ext = $_POST['ext'];
            $tempat_aduan = $_POST['tempat_aduan'];
            $PIC = $_POST['PIC'];
            $Koordinator = $_POST['Koordinator'];
            $Status = $_POST['Status'];
            $Keterangan = $_POST['Keterangan'];
            $tanggal_aduan = date('Y-m-d H:i:s');
            $Umur_aduan = generateAge($tanggal_aduan);

            $sql = "INSERT INTO daftar_aduan (artifact_id, tanggal_aduan, nama_pengadu, title_aduan, ext, tempat_aduan, PIC, Koordinator, Status, Keterangan, Umur_aduan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$artifact_id, $tanggal_aduan, $nama_pengadu, $title_aduan, $ext, $tempat_aduan, $PIC, $Koordinator, $Status, $Keterangan, $Umur_aduan]);
        } elseif (isset($_POST['update'])) {
            $id_aduan = $_POST['id_aduan'];
            $artifact_id = $_POST['artifact_id'];
            $nama_pengadu = $_POST['nama_pengadu'];
            $title_aduan = $_POST['title_aduan'];
            $ext = $_POST['ext'];
            $tempat_aduan = $_POST['tempat_aduan'];
            $PIC = $_POST['PIC'];
            $Koordinator = $_POST['Koordinator'];
            $Status = $_POST['Status'];
            $Keterangan = $_POST['Keterangan'];
            $Umur_aduan = generateAge($_POST['tanggal_aduan']);

            $sql = "UPDATE daftar_aduan SET artifact_id = ?, nama_pengadu = ?, title_aduan = ?, ext = ?, tempat_aduan = ?, PIC = ?, Koordinator = ?, Status = ?, Keterangan = ?, Umur_aduan = ? WHERE id_aduan = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$artifact_id, $nama_pengadu, $title_aduan, $ext, $tempat_aduan, $PIC, $Koordinator, $Status, $Keterangan, $Umur_aduan, $id_aduan]);
        } elseif (isset($_POST['delete'])) {
            $id_aduan = $_POST['id_aduan'];
            $sql = "DELETE FROM daftar_aduan WHERE id_aduan = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_aduan]);
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // Duplicate entry error
            $error = 'Error: Artifact Id already exists!';
        } else {
            // Other database error
            echo 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch PICs, Koordinators, and Statuses
$pics = $pdo->query("SELECT * FROM PIC")->fetchAll(PDO::FETCH_ASSOC);
$koordinators = $pdo->query("SELECT * FROM Koordinator")->fetchAll(PDO::FETCH_ASSOC);
$statuses = $pdo->query("SELECT id_status, description FROM status")->fetchAll(PDO::FETCH_ASSOC);

$complaints_per_page = 10; // Number of complaints to display per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $complaints_per_page;

$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'pending';

// Determine the sorting order based on the sort parameter
if ($sortOrder === 'pending') {
    $orderBy = 'Status DESC';
} else {
    $orderBy = 'Status ASC';
}

// Get the total number of complaints excluding Status = 1
$total_complaints = $pdo->query("SELECT COUNT(*) FROM daftar_aduan WHERE Status != 1")->fetchColumn();
$total_pages = ceil($total_complaints / $complaints_per_page);

// Fetch complaints for the current page excluding Status = 1 and sorting based on user choice
$sql = "SELECT * FROM daftar_aduan WHERE Status != 1 ORDER BY $orderBy LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $complaints_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$aduans = $stmt->fetchAll(PDO::FETCH_ASSOC);

function calculateAge($date)
{
    $now = new DateTime();
    $date = new DateTime($date);
    $interval = $now->diff($date);

    $ageString = '';
    if ($interval->y > 0) {
        $ageString .= $interval->y . ' years ';
    }
    if ($interval->m > 0) {
        $ageString .= $interval->m . ' months ';
    }
    if ($interval->d > 0) {
        $ageString .= $interval->d . ' days ';
    }
    if ($interval->h > 0) {
        $ageString .= $interval->h . ' hours ';
    }
    if ($interval->i > 0) {
        $ageString .= $interval->i . ' minutes ';
    }

    return $ageString ?: 'Just now';
}

// Fetch the latest artifact_id from the database
$sql = "SELECT artifact_id FROM daftar_aduan ORDER BY artifact_id DESC LIMIT 1";
$stmt = $pdo->query($sql);
$latest_aduan = $stmt->fetchColumn();

// Generate the next artifact_id
$next_artifact_id = $latest_aduan ? $latest_aduan + 1 : 1000;

$selectedMonth = isset($_GET['month']) ? $_GET['month'] : '';
$selectedYear = isset($_GET['year']) ? $_GET['year'] : '';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'pending';

// Filter data berdasarkan bulan dan tahun yang dipilih
$filteredAduans = array_filter($aduans, function ($aduan) use ($selectedMonth, $selectedYear) {
    $aduanMonth = date('m', strtotime($aduan['tanggal_aduan']));
    $aduanYear = date('Y', strtotime($aduan['tanggal_aduan']));

    $isMonthMatch = !$selectedMonth || $aduanMonth == $selectedMonth;
    $isYearMatch = !$selectedYear || $aduanYear == $selectedYear;

    return $isMonthMatch && $isYearMatch;
});

// Pisahkan keluhan berdasarkan status
$inProgressAduans = array_filter($filteredAduans, fn($aduan) => $aduan['Status'] == 2);
$pendingAduans = array_filter($filteredAduans, fn($aduan) => $aduan['Status'] == 3);

// Gabungkan array berdasarkan pilihan sorting
if ($sortOrder === 'pending') {
    $sortedAduans = array_merge($pendingAduans, $inProgressAduans);
} else {
    $sortedAduans = array_merge($inProgressAduans, $pendingAduans);
}

// URL with token
$urlWithToken = 'dashboard?key=' . urlencode($_SESSION['token']);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Filter data berdasarkan status yang dipilih
$selectedStatus = '';
if ($sortOrder === 'pending') {
    $selectedStatus = 3; // Status 'pending'
} elseif ($sortOrder === 'in-progress') {
    $selectedStatus = 2; // Status 'in-progress'
}

$whereClauses = ["Status = :status", "Status != 1"];
$params = [':status' => $selectedStatus];

if (isset($_GET['month']) && $_GET['month'] !== '') {
    $whereClauses[] = "MONTH(tanggal_aduan) = :month";
    $params[':month'] = $_GET['month'];
}

if (isset($_GET['year']) && $_GET['year'] !== '') {
    $whereClauses[] = "YEAR(tanggal_aduan) = :year";
    $params[':year'] = $_GET['year'];
}

$whereSql = implode(' AND ', $whereClauses);

// Hitung total data yang sesuai dengan filter
$sql = "SELECT COUNT(*) FROM daftar_aduan WHERE $whereSql";
$stmt = $pdo->prepare($sql);
foreach ($params as $param => $value) {
    $stmt->bindValue($param, $value);
}
$stmt->execute();
$total_complaints = $stmt->fetchColumn();

$total_pages = ceil($total_complaints / $complaints_per_page);

// Fetch data sesuai dengan filter dan sorting
$sql = "SELECT * FROM daftar_aduan WHERE $whereSql ORDER BY $orderBy LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

foreach ($params as $param => $value) {
    $stmt->bindValue($param, $value);
}
$stmt->bindValue(':limit', $complaints_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$aduans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Complaints</title>
    <link rel="icon" href="logopusri">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="headercss">
</head>

<body class="bg-gray-100">
    <?php include __DIR__ . '/../components/header.php'; ?>
    <main class="content-wrapper transition-all duration-300 ease-in-out ml-0 mt-10 lg:ml-64">
        <div>
            <button id="toggleSidebar" class="lg:hidden fixed top-4 mt-2 left-4 bg-blue-500 text-white px-4 py-2 rounded focus:outline-none">
                <i class="bi bi-chevron-double-right text-lg"></i>
            </button>
            <div id="loading" class="fixed inset-0 flex items-center justify-center bg-white">
                <div class="loading-spinner"></div>
            </div>
            <div div class="p-8 mt-10" id="content">
                <?php if ($_SESSION['role'] !== 'teknisi') : ?>
                    <?php
                    include '../src/views/components/DeskjobDashboardComponent.php'
                    ?>
                <?php elseif ($_SESSION['role'] !== 'deskjob') : ?>
                    <?php
                    include '../src/views/components/TeknisiDashboardComponent.php'
                    ?>
                <?php endif; ?>
                <!-- Pagination -->
                <div class="mt-6 flex justify-center space-x-2 mb-10">
                    <?php if ($current_page > 1) : ?>
                        <a href="?page=<?php echo $current_page - 1; ?>&sort=<?php echo htmlspecialchars($sortOrder); ?>&month=<?php echo htmlspecialchars($selectedMonth); ?>&year=<?php echo htmlspecialchars($selectedYear); ?>&key=<?php echo htmlspecialchars($_SESSION['token']); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-md shadow-sm">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <a href="?page=<?php echo $i; ?>&sort=<?php echo htmlspecialchars($sortOrder); ?>&month=<?php echo htmlspecialchars($selectedMonth); ?>&year=<?php echo htmlspecialchars($selectedYear); ?>&key=<?php echo htmlspecialchars($_SESSION['token']); ?>" class="bg-<?php echo $i == $current_page ? 'gray' : 'blue'; ?>-500 text-white px-4 py-2 rounded-md shadow-sm"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages) : ?>
                        <a href="?page=<?php echo $current_page + 1; ?>&sort=<?php echo htmlspecialchars($sortOrder); ?>&month=<?php echo htmlspecialchars($selectedMonth); ?>&year=<?php echo htmlspecialchars($selectedYear); ?>&key=<?php echo htmlspecialchars($_SESSION['token']); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-md shadow-sm">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="headerjs"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('modal');
            const toggleModalBtn = document.getElementById('toggleModal');
            const closeModalBtn = document.getElementById('closeModal');

            const openModal = () => {
                modal.classList.remove('hidden');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
            };

            if (toggleModalBtn) {
                toggleModalBtn.addEventListener('click', openModal);
            }

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeModal);
            }

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });
        });

        function confirmDelete() {
            return confirm('Are you sure you want to delete this record? This action cannot be undone.');
        }

        function openEditModal(aduan) {
            document.getElementById('id_aduan').value = aduan.id_aduan;
            document.getElementById('status').value = aduan.Status;
            document.getElementById('hasil_konfirmasi_teknisi').value = aduan.Hasil_konfrimasi_teknisi || '';
            document.getElementById('teknisi_penindaklanjut').value = aduan.Teknisi_penindaklanjut_aduan || '';

            document.getElementById('editModal').classList.remove('hidden');
        }

        document.getElementById('closeEditModal').addEventListener('click', () => {
            document.getElementById('editModal').classList.add('hidden');
        });

        function validateInput(input) {
            // Remove any characters that are not digits or slashes
            input.value = input.value.replace(/[^0-9/]/g, '');
        }
    </script>
    <?php
    require_once __DIR__ . '/../../assets/js/manualjs.html';
    ?>
</body>

</html>