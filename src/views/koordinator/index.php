<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Database.php';

if ($_SESSION['role'] === 'teknisi') {
    header('Location: dashboard?key=' . urlencode($_SESSION['token']));
    exit();
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $kode_koordinator = strtoupper($_POST['kode_koordinator']);
        $nama_koordinator = $_POST['nama_koordinator'];
        $sql = "INSERT INTO Koordinator (kode_koordinator, nama_koordinator) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kode_koordinator, $nama_koordinator]);
    } elseif (isset($_POST['update'])) {
        $id_koordinator = $_POST['id_koordinator'];
        $kode_koordinator = strtoupper($_POST['kode_koordinator']);
        $nama_koordinator = $_POST['nama_koordinator'];
        $sql = "UPDATE Koordinator SET kode_koordinator = ?, nama_koordinator = ? WHERE id_koordinator = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kode_koordinator, $nama_koordinator, $id_koordinator]);
    } elseif (isset($_POST['delete'])) {
        $id_koordinator = $_POST['id_koordinator'];
        $sql = "DELETE FROM Koordinator WHERE id_koordinator = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_koordinator]);
    }

    header('Location: koordinator?key=' . urlencode($token));
    exit();
}

$sql = "SELECT * FROM Koordinator";
$stmt = $pdo->query($sql);
$koordinators = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Koordinator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logopusri">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="headercss">
    <link rel="stylesheet" href="picmaincss">
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
            <div class="p-8 mt-20" id="content">
                <h3 class="text-lg font-bold mt-8">Add New Koordinator</h3>
                <form action="" method="POST" class="bg-white p-6 rounded-lg shadow-md">
                    <div class="mb-4">
                        <label for="kode_koordinator" class="block text-gray-700 text-sm font-bold mb-2">Kode Koordinator:</label>
                        <input type="text" id="kode_koordinator" name="kode_koordinator" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-4">
                        <label for="nama_koordinator" class="block text-gray-700 text-sm font-bold mb-2">Nama Koordinator:</label>
                        <input type="text" id="nama_koordinator" name="nama_koordinator" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <button type="submit" name="create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"><i class="bi bi-plus-circle"></i> Add Koordinator</button>
                </form>

                <h3 class="text-lg font-bold mt-8">Koordinator List</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white shadow-md rounded-lg">
                        <thead>
                            <tr class="border-b-2 border-gray-300">
                                <th class="py-2 px-4 bg-gray-100 text-gray-600 text-left text-sm uppercase font-bold">ID</th>
                                <th class="py-2 px-4 bg-gray-100 text-gray-600 text-left text-sm uppercase font-bold">Kode Koordinator</th>
                                <th class="py-2 px-4 bg-gray-100 text-gray-600 text-left text-sm uppercase font-bold">Nama Koordinator</th>
                                <th class="py-2 px-4 bg-gray-100 text-gray-600 text-left text-sm uppercase font-bold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $counter = 1;
                            foreach ($koordinators as $koordinator) : ?>
                                <tr class="hover:bg-gray-200 border-b-2 border-gray-300">
                                    <td class="py-2 px-4"><?php echo $counter++; ?></td>
                                    <td class="py-2 px-4"><?php echo $koordinator['kode_koordinator']; ?></td>
                                    <td class="py-2 px-4"><?php echo $koordinator['nama_koordinator']; ?></td>
                                    <td class="py-2 px-4">
                                        <button onclick="openEditModal(<?php echo $koordinator['id_koordinator']; ?>, '<?php echo $koordinator['kode_koordinator']; ?>', '<?php echo $koordinator['nama_koordinator']; ?>')" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"><i class="bi bi-pencil-square"></i> Edit</button>
                                        <form action="" method="POST" style="display:inline;">
                                            <input type="hidden" name="id_koordinator" value="<?php echo $koordinator['id_koordinator']; ?>">
                                            <button type="submit" name="delete" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" onclick="return confirmDelete()"><i class="bi bi-trash"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div id="editModal" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="closeEditModal()">&times;</span>
                        <h3 class="text-lg font-bold mt-8">Edit Koordinator</h3>
                        <form action="" method="POST" class="bg-white p-6 rounded-lg shadow-md">
                            <input type="hidden" id="edit_id_koordinator" name="id_koordinator">
                            <div class="mb-4">
                                <label for="edit_kode_koordinator" class="block text-gray-700 text-sm font-bold mb-2">Kode Koordinator:</label>
                                <input type="text" id="edit_kode_koordinator" name="kode_koordinator" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            </div>
                            <div class="mb-4">
                                <label for="edit_nama_koordinator" class="block text-gray-700 text-sm font-bold mb-2">Nama Koordinator:</label>
                                <input type="text" id="edit_nama_koordinator" name="nama_koordinator" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            </div>
                            <button type="submit" name="update" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"><i class="bi bi-save"></i> Save Changes</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="headerjs"></script>
    <script>
        function openEditModal(id_koordinator, kode_koordinator, nama_koordinator) {
            document.getElementById('edit_id_koordinator').value = id_koordinator;
            document.getElementById('edit_kode_koordinator').value = kode_koordinator;
            document.getElementById('edit_nama_koordinator').value = nama_koordinator;
            document.getElementById('editModal').style.display = "block";
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = "none";
        }

        function confirmDelete() {
            return confirm('Are you sure you want to delete this Koordinator?');
        }
    </script>
</body>

</html>