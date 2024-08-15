<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

function isTokenValid($token)
{
    global $pdo;

    $sql = "SELECT id_user, expires_at FROM tokens WHERE token = ? AND expires_at > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['id_user'] : false;
}

$token = isset($_GET['key']) ? $_GET['key'] : '';

if (!isset($_SESSION['id_user'], $_SESSION['username'], $_SESSION['role']) || !isTokenValid($token)) {
    header('Location: index');
    exit();
}

date_default_timezone_set('Asia/Jakarta');

// Pagination variables
$limit = 10; // Number of entries per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch data from database
$whereClauses = ["da.Status = 1"];
$params = [];

// Check if date filter is set
$currentDate = isset($_GET['current_date']) ? date('Y-m-d') : null;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

if ($currentDate) {
    $whereClauses[] = "DATE(da.tanggal_aduan) = ?";
    $params[] = $currentDate;
} elseif ($startDate && $endDate) {
    $whereClauses[] = "DATE(da.tanggal_aduan) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
}

$whereSQL = implode(' AND ', $whereClauses);

// Get the total number of records
$countSql = "SELECT COUNT(*) FROM daftar_aduan da WHERE $whereSQL";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalRecords = $stmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Fetch data from database with joins
$sql = "SELECT 
            da.id_aduan,
            da.artifact_id,
            da.tanggal_aduan,
            da.nama_pengadu,
            da.title_aduan,
            da.ext,
            da.tempat_aduan,
            da.PIC,
            da.Koordinator,
            da.Status,
            da.Umur_aduan,
            da.Keterangan,
            da.Hasil_konfrimasi_teknisi,
            da.Teknisi_penindaklanjut_aduan,
            s.description AS status_description,
            p.kode_pic AS pic_code,
            p.nama_pic AS pic_name,
            k.kode_koordinator AS koordinator_code,
            k.nama_koordinator AS koordinator_name
        FROM daftar_aduan da
        JOIN status s ON da.Status = s.id_status
        JOIN PIC p ON da.PIC = p.id_pic
        JOIN Koordinator k ON da.Koordinator = k.id_koordinator
        WHERE $whereSQL
        ORDER BY da.id_aduan DESC
        LIMIT $limit OFFSET $offset";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$aduan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Excel export
