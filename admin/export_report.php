<?php
/**
 * ============================================================================
 * XUẤT BÁO CÁO DOANH THU (PDF / CSV-EXCEL)
 * ============================================================================
 * Chức năng: Xuất báo cáo doanh thu, sản phẩm bán chạy, trạng thái đơn hàng
 * Hỗ trợ: Excel (CSV format), PDF (HTML print hoặc thư viện PDF)
 * 
 * GET Parameters:
 *   - format: 'excel' hoặc 'pdf' (mặc định: 'excel')
 *   - start: Ngày bắt đầu (YYYY-MM-DD)
 *   - end: Ngày kết thúc (YYYY-MM-DD)
 * ============================================================================
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die('Unauthorized');
}

// Lấy tham số
$format = $_GET['format'] ?? 'excel';
$startDate = $_GET['start'] ?? date('Y-m-01');
$endDate = $_GET['end'] ?? date('Y-m-d');
$conn = getConnection();

$statusLabels = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'processing' => 'Đang xử lý',
    'shipping' => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
    'refunded' => 'Đã hoàn tiền'
];

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function getReportData($conn, $startDate, $endDate) {
    $start = $startDate . ' 00:00:00';
    $end = $endDate . ' 23:59:59';

    // Tổng hợp
    $total = $conn->prepare("
        SELECT 
            COUNT(*) as total_orders,
            SUM(total_amount) as total_revenue,
            AVG(total_amount) as avg_order_value,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
        FROM orders
        WHERE created_at BETWEEN ? AND ?
    ");
    $total->execute([$start, $end]);
    $totals = $total->fetch(PDO::FETCH_ASSOC);

    // Doanh thu theo ngày
    $revenue = $conn->prepare("
        SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue
        FROM orders
        WHERE created_at BETWEEN ? AND ? AND status != 'cancelled'
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $revenue->execute([$start, $end]);
    $revenueData = $revenue->fetchAll(PDO::FETCH_ASSOC);

    // Top 15 sản phẩm
    $topProd = $conn->prepare("
        SELECT 
            p.name,
            SUM(oi.quantity) as total_sold,
            SUM(oi.quantity * oi.unit_price) as total_revenue,
            AVG(oi.unit_price) as avg_price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.created_at BETWEEN ? AND ? AND o.status != 'cancelled'
        GROUP BY p.id
        ORDER BY total_revenue DESC
        LIMIT 15
    ");
    $topProd->execute([$start, $end]);
    $topProducts = $topProd->fetchAll(PDO::FETCH_ASSOC);

    // Trạng thái đơn hàng
    $status = $conn->prepare("
        SELECT status, COUNT(*) as count
        FROM orders
        WHERE created_at BETWEEN ? AND ?
        GROUP BY status
        ORDER BY count DESC
    ");
    $status->execute([$start, $end]);
    $orderStatus = $status->fetchAll(PDO::FETCH_ASSOC);

    return compact('totals', 'revenueData', 'topProducts', 'orderStatus');
}

// ============================================================================
// EXPORT FUNCTIONS
// ============================================================================

function exportToExcel($data, $startDate, $endDate, $statusLabels) {
    $totals = $data['totals'];
    $revenueData = $data['revenueData'];
    $topProducts = $data['topProducts'];
    $orderStatus = $data['orderStatus'];
    
    $filename = 'bao_cao_doanh_thu_' . $startDate . '_' . $endDate . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
    
    // Tiêu đề
    fputcsv($output, []);
    fputcsv($output, ['BÁO CÁO DOANH THU THÁNG']);
    fputcsv($output, ['Từ ' . formatDate($startDate) . ' đến ' . formatDate($endDate)]);
    fputcsv($output, []);
    
    // TỔNG HỢP
    fputcsv($output, ['TỔNG HỢP CHỈ TIÊU']);
    fputcsv($output, ['STT', 'Chỉ tiêu', 'Giá trị']);
    fputcsv($output, [1, 'Tổng số đơn hàng', $totals['total_orders'] ?? 0]);
    fputcsv($output, [2, 'Tổng doanh thu', number_format($totals['total_revenue'] ?? 0, 0) . 'đ']);
    fputcsv($output, [3, 'Doanh thu TB/đơn', number_format($totals['avg_order_value'] ?? 0, 0) . 'đ']);
    fputcsv($output, [4, 'Đơn hàng đã giao', $totals['delivered_orders'] ?? 0]);
    fputcsv($output, [5, 'Đơn hàng đã hủy', $totals['cancelled_orders'] ?? 0]);
    fputcsv($output, []);
    
    // DOANH THU THEO NGÀY
    fputcsv($output, ['DOANH THU THEO NGÀY']);
    fputcsv($output, ['STT', 'Ngày', 'Số đơn', 'Doanh thu']);
    $stt = 1;
    foreach ($revenueData as $row) {
        fputcsv($output, [$stt++, formatDate($row['date']), $row['orders'], number_format($row['revenue'] ?? 0, 0) . 'đ']);
    }
    fputcsv($output, []);
    
    // TOP 15 SẢN PHẨM
    fputcsv($output, ['TOP 15 SẢN PHẨM BÁN CHẠY']);
    fputcsv($output, ['STT', 'Sản phẩm', 'Số lượng', 'Doanh thu', 'Giá BQ']);
    $stt = 1;
    foreach ($topProducts as $row) {
        fputcsv($output, [$stt++, $row['name'], $row['total_sold'], number_format($row['total_revenue'] ?? 0, 0) . 'đ', number_format($row['avg_price'] ?? 0, 0) . 'đ']);
    }
    fputcsv($output, []);
    
    // TRẠNG THÁI
    fputcsv($output, ['TRẠNG THÁI ĐƠN HÀNG']);
    fputcsv($output, ['STT', 'Trạng thái', 'Số lượng']);
    $stt = 1;
    foreach ($orderStatus as $row) {
        fputcsv($output, [$stt++, $statusLabels[$row['status']] ?? $row['status'], $row['count']]);
    }
    
    fclose($output);
    exit;
}

function getPdfHtml($data, $startDate, $endDate, $statusLabels) {
    $totals = $data['totals'];
    $revenueData = $data['revenueData'];
    $topProducts = $data['topProducts'];
    $orderStatus = $data['orderStatus'];
    
    $css = <<<CSS
        * { margin: 0; padding: 0; }
        html, body { margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; color: #000; font-size: 7.5pt; line-height: 1.1; padding: 6px; }
        h1 { text-align: center; font-size: 12pt; margin: 0 0 2px 0; font-weight: bold; }
        .date-range { text-align: center; margin: 0 0 4px 0; font-size: 7.5pt; }
        h2 { font-size: 8pt; margin: 3px 0 2px 0; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 1px; }
        .summary { display: grid; grid-template-columns: 140px 1fr; padding: 0; font-size: 7.5pt; column-gap: 5px; }
        .summary-label { font-weight: bold; }
        .summary-value { font-weight: bold; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin: 1px 0; font-size: 7pt; }
        th { background-color: #d3d3d3; border: 0.5px solid #000; padding: 1px 1.5px; text-align: left; font-weight: bold; }
        td { border: 0.5px solid #000; padding: 1px 1.5px; overflow: hidden; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:nth-child(odd) { background-color: #fff; }
        .text-right { text-align: right; }
        .footer { text-align: center; color: #999; font-size: 6pt; margin-top: 2px; padding-top: 1px; border-top: 0.5px solid #000; }
        @media print {
            @page { margin: 5mm; }
            body { margin: 0; padding: 3px; }
        }
    CSS;
    
    $html = "<!DOCTYPE html>\n<html lang=\"vi\">\n<head>\n    <meta charset=\"UTF-8\">\n    <style>$css</style>\n</head>\n<body>\n";
    
    $html .= "    <h1>BÁO CÁO DOANH THU THÁNG</h1>\n";
    $html .= "    <div class=\"date-range\">Từ " . formatDate($startDate) . " đến " . formatDate($endDate) . "</div>\n\n";
    
    // Tổng hợp
    $html .= "    <h2>TỔNG HỢP CHỈ TIÊU</h2>\n";
    $html .= "    <div class=\"summary\"><span class=\"summary-label\">Tổng số đơn hàng:</span><span class=\"summary-value\">" . ($totals['total_orders'] ?? 0) . "</span></div>\n";
    $html .= "    <div class=\"summary\"><span class=\"summary-label\">Tổng doanh thu:</span><span class=\"summary-value\">" . number_format($totals['total_revenue'] ?? 0, 0) . "đ</span></div>\n";
    $html .= "    <div class=\"summary\"><span class=\"summary-label\">Doanh thu TB/đơn:</span><span class=\"summary-value\">" . number_format($totals['avg_order_value'] ?? 0, 0) . "đ</span></div>\n";
    $html .= "    <div class=\"summary\"><span class=\"summary-label\">Đơn hàng đã giao:</span><span class=\"summary-value\">" . ($totals['delivered_orders'] ?? 0) . "</span></div>\n";
    $html .= "    <div class=\"summary\"><span class=\"summary-label\">Đơn hàng đã hủy:</span><span class=\"summary-value\">" . ($totals['cancelled_orders'] ?? 0) . "</span></div>\n\n";
    
    // Doanh thu theo ngày
    $html .= "    <h2>DOANH THU THEO NGÀY</h2>\n    <table><thead><tr><th style=\"width:28%\">Ngày</th><th style=\"width:18%\" class=\"text-right\">Số đơn</th><th style=\"width:54%\" class=\"text-right\">Doanh thu</th></tr></thead><tbody>\n";
    foreach ($revenueData as $row) {
        $html .= "        <tr><td>" . formatDate($row['date']) . "</td><td class=\"text-right\">" . $row['orders'] . "</td><td class=\"text-right\">" . number_format($row['revenue'] ?? 0, 0) . "đ</td></tr>\n";
    }
    $html .= "    </tbody></table>\n\n";
    
    // Top sản phẩm
    $html .= "    <h2>TOP 15 SẢN PHẨM BÁN CHẠY</h2>\n    <table><thead><tr><th style=\"width:4%\">STT</th><th style=\"width:42%\">Sản phẩm</th><th style=\"width:10%\" class=\"text-right\">SL</th><th style=\"width:22%\" class=\"text-right\">Doanh thu</th><th style=\"width:22%\" class=\"text-right\">Giá BQ</th></tr></thead><tbody>\n";
    $stt = 1;
    foreach ($topProducts as $row) {
        $html .= "        <tr><td>" . $stt++ . "</td><td>" . htmlspecialchars(substr($row['name'], 0, 35)) . "</td><td class=\"text-right\">" . $row['total_sold'] . "</td><td class=\"text-right\">" . number_format($row['total_revenue'] ?? 0, 0) . "đ</td><td class=\"text-right\">" . number_format($row['avg_price'] ?? 0, 0) . "đ</td></tr>\n";
    }
    $html .= "    </tbody></table>\n\n";
    
    // Trạng thái
    $html .= "    <h2>TRẠNG THÁI ĐƠN HÀNG</h2>\n    <table><thead><tr><th style=\"width:68%\">Trạng thái</th><th style=\"width:32%\" class=\"text-right\">Số lượng</th></tr></thead><tbody>\n";
    foreach ($orderStatus as $row) {
        $html .= "        <tr><td>" . ($statusLabels[$row['status']] ?? $row['status']) . "</td><td class=\"text-right\">" . $row['count'] . "</td></tr>\n";
    }
    $html .= "    </tbody></table>\n\n";
    
    $html .= "    <div class=\"footer\"><p>Báo cáo được tạo lúc: " . date('d/m/Y H:i:s') . "</p></div>\n</body>\n</html>";
    
    return $html;
}

function generatePDF($html) {
    try {
        require_once '../vendor/autoload.php';
        
        /** @noinspection PhpUndefinedClassInspection */
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'margin_header' => 0,
            'margin_footer' => 0
        ]);
        $mpdf->SetDefaultFont('dejavusans');
        $mpdf->WriteHTML($html);
        return $mpdf->Output('', 'S');
    } catch (Exception $e) {
        error_log('mpdf error: ' . $e->getMessage());
        return false;
    }
}

