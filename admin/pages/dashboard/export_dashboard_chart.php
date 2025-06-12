<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/session.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    die('Không có quyền truy cập');
}

// Kiểm tra có PhpSpreadsheet không
$composerAutoload = __DIR__ . '/../../../vendor/autoload.php';
if (!file_exists($composerAutoload)) {
    // Fallback về file export đơn giản
    header('Location: export_dashboard_excel.php?' . $_SERVER['QUERY_STRING']);
    exit();
}

require_once $composerAutoload;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

// Thiết lập timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Xử lý tuần được chọn
$week_input = isset($_GET['week']) ? $_GET['week'] : date('Y-W');
if ($week_input) {
    $year = substr($week_input, 0, 4);
    $week_num = substr($week_input, 6);
    $selected_date = date('Y-m-d', strtotime($year . 'W' . $week_num . '1'));
} else {
    $selected_date = date('Y-m-d');
}

$start_of_week = date('Y-m-d 00:00:00', strtotime('monday this week', strtotime($selected_date)));
$end_of_week = date('Y-m-d 23:59:59', strtotime('sunday this week', strtotime($selected_date)));

// Lấy dữ liệu (tương tự file export_dashboard_excel.php)
try {
    // 1. Thống kê tổng quan
    $orders_this_week_query = "SELECT COUNT(*) as count FROM orders WHERE order_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($orders_this_week_query);
    $stmt->bind_param("ss", $start_of_week, $end_of_week);
    $stmt->execute();
    $orders_this_week = $stmt->get_result()->fetch_assoc()['count'];

    // 2. Dữ liệu theo ngày cho biểu đồ
    $daily_stats = [];
    for ($i = 0; $i < 7; $i++) {
        $day_start = date('Y-m-d 00:00:00', strtotime($start_of_week . " +{$i} day"));
        $day_end = date('Y-m-d 23:59:59', strtotime($start_of_week . " +{$i} day"));
        
        $daily_query = "
            SELECT 
                COUNT(o.order_id) as orders_count,
                COALESCE(SUM(o.final_amount), 0) as revenue
            FROM orders o 
            WHERE o.order_date BETWEEN ? AND ?
        ";
        $stmt = $conn->prepare($daily_query);
        $stmt->bind_param("ss", $day_start, $day_end);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $daily_stats[] = [
            'day_vi' => [
                'Monday' => 'Thứ 2',
                'Tuesday' => 'Thứ 3', 
                'Wednesday' => 'Thứ 4',
                'Thursday' => 'Thứ 5',
                'Friday' => 'Thứ 6',
                'Saturday' => 'Thứ 7',
                'Sunday' => 'Chủ nhật'
            ][date('l', strtotime($day_start))],
            'orders' => $result['orders_count'],
            'revenue' => $result['revenue']
        ];
    }

} catch (Exception $e) {
    die('Lỗi truy vấn database: ' . $e->getMessage());
}

// Tạo Spreadsheet
$spreadsheet = new Spreadsheet();
$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Dashboard Report');

// Header styling
$headerStyle = [
    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF412D3B']],
    'alignment' => ['horizontal' => 'center']
];

// Tiêu đề
$worksheet->setCellValue('A1', 'DASHBOARD REPORT - TUẦN ' . date('d/m/Y', strtotime($start_of_week)) . ' - ' . date('d/m/Y', strtotime($end_of_week)));
$worksheet->mergeCells('A1:D1');
$worksheet->getStyle('A1')->applyFromArray($headerStyle);

// Dữ liệu cho biểu đồ
$worksheet->setCellValue('A3', 'Ngày');
$worksheet->setCellValue('B3', 'Số đơn hàng');
$worksheet->setCellValue('C3', 'Doanh thu (triệu đồng)');

$row = 4;
foreach ($daily_stats as $day) {
    $worksheet->setCellValue('A' . $row, $day['day_vi']);
    $worksheet->setCellValue('B' . $row, $day['orders']);
    $worksheet->setCellValue('C' . $row, round($day['revenue'] / 1000000, 2));
    $row++;
}

// Tạo biểu đồ
$dataSeriesLabels = [
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Dashboard Report!$B$3', null, 1),
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Dashboard Report!$C$3', null, 1)
];

$xAxisTickValues = [
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Dashboard Report!$A$4:$A$10', null, 7)
];

$dataSeriesValues = [
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Dashboard Report!$B$4:$B$10', null, 7),
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Dashboard Report!$C$4:$C$10', null, 7)
];

$series = new DataSeries(
    DataSeries::TYPE_LINECHART,
    DataSeries::GROUPING_STANDARD,
    range(0, count($dataSeriesValues) - 1),
    $dataSeriesLabels,
    $xAxisTickValues,
    $dataSeriesValues
);

$plotArea = new PlotArea(null, [$series]);
$legend = new Legend(Legend::POSITION_TOPRIGHT, null, false);
$title = new Title('Thống kê đơn hàng và doanh thu theo ngày');
$chart = new Chart('chart1', $title, $legend, $plotArea);

$chart->setTopLeftPosition('E3');
$chart->setBottomRightPosition('L15');

$worksheet->addChart($chart);

// Auto-size columns
foreach (range('A', 'D') as $col) {
    $worksheet->getColumnDimension($col)->setAutoSize(true);
}

// Output
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Dashboard_Chart_Report_' . date('Y-m-d', strtotime($selected_date)) . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);
$writer->save('php://output');
?> 