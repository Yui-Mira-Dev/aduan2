<!-- Flex Container for Side-by-Side Layout -->
<div class="flex flex-col sm:flex-row sm:space-x-6 bg-gray-100 p-6 rounded-lg shadow-lg">

    <!-- Kelompok Box 2 -->
    <div class="flex-1 mb-6 p-6 bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Existing Complaints</h3>
        <form method="GET" action="dashboardcontent" class="flex flex-col sm:flex-row sm:space-x-4 space-y-4 sm:space-y-0 items-center">
            <input type="hidden" name="page" value="<?php echo htmlspecialchars($current_page); ?>">
            <input type="hidden" name="month" value="<?php echo htmlspecialchars($selectedMonth); ?>">
            <input type="hidden" name="year" value="<?php echo htmlspecialchars($selectedYear); ?>">

            <button type="submit" name="sort" value="pending" class="bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition w-full sm:w-auto mb-2 sm:mb-0">
                Show Pending
            </button>

            <button type="submit" name="sort" value="in-progress" class="bg-green-600 text-white px-5 py-2 rounded-lg shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition w-full sm:w-auto">
                Show In-Progress
            </button>

            <input type="hidden" name="key" value="<?php echo htmlspecialchars($_SESSION['token']); ?>">
        </form>
    </div>

    <!-- Kelompok Box 3 -->
    <div class="flex-1 mb-6 p-6 bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 flex justify-center items-center min-h-[300px]">
        <form method="GET" action="dashboardcontent" class="flex flex-col sm:flex-row sm:space-x-4 space-y-4 sm:space-y-0 items-center">
            <div class="w-full sm:w-auto">
                <select name="month" class="border border-gray-300 rounded-lg px-4 py-2 w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">Select Month</option>
                    <?php for ($m = 1; $m <= 12; $m++) : ?>
                        <option value="<?php echo $m; ?>" <?php if (isset($_GET['month']) && $_GET['month'] == $m) echo 'selected'; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="w-full sm:w-auto">
                <select name="year" class="border border-gray-300 rounded-lg px-4 py-2 w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">Select Year</option>
                    <?php
                    $startYear = date('Y') - 5;
                    $endYear = date('Y');
                    for ($y = $startYear; $y <= $endYear; $y++) : ?>
                        <option value="<?php echo $y; ?>" <?php if (isset($_GET['year']) && $_GET['year'] == $y) echo 'selected'; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition w-full sm:w-auto">
                Filter
            </button>

            <input type="hidden" name="key" value="<?php echo htmlspecialchars($_SESSION['token']); ?>">
        </form>
    </div>
</div>

<!-- main -->
<?php
// Ambil hanya 20 log terbaru dari tabel 'daftar_aduan' berdasarkan ID secara descending
$aduans = $pdo->query("
    SELECT tanggal_aduan, title_aduan, Status 
    FROM daftar_aduan 
    ORDER BY id_aduan DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch counts for each status
$sql = "SELECT Status, COUNT(*) as total FROM daftar_aduan GROUP BY Status";
$stmt = $pdo->query($sql);
$statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Assign default values if no data exists
$totalComplete = $statusCounts[1] ?? 0;
$totalInProgress = $statusCounts[2] ?? 0;
$totalPending = $statusCounts[3] ?? 0;
?>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4">
    <!-- Card for Complete -->
    <div class="bg-green-100 border border-green-300 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <h3 class="text-lg font-semibold text-green-700">Total Complete</h3>
        <p class="text-3xl font-bold text-green-800 mt-2"><?php echo $totalComplete; ?></p>
    </div>

    <!-- Card for In-Progress -->
    <div class="bg-yellow-100 border border-yellow-300 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <h3 class="text-lg font-semibold text-yellow-700">Total In-Progress</h3>
        <p class="text-3xl font-bold text-yellow-800 mt-2"><?php echo $totalInProgress; ?></p>
    </div>

    <!-- Card for Pending -->
    <div class="bg-red-100 border border-red-300 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <h3 class="text-lg font-semibold text-red-700">Total Pending</h3>
        <p class="text-3xl font-bold text-red-800 mt-2"><?php echo $totalPending; ?></p>
    </div>
</div>


<!-- Log Data Latest Complaints -->
<div class="mt-6">
    <h3 class="text-xl font-semibold mb-4">Log Data Terbaru</h3>
    <table class="min-w-full border-collapse border border-gray-200">
        <thead>
            <tr>
                <th class="border border-gray-300 px-4 py-2 text-left">Tanggal Aduan</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Title Aduan</th>
                <th class="border border-gray-300 px-4 py-2 text-left">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($aduans as $aduan): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?php echo $aduan['tanggal_aduan']; ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?php echo $aduan['title_aduan']; ?></td>
                    <td class="border border-gray-300 px-4 py-2">
                        <?php
                        // Tampilkan deskripsi status berdasarkan nilai status
                        switch ($aduan['Status']) {
                            case 1:
                                echo 'Aduan Telah Complete';
                                break;
                            case 2:
                                echo 'Aduan Dalam Proses';
                                break;
                            case 3:
                                echo 'Aduan Baru Ditambahkan';
                                break;
                            default:
                                echo 'Status Tidak Dikenal';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>