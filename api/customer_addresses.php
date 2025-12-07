<?php


// 1. Include file cấu hình chung (Để lấy thông tin DB chuẩn từ config.php)
require_once __DIR__ . '/../includes/config.php'; 

// Set header JSON
header('Content-Type: application/json; charset=utf-8');

// Start session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    // 2. Sử dụng hàm getConnection() từ config.php thay vì tự tạo PDO mới
    $conn = getConnection();

    // Helper function
    if (!function_exists('sanitize')) {
        function sanitize($data) {
            if ($data === null) return '';
            if (!is_string($data)) $data = (string)$data;
            return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
        }
    }

    switch ($action) {
        // Lấy danh sách địa chỉ
        case 'list':
            $stmt = $conn->prepare("SELECT * FROM customer_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
            $stmt->execute([$userId]);
            $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'addresses' => $addresses]);
            break;

        // Thêm địa chỉ
        case 'add':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $name = sanitize($data['name'] ?? '');
            $phone = sanitize($data['phone'] ?? '');
            $address = sanitize($data['address'] ?? '');
            $ward = sanitize($data['ward'] ?? '');
            $district = sanitize($data['district'] ?? '');
            $city = sanitize($data['city'] ?? '');
            $note = sanitize($data['note'] ?? '');
            $isDefault = $data['is_default'] ?? 0;

            if (empty($name) || empty($phone) || empty($address)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
                exit;
            }

            // Nếu set làm mặc định, bỏ mặc định các địa chỉ khác
            if ($isDefault) {
                $conn->prepare("UPDATE customer_addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
            }

            $stmt = $conn->prepare("INSERT INTO customer_addresses (user_id, name, phone, address, ward, district, city, note, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $name, $phone, $address, $ward, $district, $city, $note, $isDefault ? 1 : 0]);

            echo json_encode([
                'success' => true,
                'message' => 'Thêm địa chỉ thành công',
                'id' => $conn->lastInsertId()
            ]);
            break;

        // Cập nhật địa chỉ
        case 'update':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);
            $name = sanitize($data['name'] ?? '');
            $phone = sanitize($data['phone'] ?? '');
            $address = sanitize($data['address'] ?? '');
            $ward = sanitize($data['ward'] ?? '');
            $district = sanitize($data['district'] ?? '');
            $city = sanitize($data['city'] ?? '');
            $note = sanitize($data['note'] ?? '');
            $isDefault = $data['is_default'] ?? 0;

            // Kiểm tra quyền sở hữu
            $checkStmt = $conn->prepare("SELECT user_id FROM customer_addresses WHERE id = ?");
            $checkStmt->execute([$id]);
            $existingAddress = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existingAddress || $existingAddress['user_id'] != $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
                exit;
            }

            // Nếu set làm mặc định, bỏ mặc định các địa chỉ khác
            if ($isDefault) {
                $conn->prepare("UPDATE customer_addresses SET is_default = 0 WHERE user_id = ? AND id != ?")->execute([$userId, $id]);
            }

            $stmt = $conn->prepare("UPDATE customer_addresses SET name = ?, phone = ?, address = ?, ward = ?, district = ?, city = ?, note = ?, is_default = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$name, $phone, $address, $ward, $district, $city, $note, $isDefault ? 1 : 0, $id, $userId]);

            echo json_encode(['success' => true, 'message' => 'Cập nhật địa chỉ thành công']);
            break;

        // Xóa địa chỉ
        case 'delete':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);

            // Kiểm tra quyền sở hữu
            $checkStmt = $conn->prepare("SELECT user_id FROM customer_addresses WHERE id = ?");
            $checkStmt->execute([$id]);
            $existingAddress = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existingAddress || $existingAddress['user_id'] != $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
                exit;
            }

            $stmt = $conn->prepare("DELETE FROM customer_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);

            echo json_encode(['success' => true, 'message' => 'Xóa địa chỉ thành công']);
            break;

        // Đặt làm mặc định
        case 'set_default':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);

            // Kiểm tra quyền sở hữu
            $checkStmt = $conn->prepare("SELECT user_id FROM customer_addresses WHERE id = ?");
            $checkStmt->execute([$id]);
            $existingAddress = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existingAddress || $existingAddress['user_id'] != $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
                exit;
            }

            // Bỏ mặc định các địa chỉ khác
            $conn->prepare("UPDATE customer_addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
            // Đặt làm mặc định
            $conn->prepare("UPDATE customer_addresses SET is_default = 1 WHERE id = ? AND user_id = ?")->execute([$id, $userId]);

            echo json_encode(['success' => true, 'message' => 'Đặt làm địa chỉ mặc định thành công']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>