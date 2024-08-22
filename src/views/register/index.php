<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables and database connection
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../models/Database.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../..');
$dotenv->load();

// Get PDO instance
try {
    $pdo = getPDOInstance();
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}

// Redirect if the user is a technician
if ($_SESSION['role'] === 'teknisi') {
    header('Location: dashboard?key=' . urlencode($_SESSION['token']));
    exit();
}

// Token validation function
function isTokenValid($pdo, $token)
{
    $sql = "SELECT id_user, expires_at FROM tokens WHERE token = ? AND expires_at > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['id_user'] : false;
}

// Check if username or email already exists
function isUsernameOrEmailExists($pdo, $username, $email)
{
    $sql = "SELECT COUNT(*) FROM Users WHERE username = ? OR email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $email]);
    $count = $stmt->fetchColumn();

    return $count > 0;
}

// Fetch all users
function fetchUsers($pdo)
{
    $sql = "SELECT id_user, username, email, role FROM Users";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Register a new user
function registerUser($pdo, $username, $email, $password, $role)
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO Users (username, email, password, role) VALUES (:username, :email, :password, :role)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':role', $role);

    return $stmt->execute();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'deleteUser') {
            $id_user = $_POST['id_user'];
            $sql = "DELETE FROM Users WHERE id_user = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$id_user])) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'User deleted successfully!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Failed to delete user!'];
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }

        if ($_POST['action'] === 'register') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';

            if ($username && $email && $password && $role) {
                if (isUsernameOrEmailExists($pdo, $username, $email)) {
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Username atau email sudah ada!'];
                } else {
                    if (registerUser($pdo, $username, $email, $password, $role)) {
                        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Registrasi berhasil!'];
                    } else {
                        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Registrasi gagal!'];
                    }
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Harap isi semua field!'];
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        } elseif ($_POST['action'] === 'editUser') {
            $id_user = $_POST['id_user'];
            $username = $_POST['username'];
            $email = $_POST['email'];
            $role = $_POST['role'];

            $sql = "UPDATE Users SET username = ?, email = ?, role = ? WHERE id_user = ?";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$username, $email, $role, $id_user])) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'User details updated!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Update failed!'];
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        } elseif ($_POST['action'] === 'editPassword') {
            $id_user = $_POST['id_user'];
            $password = $_POST['password'];

            if ($password) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE Users SET password = ? WHERE id_user = ?";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$hashedPassword, $id_user])) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Password updated!'];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Password update failed!'];
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Password cannot be empty!'];
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }
}

// Fetch users for displaying in the user list
$users = fetchUsers($pdo);

// Check token validity
$token = isset($_GET['key']) ? $_GET['key'] : '';

if (!isset($_SESSION['id_user'], $_SESSION['username'], $_SESSION['role']) || !isTokenValid($pdo, $token)) {
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
    <title>Registrasi Pengguna</title>
    <link rel="icon" href="logopusri">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="headercss">
    <link rel="stylesheet" href="picmaincss">
    <style>
        .opacity-0 {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        #flashMessage {
            z-index: 9999 !important;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: auto;
            max-width: 400px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #flashMessage.success {
            background-color: #48bb78;
            color: #fff;
        }

        #flashMessage.error {
            background-color: #f56565;
            color: #fff;
        }

        #flashMessage svg {
            vertical-align: middle;
            margin-right: 8px;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <?php
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        $flashClass = $flash['type'] === 'success' ? 'success' : 'error';
        $icon = $flash['type'] === 'success' ? '<svg class="inline w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' : '<svg class="inline w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        echo "<div id='flashMessage' class='$flashClass'>
            $icon {$flash['message']}
        </div>";
        unset($_SESSION['flash_message']);
    }
    ?>

    <main class="content-wrapper transition-all duration-300 ease-in-out ml-0 mt-10 lg:ml-64">
        <div class="flex flex-col items-center justify-center">
            <button id="toggleSidebar" class="lg:hidden fixed top-4 mt-2 left-4 bg-blue-500 text-white px-4 py-2 rounded focus:outline-none">
                <i class="bi bi-chevron-double-right text-lg"></i>
            </button>
            <div id="loading" class="fixed inset-0 flex items-center justify-center bg-white">
                <div class="loading-spinner"></div>
            </div>

            <!-- User Table -->
            <div class="bg-white p-8 rounded-lg shadow-md w-full mt-10" id="userTable">
                <!-- Add User Button -->
                <div class="flex justify-start mb-4">
                    <button id="openAddUserModal" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add User</button>
                </div>
                <h2 class="text-2xl font-bold text-center mb-6">User List</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button class="edit-btn text-blue-600 hover:text-blue-900" data-user-id="<?php echo $user['id_user']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>" data-email="<?php echo htmlspecialchars($user['email']); ?>" data-role="<?php echo htmlspecialchars($user['role']); ?>">Edit</button>
                                        <button class="edit-password-btn text-green-600 hover:text-green-900 ml-4" data-user-id="<?php echo $user['id_user']; ?>">Edit Password</button>
                                        <button class="delete-btn text-red-600 hover:text-red-900 ml-4" data-user-id="<?php echo $user['id_user']; ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal for adding user -->
        <div id="addUserModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-2xl font-bold mb-4">Add New User</h2>
                <form id="registerForm" method="POST">
                    <input type="hidden" name="action" value="register">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="username" name="username" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mt-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mt-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mt-4">
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="role" name="role" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="admin">Admin</option>
                            <option value="teknisi">Teknisi</option>
                            <option value="deskjob">Deskjob</option>
                        </select>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="button" id="closeAddUserModal" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Close</button>
                        <button type="submit" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add User</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal for editing user details -->
        <div id="editUserModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-2xl font-bold mb-4">Edit User Details</h2>
                <form id="editUserForm" method="POST">
                    <input type="hidden" name="action" value="editUser">
                    <input type="hidden" id="editUserId" name="id_user">
                    <div>
                        <label for="editUsername" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="editUsername" name="username" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mt-4">
                        <label for="editEmail" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="editEmail" name="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mt-4">
                        <label for="editRole" class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="editRole" name="role" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="admin">Admin</option>
                            <option value="teknisi">Teknisi</option>
                            <option value="deskjob">Deskjob</option>
                        </select>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="button" id="closeEditUserModal" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Close</button>
                        <button type="submit" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal for editing password -->
        <div id="editPasswordModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-2xl font-bold mb-4">Change Password</h2>
                <form id="editPasswordForm" method="POST">
                    <input type="hidden" name="action" value="editPassword">
                    <input type="hidden" id="editPasswordUserId" name="id_user">
                    <div>
                        <label for="newPassword" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input type="password" id="newPassword" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="button" id="closeEditPasswordModal" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Close</button>
                        <button type="submit" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Password</button>
                    </div>
                </form>
            </div>
        </div>

        <script src="headerjs"></script>
        <script src="registerjs"></script>
        <?php
        require_once __DIR__ . '/../../assets/js/manualjs.html';
        ?>
    </main>
</body>

</html>