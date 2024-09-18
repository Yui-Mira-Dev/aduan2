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
    <div class="flex-1 p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
        <h3 class="text-lg font-semibold text-gray-700 mb-3">Existing Complaints</h3>
        <form method="GET" action="<?php echo htmlspecialchars($urlWithToken); ?>" class="flex flex-wrap space-y-3 sm:space-y-0 sm:space-x-4 items-center">
            <input type="hidden" name="page" value="<?php echo htmlspecialchars($current_page); ?>">
            <input type="hidden" name="month" value="<?php echo htmlspecialchars($selectedMonth); ?>">
            <input type="hidden" name="year" value="<?php echo htmlspecialchars($selectedYear); ?>">

            <button type="submit" name="sort" value="pending" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 transition w-full sm:w-auto">
                Show Pending
            </button>

            <button type="submit" name="sort" value="in-progress" class="bg-green-600 text-white px-4 py-2 rounded-md shadow hover:bg-green-700 transition w-full sm:w-auto">
                Show In-Progress
            </button>

            <input type="hidden" name="key" value="<?php echo htmlspecialchars($_SESSION['token']); ?>">
        </form>
    </div>
</div>

<!-- Kelompok Box 3: Slim Filter Form with Mobile Optimization -->
<div class="mt-4 flex justify-center items-center mb-3">
    <form method="GET" action="<?php echo htmlspecialchars($urlWithToken); ?>" class="flex flex-col sm:flex-row sm:space-x-4 space-y-3 sm:space-y-0 items-center w-full">

        <!-- Month Selector -->
        <div class="w-full sm:w-auto">
            <select name="month" class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Month</option>
                <?php for ($m = 1; $m <= 12; $m++) : ?>
                    <option value="<?php echo $m; ?>" <?php if (isset($_GET['month']) && $_GET['month'] == $m) echo 'selected'; ?>>
                        <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <!-- Year Selector -->
        <div class="w-full sm:w-auto">
            <select name="year" class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
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

        <!-- Submit Button -->
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition w-full sm:w-auto">
            Filter
        </button>

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
            <?php else : ?>
                <tr>
                    <td colspan="12" class="text-center py-4">No data found for the selected month and year.</td>
                </tr>
            <?php endif; ?>
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