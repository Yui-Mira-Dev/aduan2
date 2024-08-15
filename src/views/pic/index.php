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
        $kode_pic = strtoupper($_POST['kode_pic']); // Convert to uppercase
        $nama_pic = $_POST['nama_pic'];
        $sql = "INSERT INTO PIC (kode_pic, nama_pic) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kode_pic, $nama_pic]);
    } elseif (isset($_POST['update'])) {
        $id_pic = $_POST['id_pic'];
        $kode_pic = strtoupper($_POST['kode_pic']); // Convert to uppercase
        $nama_pic = $_POST['nama_pic'];
        $sql = "UPDATE PIC SET kode_pic = ?, nama_pic = ? WHERE id_pic = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kode_pic, $nama_pic, $id_pic]);
    } elseif (isset($_POST['delete'])) {
        $id_pic = $_POST['id_pic'];
        $sql = "DELETE FROM PIC WHERE id_pic = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_pic]);
    }

    // Redirect to avoid form resubmission
    header('Location: pic?key=' . urlencode($token));
    exit();
}

$sql = "SELECT * FROM PIC";
$stmt = $pdo->query($sql);
$pics = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIC</title>
    <link rel="icon" href="logopusri">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="headercss">
    <link rel="stylesheet" href="picmaincss">
</head>

<body class="bg-gray-100">

    <?php
    // Include header component
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
                <h3 class="text-lg font-bold mt-8">Add New PIC</h3>
                <form action="" method="POST" class="bg-white p-6 rounded-lg shadow-md">
                    <div class="mb-4">
                        <label for="kode_pic" class="block text-gray-700 text-sm font-bold mb-2">Kode PIC:</label>
                        <input type="text" id="kode_pic" name="kode_pic" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-4">
                        <label for="nama_pic" class="block text-gray-700 text-sm font-bold mb-2">Nama PIC:</label>
                        <input type="text" id="nama_pic" name="nama_pic" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <button type="submit" name="create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"><i class="bi bi-plus-circle"></i> Add PIC</button>
                </form>

                <h3 class="text-lg font-bold mt-8">PIC List</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white shadow-md rounded-lg">
                        <thead>
                            <tr class="border-b-2 border-gray-300">
                                <th class="py-2 px-4 bg-gray-100 text-gray-600 text-left text-sm uppercase font-bold">ID</th>
                                <th class="py-2 px-4 bg-gray-100 text-gray-600 text-left text-sm uppercase font-bold">Kode PIC</th>
                                <th class="py-2 px-4 bg-gray-100 text-gray-600 text-left text-sm uppercase font-bold">Nama PIC</th>
                                <th class="py-2 px-4 bg-gray-100 text-gray-600 text-left text-sm uppercase font-bold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $counter = 1;
                            foreach ($pics as $pic) : ?>
                                <tr class="hover:bg-gray-200 border-b-2 border-gray-300">
                                    <td class="py-2 px-4"><?php echo $counter++; ?></td>
                                    <td class="py-2 px-4"><?php echo $pic['kode_pic']; ?></td>
                                    <td class="py-2 px-4"><?php echo $pic['nama_pic']; ?></td>
                                    <td class="py-2 px-4">
                                        <button onclick="openEditModal(<?php echo $pic['id_pic']; ?>, '<?php echo $pic['kode_pic']; ?>', '<?php echo $pic['nama_pic']; ?>')" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"><i class="bi bi-pencil-square"></i> Edit</button>
                                        <form action="" method="POST" style="display:inline;">
                                            <input type="hidden" name="id_pic" value="<?php echo $pic['id_pic']; ?>">
                                            <button type="submit" name="delete" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" onclick="return confirmDelete()"><i class="bi bi-trash"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div id="editModal" class="modal" style="display:none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeEditModal()">&times;</span>
                        <h3 class="text-lg font-bold mt-8">Edit PIC</h3>
                        <form action="" method="POST" class="bg-white p-6 rounded-lg shadow-md">
                            <input type="hidden" id="edit_id_pic" name="id_pic">
                            <div class="mb-4">
                                <label for="edit_kode_pic" class="block text-gray-700 text-sm font-bold mb-2">Kode PIC:</label>
                                <input type="text" id="edit_kode_pic" name="kode_pic" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            </div>
                            <div class="mb-4">
                                <label for="edit_nama_pic" class="block text-gray-700 text-sm font-bold mb-2">Nama PIC:</label>
                                <input type="text" id="edit_nama_pic" name="nama_pic" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            </div>
                            <button type="submit" name="update" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"><i class="bi bi-save"></i> Update PIC</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="headerjs"></script>
    <script src="picmainjs"></script>
    <script>
        function openEditModal(id_pic, kode_pic, nama_pic) {
            document.getElementById('edit_id_pic').value = id_pic;
            document.getElementById('edit_kode_pic').value = kode_pic;
            document.getElementById('edit_nama_pic').value = nama_pic;
            document.getElementById('editModal').style.display = "block";
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = "none";
        }

        function confirmDelete() {
            return confirm('Are you sure you want to delete this PIC?');
        }
    </script>
</body>

</html>