if (isset($_GET['export'])) {
    // Create a new Spreadsheet object
    $spreadsheet = new Spreadsheet();

    // Fetch summary data
    $summarySql = "SELECT 
                       COUNT(*) AS total_outstanding, 
                       SUM(CASE WHEN da.Status = 1 THEN 1 ELSE 0 END) AS outstanding,
                       SUM(CASE WHEN da.Status = 2 THEN 1 ELSE 0 END) AS in_progress,
                       SUM(CASE WHEN da.Status = 3 THEN 1 ELSE 0 END) AS pending
                   FROM daftar_aduan da
                   WHERE DATE(da.tanggal_aduan) BETWEEN ? AND ?";
    $summaryStmt = $pdo->prepare($summarySql);
    $summaryStmt->execute([$startDate, $endDate]);
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

    // Add data for each status
    $statusLabels = ['Complete', 'In Progress', 'Pending'];
    $statusIds = [1, 2, 3];
    $colors = ['FFFF00', '00FF00', 'FF0000']; // Yellow, Green, Red

    foreach ($statusIds as $index => $statusId) {
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $statusLabels[$index]);
        $spreadsheet->addSheet($sheet);

        // Set title and summary header
        $sheet->setCellValue('A1', "{$statusLabels[$index]} Tickets");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1:N1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set summary data
        $sheet->setCellValue('A3', 'Summary Case to Follow Up');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
        $sheet->mergeCells('A3:N3');
        $sheet->getStyle('A3:N3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A4', 'Total Outstanding:');
        $sheet->setCellValue('B4', $summary['total_outstanding']);
        $sheet->setCellValue('A5', 'Outstanding:');
        $sheet->setCellValue('B5', $summary['outstanding']);
        $sheet->setCellValue('A6', 'In Progress:');
        $sheet->setCellValue('B6', $summary['in_progress']);
        $sheet->setCellValue('A7', 'Pending:');
        $sheet->setCellValue('B7', $summary['pending']);

        $sheet->getStyle('A4:B7')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'F0F8FF'],
            ],
        ]);

        // Add section title
        $sheet->setCellValue('A9', 'Datar Tiket');

        // Add filters
        $sheet->setCellValue('A10', 'Periode:');
        $sheet->setCellValue('B10', $startDate . ' to ' . $endDate);

        // Add table header
        $sheet->setCellValue('A12', 'No');
        $sheet->setCellValue('B12', 'Artifact Id');
        $sheet->setCellValue('C12', 'Tanggal Aduan');
        $sheet->setCellValue('D12', 'Nama Pengadu');
        $sheet->setCellValue('E12', 'Title Aduan');
        $sheet->setCellValue('F12', 'Ext');
        $sheet->setCellValue('G12', 'Tempat Aduan');
        $sheet->setCellValue('H12', 'PIC');
        $sheet->setCellValue('I12', 'Koordinator');
        $sheet->setCellValue('J12', 'Status');
        $sheet->setCellValue('K12', 'Umur Aduan');
        $sheet->setCellValue('L12', 'Keterangan');
        $sheet->setCellValue('M12', 'Hasil Konfrimasi Teknis');
        $sheet->setCellValue('N12', 'Teknisi Penindaklanjut Aduan');
        $sheet->getStyle('A12:N12')->getFont()->setBold(true);
        $sheet->getStyle('A12:N12')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D3D3D3'); // Light gray
        $sheet->getStyle('A12:N12')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $dataSql = "SELECT * FROM daftar_aduan da WHERE da.Status = ? AND DATE(da.tanggal_aduan) BETWEEN ? AND ?";
        $dataStmt = $pdo->prepare($dataSql);
        $dataStmt->execute([$statusId, $startDate, $endDate]);
        $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        $row = 13;
        $num = 1;
        foreach ($data as $datum) {
            $sheet->setCellValue('A' . $row, $num++); // Nomor urut
            $sheet->setCellValue('B' . $row, $datum['artifact_id']);
            $sheet->setCellValue('C' . $row, $datum['tanggal_aduan']);
            $sheet->setCellValue('D' . $row, $datum['nama_pengadu']);
            $sheet->setCellValue('E' . $row, $datum['title_aduan']);
            $sheet->setCellValue('F' . $row, $datum['ext']);
            $sheet->setCellValue('G' . $row, $datum['tempat_aduan']);
            $sheet->setCellValue('H' . $row, $datum['PIC']);
            $sheet->setCellValue('I' . $row, $datum['Koordinator']);
            $sheet->setCellValue('J' . $row, $datum['Status']);
            if ($datum['Status'] == 2 || $datum['Status'] == 3) {
                $sheet->setCellValue('K' . $row, 'belum selesai');
            } else {
                $sheet->setCellValue('K' . $row, $datum['Umur_aduan']);
            }
            $sheet->setCellValue('L' . $row, $datum['Keterangan']);
            $sheet->setCellValue('M' . $row, $datum['Hasil_konfrimasi_teknisi']);
            $sheet->setCellValue('N' . $row, $datum['Teknisi_penindaklanjut_aduan']);

            // Align columns
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row . ':N' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . $row . ':N' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $row++;
        }
        $row += 2; // Add some spacing before the next status
    }

    // Remove default sheet created by PhpSpreadsheet
    $spreadsheet->removeSheetByIndex(0);

    // Auto-size columns for all sheets
    foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
        foreach (range('A', 'N') as $columnID) {
            $worksheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    // Align cells to the top for all sheets
    foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
        $worksheet->getStyle('A1:N' . $worksheet->getHighestRow())->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
    }

    // Generate dynamic filename
    $filename = sprintf('report_periode_%s_to_%s.xlsx', $startDate, $endDate);

    // Set headers to force download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Cache-Control: max-age=0');

    // Save the spreadsheet to output
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

$whereClauses = ["da.Status = 1"];
$params = [];

