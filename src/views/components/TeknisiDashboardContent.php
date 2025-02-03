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
            <?php if ($aduans) : ?>
                <?php foreach ($aduans as $aduan) : ?>
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
                            <?php
                            echo isset($aduan['Hasil_konfrimasi_teknisi'])
                                ? htmlspecialchars($aduan['Hasil_konfrimasi_teknisi'])
                                : 'Belum Ada Hasil';
                            ?>
                        </td>
                        <td class="py-2 px-4">
                            <?php
                            echo isset($aduan['Teknisi_penindaklanjut_aduan'])
                                ? htmlspecialchars($aduan['Teknisi_penindaklanjut_aduan'])
                                : 'Belum Ditugaskan';
                            ?>
                        </td>
                        <td class="py-2 px-4">
                            <?php echo calculateAge($aduan['tanggal_aduan']); ?>
                        </td>
                        <td class="py-2 px-4 text-left block md:table-cell">
                            <?php if ($_SESSION['role'] !== 'Technician') : ?>
                                <div class="flex space-x-2">
                                    <button class="bg-yellow-500 text-white px-2 py-1 rounded-md shadow-sm" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($aduan)); ?>)">
                                        <i class="bi bi-pencil text-base"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="12" class="text-center py-4">No data found for the selected month and year.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal HTML -->
<div id="editModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
        <h3 class="text-lg font-bold mb-4">Edit Complaint</h3>
        <form id="editForm" method="POST" action="update_complaint">
            <input type="hidden" id="id_aduan" name="id_aduan">
            <div class="mb-4">
                <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                <select id="status" name="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <?php foreach ($statuses as $status) : ?>
                        <option value="<?php echo $status['id_status']; ?>"><?php echo $status['description']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="hasil_konfirmasi_teknisi" class="block text-gray-700 text-sm font-bold mb-2">Hasil Konfirmasi Teknis:</label>
                <textarea id="hasil_konfirmasi_teknisi" name="hasil_konfirmasi_teknisi" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
            </div>
            <div class="mb-4">
                <label for="teknisi_penindaklanjut" class="block text-gray-700 text-sm font-bold mb-2">Teknisi Penindaklanjut Aduan:</label>
                <input type="text" id="teknisi_penindaklanjut" name="teknisi_penindaklanjut" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md shadow-sm">Save Changes</button>
                <button type="button" id="closeEditModal" class="ml-4 bg-red-500 text-white px-4 py-2 rounded-md shadow-sm">Close</button>
            </div>
        </form>
    </div>
</div>

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