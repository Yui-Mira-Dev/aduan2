<!-- Flex Container for Side-by-Side Layout -->
<div class="flex flex-col sm:flex-row sm:space-x-4 bg-gray-100 p-4 rounded-lg shadow-sm">

    <!-- Kelompok Box 1: Add New Complaint -->
    <div class="flex-1 mb-4 sm:mb-0 p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
        <h3 class="text-lg font-semibold text-gray-700 mb-3">Add New Complaint</h3>
        <button id="toggleModal" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 transition">
            Add New Complaint
        </button>
    </div>

    <!-- Kelompok Box 2: Existing Complaints -->
    <!-- Kelompok Box 2: Existing Complaints -->
    <div class="flex-1 p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
        <h3 class="text-lg font-semibold text-gray-700 mb-3">Existing Complaints</h3>
        <form method="GET" action="dashboardcontent" class="flex flex-wrap space-y-3 sm:space-y-0 sm:space-x-4 items-center">
            <!-- Parameter yang akan dikirimkan -->
            <input type="hidden" name="page" value="<?php echo htmlspecialchars($current_page); ?>">
            <input type="hidden" name="month" value="<?php echo htmlspecialchars($selectedMonth); ?>">
            <input type="hidden" name="year" value="<?php echo htmlspecialchars($selectedYear); ?>">

            <!-- Tombol untuk menyaring data berdasarkan status -->
            <button type="submit" name="sort" value="pending" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 transition w-full sm:w-auto">
                Show Pending
            </button>

            <button type="submit" name="sort" value="in-progress" class="bg-green-600 text-white px-4 py-2 rounded-md shadow hover:bg-green-700 transition w-full sm:w-auto">
                Show In-Progress
            </button>

            <!-- Token keamanan, jika diperlukan -->
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

<!-- Modal Background -->
<div id="modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden mt-20">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg overflow-y-auto max-h-screen p-4 md:p-6">
        <div class="p-4 md:p-6">
            <h3 class="text-lg font-bold mt-4 md:mt-8">Add New Complaint</h3>
            <form action="" method="POST" class="bg-white p-4 md:p-6 rounded-lg shadow-md space-y-4 md:space-y-6">
                <div class="mb-4">
                    <label for="artifact_id" class="block text-gray-700 text-sm font-bold mb-2">Artifact Id:</label>
                    <input type="number" id="artifact_id" name="artifact_id" value="<?php echo htmlspecialchars($next_artifact_id); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="nama_pengadu" class="block text-gray-700 text-sm font-bold mb-2">Nama Pengadu:</label>
                    <input type="text" id="nama_pengadu" name="nama_pengadu" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="title_aduan" class="block text-gray-700 text-sm font-bold mb-2">Title Aduan:</label>
                    <input type="text" id="title_aduan" name="title_aduan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="ext" class="block text-gray-700 text-sm font-bold mb-2">Ext/No TLP:</label>
                    <input type="text" id="ext" name="ext"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        pattern="[0-9/]*" inputmode="text"
                        title="Hanya angka dan simbol / yang diperbolehkan" required
                        oninput="validateInput(this)">
                </div>
                <div class="mb-4">
                    <label for="tempat_aduan" class="block text-gray-700 text-sm font-bold mb-2">Tempat Aduan:</label>
                    <input type="text" id="tempat_aduan" name="tempat_aduan" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="PIC" class="block text-gray-700 text-sm font-bold mb-2">PIC:</label>
                    <select id="PIC" name="PIC" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Select PIC</option>
                        <?php foreach ($pics as $pic) : ?>
                            <option value="<?php echo $pic['id_pic']; ?>"><?php echo $pic['kode_pic']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="Koordinator" class="block text-gray-700 text-sm font-bold mb-2">Koordinator:</label>
                    <select id="Koordinator" name="Koordinator" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Select Koordinator</option>
                        <?php foreach ($koordinators as $koordinator) : ?>
                            <option value="<?php echo $koordinator['id_koordinator']; ?>"><?php echo $koordinator['kode_koordinator']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="Status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                    <select id="Status" name="Status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Select Status</option>
                        <?php foreach ($statuses as $status) : ?>
                            <option value="<?php echo $status['id_status']; ?>" <?php echo $status['id_status'] == 3 ? 'selected' : ''; ?>>
                                <?php echo $status['description']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="Keterangan" class="block text-gray-700 text-sm font-bold mb-2">Keterangan:</label>
                    <textarea id="Keterangan" name="Keterangan" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>
                <input type="hidden" name="tanggal_aduan" value="<?php echo date('Y-m-d H:i:s'); ?>">
                <div class="flex justify-end mt-4 md:mt-6 space-x-2 md:space-x-4 pb-4 md:pb-6">
                    <button type="submit" name="create" class="bg-blue-500 text-white px-4 py-2 rounded">Add Complaint</button>
                    <button type="button" id="closeModal" class="bg-red-500 text-white px-4 py-2 rounded">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>