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

// Handle search filters
$search_username = isset($_GET['username']) ? $_GET['username'] : '';
$search_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$search_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$logs_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $logs_per_page;

// Prepare base SQL query with search filters
$sql = "
    SELECT l.id_log, l.tanggal_login, u.username, l.ip_user_login 
    FROM logs l
    JOIN Users u ON l.id_user = u.Id_user
    WHERE 1=1
";

$params = [];
if ($search_username) {
    $sql .= " AND u.username LIKE ?";
    $params[] = "%$search_username%";
}
if ($search_start_date && $search_end_date) {
    $sql .= " AND l.tanggal_login BETWEEN ? AND ?";
    $params[] = $search_start_date;
    $params[] = $search_end_date;
}

$sql .= " ORDER BY l.id_log DESC
    LIMIT $offset, $logs_per_page
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total logs count for pagination
$sql_count = "SELECT COUNT(*) FROM logs l JOIN Users u ON l.id_user = u.Id_user WHERE 1=1";
if ($search_username) {
    $sql_count .= " AND u.username LIKE ?";
}
if ($search_start_date && $search_end_date) {
    $sql_count .= " AND l.tanggal_login BETWEEN ? AND ?";
}
$stmt_count = $pdo->prepare($sql_count);
$params_count = [];
if ($search_username) {
    $params_count[] = "%$search_username%";
}
if ($search_start_date && $search_end_date) {
    $params_count[] = $search_start_date;
    $params_count[] = $search_end_date;
}
$stmt_count->execute($params_count);
$total_logs = $stmt_count->fetchColumn();
$total_pages = ceil($total_logs / $logs_per_page);

date_default_timezone_set('Asia/Jakarta');

// Get unique usernames for dropdown
$sql_usernames = "SELECT DISTINCT username FROM Users";
$dropdown_usernames = $pdo->query($sql_usernames)->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="30">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs</title>
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
            <div class="p-8 mt-20" id="content">
                <h2 class="text-2xl font-bold mb-4">Logs</h2>
                <p>Current server time (Jakarta): <span id="current-time"><?php echo date('Y-m-d H:i:s'); ?></span></p>

                <!-- Search Form -->
                <form method="GET" class="mb-4">
                    <input type="hidden" name="key" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="flex gap-4 mb-4">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <select id="username" name="username" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select Username</option>
                                <?php foreach ($dropdown_usernames as $username) : ?>
                                    <option value="<?php echo htmlspecialchars($username); ?>" <?php echo $search_username === $username ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($username); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($search_start_date); ?>" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($search_end_date); ?>" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none">Search</button>
                </form>

                <div class="scrollable-table-container">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead>
                            <tr class="border-b-2 border-gray-300">
                                <th class="py-2 px-4 border-b">Log ID</th>
                                <th class="py-2 px-4 border-b">Date and Time</th>
                                <th class="py-2 px-4 border-b">Username</th>
                                <th class="py-2 px-4 border-b">IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $counter = $offset + 1;
                            foreach ($logs as $log) : ?>
                                <tr class="hover:bg-gray-200 text-center border-b-2 border-gray-300">
                                    <td class="py-2 px-4 border-b"><?php echo $counter++; ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo $log['tanggal_login']; ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo $log['username']; ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo $log['ip_user_login']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    <?php if ($total_pages > 1) : ?>
                        <nav class="flex justify-between">
                            <a href="?key=<?php echo htmlspecialchars($token); ?>&page=1<?php if ($search_username) echo '&username=' . urlencode($search_username);
                                                                                        if ($search_start_date) echo '&start_date=' . urlencode($search_start_date);
                                                                                        if ($search_end_date) echo '&end_date=' . urlencode($search_end_date); ?>" class="text-blue-500 hover:underline">First</a>
                            <a href="?key=<?php echo htmlspecialchars($token); ?>&page=<?php echo max(1, $page - 1); ?><?php if ($search_username) echo '&username=' . urlencode($search_username);
                                                                                                                        if ($search_start_date) echo '&start_date=' . urlencode($search_start_date);
                                                                                                                        if ($search_end_date) echo '&end_date=' . urlencode($search_end_date); ?>" class="text-blue-500 hover:underline">Previous</a>
                            <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                            <a href="?key=<?php echo htmlspecialchars($token); ?>&page=<?php echo min($total_pages, $page + 1); ?><?php if ($search_username) echo '&username=' . urlencode($search_username);
                                                                                                                                    if ($search_start_date) echo '&start_date=' . urlencode($search_start_date);
                                                                                                                                    if ($search_end_date) echo '&end_date=' . urlencode($search_end_date); ?>" class="text-blue-500 hover:underline">Next</a>
                            <a href="?key=<?php echo htmlspecialchars($token); ?>&page=<?php echo $total_pages; ?><?php if ($search_username) echo '&username=' . urlencode($search_username);
                                                                                                                    if ($search_start_date) echo '&start_date=' . urlencode($search_start_date);
                                                                                                                    if ($search_end_date) echo '&end_date=' . urlencode($search_end_date); ?>" class="text-blue-500 hover:underline">Last</a>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="headerjs"></script>
    <script>
        function updateTime() {
            const now = new Date();
            const options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Jakarta',
                hour12: false
            };
            const timeString = now.toLocaleString('id-ID', options).replace(',', '');

            document.getElementById('current-time').textContent = timeString;
        }

        setInterval(updateTime, 1000);

        updateTime();
    </script>
    <?php
    require_once __DIR__ . '/../../assets/js/manualjs.html';
    ?>
</body>

</html>