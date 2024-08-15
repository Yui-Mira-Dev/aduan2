<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/Database.php';

function isTokenValid($token)
{
    global $pdo;

    $sql = "SELECT id_user FROM tokens WHERE token = ? AND expires_at > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    return $stmt->fetchColumn();
}

$token = isset($_GET['key']) ? $_GET['key'] : '';

if (!isset($_SESSION['id_user'], $_SESSION['username'], $_SESSION['role']) || !isTokenValid($token)) {
    header('Location: dashboard');
    exit();
}

$id_aduan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $artifact_id = $_POST['artifact_id'];
        $nama_pengadu = $_POST['nama_pengadu'];
        $title_aduan = $_POST['title_aduan'];
        $ext = $_POST['ext'];
        $tempat_aduan = $_POST['tempat_aduan'];
        $PIC = $_POST['PIC'];
        $Koordinator = $_POST['Koordinator'];
        $Status = $_POST['Status'];
        $Keterangan = $_POST['Keterangan'];

        // Get the existing tanggal_aduan from the database
        $sql = "SELECT tanggal_aduan FROM daftar_aduan WHERE id_aduan = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_aduan]);
        $aduan = $stmt->fetch(PDO::FETCH_ASSOC);
        $tanggal_aduan = $aduan['tanggal_aduan'];

        // Generate the age based on the status
        $Umur_aduan = generateAge($tanggal_aduan, $Status);

        $sql = "UPDATE daftar_aduan SET artifact_id = ?, nama_pengadu = ?, title_aduan = ?, ext = ?, tempat_aduan = ?, PIC = ?, Koordinator = ?, Status = ?, Keterangan = ?, Umur_aduan = ? WHERE id_aduan = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$artifact_id, $nama_pengadu, $title_aduan, $ext, $tempat_aduan, $PIC, $Koordinator, $Status, $Keterangan, $Umur_aduan, $id_aduan]);

        header('Location: dashboard?key=' . htmlspecialchars($token));
        exit();
    }
}

function generateAge($date, $status)
{
    $now = new DateTime();
    $date = new DateTime($date);
    $interval = $now->diff($date);

    if ($status == 1) {
        $totalDays = $interval->days;
        if ($totalDays < 7) {
            return $interval->format('%d days %h hours %i minutes');
        } elseif ($totalDays < 30) {
            return $interval->format('%a days %h hours %i minutes');
        } else {
            return $interval->format('%m months %d days %h hours %i minutes');
        }
    } else {
        return $interval->format('%y years %m months %d days');
    }
}

$sql = "SELECT * FROM daftar_aduan WHERE id_aduan = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_aduan]);
$aduan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aduan) {
    header('Location: dashboard?key=' . htmlspecialchars($token));
    exit();
}

// Fetch PICs, Koordinators, and Statuses
$pics = $pdo->query("SELECT * FROM PIC")->fetchAll(PDO::FETCH_ASSOC);
$koordinators = $pdo->query("SELECT * FROM Koordinator")->fetchAll(PDO::FETCH_ASSOC);
$statuses = $pdo->query("SELECT id_status, description FROM status")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Complaint</title>
    <link rel="icon" href="logopusri">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="headercss">
</head>

<body class="bg-gray-100">
    <?php include __DIR__ . '/../views/components/header.php'; ?>
    <main class="content-wrapper transition-all duration-300 ease-in-out ml-0 mt-20 lg:ml-64">
        <div class="p-8">
            <button id="toggleSidebar" class="lg:hidden fixed top-4 left-4 bg-blue-500 text-white px-4 py-2 rounded-md focus:outline-none">
                <i class="bi bi-chevron-double-right text-lg"></i>
            </button>
            <h3 class="text-2xl font-bold mb-6">Edit Complaint</h3>
            <form action="" method="POST" class="bg-white p-6 rounded-lg shadow-md">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                        <label for="artifact_id" class="block text-gray-700 text-sm font-semibold mb-2">Artifact Id:</label>
                        <input type="number" id="artifact_id" name="artifact_id" value="<?php echo htmlspecialchars($aduan['artifact_id']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div>
                        <label for="nama_pengadu" class="block text-gray-700 text-sm font-semibold mb-2">Nama Pengadu:</label>
                        <input type="text" id="nama_pengadu" name="nama_pengadu" value="<?php echo htmlspecialchars($aduan['nama_pengadu']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                        <label for="title_aduan" class="block text-gray-700 text-sm font-semibold mb-2">Title Aduan:</label>
                        <input type="text" id="title_aduan" name="title_aduan" value="<?php echo htmlspecialchars($aduan['title_aduan']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div>
                        <label for="ext" class="block text-gray-700 text-sm font-semibold mb-2">Ext:</label>
                        <input type="text" id="ext" name="ext" value="<?php echo htmlspecialchars($aduan['ext']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="tempat_aduan" class="block text-gray-700 text-sm font-semibold mb-2">Tempat Aduan:</label>
                    <input type="text" id="tempat_aduan" name="tempat_aduan" value="<?php echo htmlspecialchars($aduan['tempat_aduan']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                        <label for="PIC" class="block text-gray-700 text-sm font-semibold mb-2">PIC:</label>
                        <select id="PIC" name="PIC" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select PIC</option>
                            <?php foreach ($pics as $pic) : ?>
                                <option value="<?php echo $pic['id_pic']; ?>" <?php echo $aduan['PIC'] == $pic['id_pic'] ? 'selected' : ''; ?>><?php echo $pic['kode_pic']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="Koordinator" class="block text-gray-700 text-sm font-semibold mb-2">Koordinator:</label>
                        <select id="Koordinator" name="Koordinator" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select Koordinator</option>
                            <?php foreach ($koordinators as $koordinator) : ?>
                                <option value="<?php echo $koordinator['id_koordinator']; ?>" <?php echo $aduan['Koordinator'] == $koordinator['id_koordinator'] ? 'selected' : ''; ?>><?php echo $koordinator['kode_koordinator']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="Status" class="block text-gray-700 text-sm font-semibold mb-2">Status:</label>
                    <select id="Status" name="Status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Select Status</option>
                        <?php foreach ($statuses as $status) : ?>
                            <option value="<?php echo $status['id_status']; ?>" <?php echo $aduan['Status'] == $status['id_status'] ? 'selected' : ''; ?>><?php echo $status['description']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="Keterangan" class="block text-gray-700 text-sm font-semibold mb-2">Keterangan:</label>
                    <textarea id="Keterangan" name="Keterangan" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($aduan['Keterangan']); ?></textarea>
                </div>
                <input type="hidden" name="update" value="1">
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>