function exportToPdf($data, $startDate, $endDate, $statusLabels) {
    $filename = 'bao_cao_doanh_thu_' . $startDate . '_' . $endDate . '.pdf';
    $html = getPdfHtml($data, $startDate, $endDate, $statusLabels);
    
    // Nếu có tham số download=1, xuất PDF
    if (isset($_GET['download']) && $_GET['download'] == '1') {
        $pdfContent = generatePDF($html);
        
        if (!$pdfContent) {
            http_response_code(500);
            die('Lỗi: Không thể tạo PDF. Vui lòng thử lại.');
        }
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit;
    }
    
    // Hiển thị preview HTML
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem trước báo cáo</title>
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .btn-group { margin-bottom: 20px; text-align: center; }
        .btn { padding: 10px 20px; margin: 0 5px; font-size: 14px; cursor: pointer; border: none; border-radius: 4px; text-decoration: none; display: inline-block; }
        .btn-download { background-color: #4CAF50; color: white; }
        .btn-download:hover { background-color: #45a049; }
        .btn-print { background-color: #2196F3; color: white; }
        .btn-print:hover { background-color: #0b7dda; }
        .preview { background: white; padding: 20px; border-radius: 4px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        .preview-content { margin-top: 20px; }
        @media print {
            .btn-group { display: none; }
            .preview { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="preview">
        <div class="btn-group">
            <a href="?format=pdf&start=' . $startDate . '&end=' . $endDate . '&download=1" class="btn btn-download">⬇️ Tải PDF</a>
            <button onclick="window.print()" class="btn btn-print">🖨️ In</button>
        </div>
        <div class="preview-content">' . $html . '</div>
    </div>
</body>
</html>';
    exit;
}

// ============================================================================
// MAIN EXECUTION
// ============================================================================

$data = getReportData($conn, $startDate, $endDate);

if ($format === 'excel') {
    exportToExcel($data, $startDate, $endDate, $statusLabels);
} elseif ($format === 'pdf') {
    exportToPdf($data, $startDate, $endDate, $statusLabels);
} else {
    http_response_code(400);
    die('Format không hợp lệ. Sử dụng: excel hoặc pdf');
}
?>


