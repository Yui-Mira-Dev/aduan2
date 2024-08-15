<h3 class="text-lg font-bold mt-8">Add New Complaint</h3>
<button id="toggleModal" class="bg-blue-500 text-white px-4 py-2 rounded">Add New Complaint</button>
<h3 class="text-lg font-bold mt-8">Existing Complaints</h3>

<?php
// Retrieve sorting choice from query parameter (default to showing Status 2 first)
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'pending';

// Separate complaints into two arrays
$inProgressAduans = array_filter($aduans, fn($aduan) => $aduan['Status'] == 2);
$pendingAduans = array_filter($aduans, fn($aduan) => $aduan['Status'] == 3);

// Merge arrays based on sorting choice
if ($sortOrder === 'pending') {
    $sortedAduans = array_merge($pendingAduans, $inProgressAduans);
} else {
    $sortedAduans = array_merge($inProgressAduans, $pendingAduans);
}

// URL with token
$urlWithToken = 'dashboard?key=' . urlencode($_SESSION['token']);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
?>

<div class="mb-4">
    <form method="GET" action="<?php echo htmlspecialchars($urlWithToken); ?>" class="flex space-x-2">
        <input type="hidden" name="page" value="<?php echo htmlspecialchars($current_page); ?>">
        <button type="submit" name="sort" value="pending" class="bg-blue-500 text-white px-4 py-2 rounded-md shadow-sm">Show Pending First</button>
        <button type="submit" name="sort" value="in-progress" class="bg-green-500 text-white px-4 py-2 rounded-md shadow-sm">Show In-Progress First</button>
        <input type="hidden" name="key" value="<?php echo htmlspecialchars($_SESSION['token']); ?>">
    </form>
</div>

<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200 overflow-x-auto">
        <thead>
            <tr class="border-b-2 border-gray-300">
                <th class="py-2 px-4 bg-gray-100">Artifact Id</th>
                <th class="py-2 px-4 bg-gray-100">Nama Pengadu</th>
                <th class="py-2 px-4 bg-gray-100 w-1/6">Title Aduan</th>
                <th class="py-2 px-4 bg-gray-100 w-1/6">Tempat Aduan</th>
                <th class="py-2 px-4 bg-gray-100 w-1/6">PIC</th>
                <th class="py-2 px-4 bg-gray-100 w-1/6">Koordinator</th>
                <th class="py-2 px-4 bg-gray-100 w-1/6">Status</th>
                <th class="py-2 px-4 bg-gray-100 w-1/6">Keterangan</th>
                <th class="py-2 px-4 bg-gray-100 w-1/6">Hasil Konfirmasi Teknis</th>
                <th class="py-2 px-4 bg-gray-100 w-1/6">Teknisi Penindaklanjut Aduan</th>
                <th class="py-2 px-4 bg-gray-100">Umur Aduan</th>
                <th class="py-2 px-4 bg-gray-100">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sortedAduans as $aduan) : ?>
                <tr class="hover:bg-gray-200 border-b-2 border-gray-300">
                    <td class="py-2 px-4"><?php echo htmlspecialchars($aduan['artifact_id']); ?></td>
                    <td class="py-2 px-4"><?php echo htmlspecialchars($aduan['nama_pengadu']); ?></td>
                    <td class="py-2 px-4"><?php echo htmlspecialchars($aduan['title_aduan']); ?></td>
                    <td class="py-2 px-4"><?php echo htmlspecialchars($aduan['tempat_aduan']); ?></td>
                    <td class="py-2 px-4">
                        <?php
                        $pic = array_filter($pics, fn($p) => $p['id_pic'] == $aduan['PIC']);
                        echo $pic ? reset($pic)['kode_pic'] : 'N/A';
                        ?>
                    </td>
                    <td class="py-2 px-4">
                        <?php
                        $koordinator = array_filter($koordinators, fn($k) => $k['id_koordinator'] == $aduan['Koordinator']);
                        echo $koordinator ? reset($koordinator)['kode_koordinator'] : 'N/A';
                        ?>
                    </td>
                    <td class="py-2 px-4">
                        <?php
                        $status = array_filter($statuses, fn($s) => $s['id_status'] == $aduan['Status']);
                        echo $status ? reset($status)['description'] : 'N/A';
                        ?>
                    </td>
                    <td class="py-2 px-4"><?php echo htmlspecialchars($aduan['Keterangan']); ?></td>
                    <td class="py-2 px-4">
                        <?php echo isset($aduan['Hasil_konfrimasi_teknisi']) ? htmlspecialchars($aduan['Hasil_konfrimasi_teknisi']) : 'N/A'; ?>
                    </td>
                    <td class="py-2 px-4">
                        <?php echo isset($aduan['Teknisi_penindaklanjut_aduan']) ? htmlspecialchars($aduan['Teknisi_penindaklanjut_aduan']) : 'N/A'; ?>
                    </td>
                    <td class="py-2 px-4">
                        <?php echo calculateAge($aduan['tanggal_aduan']); ?>
                    </td>
                    <td class="py-2 px-4 text-left block md:table-cell">
                        <?php if ($_SESSION['role'] !== 'Technician') : ?>
                            <div class="flex space-x-2">
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirmDelete();">
                                    <input type="hidden" name="id_aduan" value="<?php echo htmlspecialchars($aduan['id_aduan']); ?>">
                                    <button type="submit" name="delete" class="bg-red-500 text-white px-2 py-1 rounded-md shadow-sm">
                                        <i class="bi bi-trash text-base"></i>
                                    </button>
                                </form>
                                <button class="bg-yellow-500 text-white px-2 py-1 rounded-md shadow-sm" onclick="window.location.href='edit_complaint?id=<?php echo $aduan['id_aduan']; ?>&key=<?php echo htmlspecialchars($token); ?>'">
                                    <i class="bi bi-pencil text-base"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-6 flex justify-center space-x-2 mb-10">
    <?php if ($current_page > 1) : ?>
        <a href="?page=<?php echo $current_page - 1; ?>&sort=<?php echo htmlspecialchars($sortOrder); ?>&key=<?php echo htmlspecialchars($_SESSION['token']); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-md shadow-sm">Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
        <a href="?page=<?php echo $i; ?>&sort=<?php echo htmlspecialchars($sortOrder); ?>&key=<?php echo htmlspecialchars($_SESSION['token']); ?>" class="bg-<?php echo $i == $current_page ? 'gray' : 'blue'; ?>-500 text-white px-4 py-2 rounded-md shadow-sm"><?php echo $i; ?></a>
    <?php endfor; ?>

    <?php if ($current_page < $total_pages) : ?>
        <a href="?page=<?php echo $current_page + 1; ?>&sort=<?php echo htmlspecialchars($sortOrder); ?>&key=<?php echo htmlspecialchars($_SESSION['token']); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-md shadow-sm">Next</a>
    <?php endif; ?>
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
                    <label for="ext" class="block text-gray-700 text-sm font-bold mb-2">Ext:</label>
                    <input type="text" id="ext" name="ext" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
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