if ($searchTerm) {
    $whereClauses[] = "(da.artifact_id LIKE ? OR da.nama_pengadu LIKE ? OR da.title_aduan LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

// Existing date filters
if ($currentDate) {
    $whereClauses[] = "DATE(da.tanggal_aduan) = ?";
    $params[] = $currentDate;
} elseif ($startDate && $endDate) {
    $whereClauses[] = "DATE(da.tanggal_aduan) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
}

$whereSQL = implode(' AND ', $whereClauses);

// Fetch the data based on updated where clause
$sql = "SELECT 
            da.id_aduan,
            da.artifact_id,
            da.tanggal_aduan,
            da.nama_pengadu,
            da.title_aduan,
            da.ext,
            da.tempat_aduan,
            da.PIC,
            da.Koordinator,
            da.Status,
            da.Umur_aduan,  
            da.Keterangan,
            da.Hasil_konfrimasi_teknisi,
            da.Teknisi_penindaklanjut_aduan,
            s.description AS status_description
        FROM daftar_aduan da
        JOIN status s ON da.Status = s.id_status
        WHERE $whereSQL
        ORDER BY da.id_aduan DESC
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$aduan = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Status</title>
    <link rel="icon" href="logopusri">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="StatusComplete"></script>
    <link rel="stylesheet" href="headercss">
    <style>
        #suggestions {
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <main class="content-wrapper transition-all duration-300 ease-in-out ml-0 mt-10 lg:ml-64">
        <div>
            <button id="toggleSidebar" class="lg:hidden fixed top-4 mt-2 left-4 bg-blue-500 text-white px-4 py-2 rounded focus:outline-none">
                <i class="bi bi-chevron-double-right text-lg"></i>
            </button>
            <div id="loading" class="fixed inset-0 flex items-center justify-center bg-white">
                <div class="loading-spinner"></div>
            </div>
            <div class="p-8 mt-20" id="content">
                <!-- Search Form -->
                <form method="GET" action="" class="mb-6 bg-white border border-gray-200 rounded-lg p-4">
                    <input type="hidden" name="key" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                    <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                    <input type="text" id="search" name="search" placeholder="Search by Artifact ID, etc." class="form-input mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <!-- Suggestions -->
                    <div id="suggestions" class="mt-2 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg focus:outline-none mt-2 hover:bg-blue-600 transition-colors">
                        Search
                    </button>
                </form>

                <!-- Filter Form -->
                <form method="GET" action="" class="my-6 bg-white border border-gray-200 rounded-lg p-4">
                    <input type="hidden" name="key" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4">
                        <div class="flex-1">
                            <input type="date" name="start_date" class="form-input mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" value="<?php echo htmlspecialchars($startDate); ?>">
                        </div>
                        <div class="flex-1">
                            <input type="date" name="end_date" class="form-input mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" value="<?php echo htmlspecialchars($endDate); ?>">
                        </div>
                        <div class="flex-1">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg focus:outline-none hover:bg-blue-600 transition-colors">
                                Filter
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Export Form -->
                <form method="GET" action="" class="my-6 bg-white border border-gray-200 rounded-lg p-4">
                    <input type="hidden" name="key" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                    <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                    <button type="submit" name="export" class="bg-green-500 text-white px-4 py-2 rounded-lg focus:outline-none hover:bg-green-600 transition-colors">
                        Export to Excel
                    </button>
                </form>

                <!-- Data Table -->
                <div class="overflow-x-auto mt-6 bg-white border border-gray-200 rounded-lg shadow-lg">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="border-b-2 border-gray-300">
                                <th class="py-2 px-4 bg-gray-100">Artifact Id</th>
                                <th class="py-2 px-4 bg-gray-100">Tanggal Aduan</th>
                                <th class="py-2 px-4 bg-gray-100">Nama Pengadu</th>
                                <th class="py-2 px-4 bg-gray-100">Title Aduan</th>
                                <th class="py-2 px-4 bg-gray-100">Tempat Aduan</th>
                                <th class="py-2 px-4 bg-gray-100">Status</th>
                                <th class="py-2 px-4 bg-gray-100">Keterangan</th>
                                <th class="py-2 px-4 bg-gray-100">Umur Aduan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aduan as $row) : ?>
                                <tr class="hover:bg-gray-200 bg-gray-100 border-b-2 border-gray-300 text-center">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['artifact_id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['tanggal_aduan']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['nama_pengadu']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['title_aduan']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['tempat_aduan']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['status_description']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['Keterangan']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['Umur_aduan']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    <?php if ($totalPages > 1) : ?>
                        <nav class="block">
                            <ul class="flex pl-0 rounded list-none flex-wrap">
                                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                    <li>
                                        <a href="?key=<?php echo htmlspecialchars($token); ?>&page=<?php echo $i; ?>&start_date=<?php echo htmlspecialchars($startDate); ?>&end_date=<?php echo htmlspecialchars($endDate); ?><?php echo $currentDate ? '&current_date=1' : ''; ?>" class="first:ml-0 text-xs font-semibold flex w-8 h-8 mx-1 justify-center items-center rounded-full <?php echo $page == $i ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <script src="headerjs"></script>
</body>

</html>