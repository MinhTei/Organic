<?php
// includes/import_helper.php - Hàm hỗ trợ import dữ liệu từ Excel/CSV

// Require Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Import sản phẩm từ file Excel hoặc CSV
 * @param string $filePath Đường dẫn tới file
 * @param int $categoryId ID danh mục (không bắt buộc)
 * @return array ['success' => int, 'errors' => array, 'warnings' => array]
 */
function importProductsFromExcel($filePath, $categoryId = null)
{
    if (!file_exists($filePath)) {
        return [
            'success' => 0,
            'errors' => ['File không tồn tại: ' . $filePath],
            'warnings' => []
        ];
    }

    // Xác định loại file
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
        return [
            'success' => 0,
            'errors' => ['File phải là Excel (.xlsx, .xls) hoặc CSV'],
            'warnings' => []
        ];
    }

    try {
        // Xử lý CSV
        if ($ext === 'csv') {
            $rows = [];
            if (($handle = fopen($filePath, 'r')) !== false) {
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
            return processProductRows($rows, $categoryId);
        }

        // Xử lý Excel (.xlsx, .xls) bằng PhpSpreadsheet
        if (in_array($ext, ['xlsx', 'xls'])) {
            if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
                return [
                    'success' => 0,
                    'errors' => ['Thư viện PhpSpreadsheet không được tìm thấy. Hãy chạy: composer require phpoffice/phpspreadsheet'],
                    'warnings' => []
                ];
            }

            try {
                /** @var \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet */
                if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                    throw new \Exception('PhpSpreadsheet IOFactory not found');
                }
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                return processProductRows($rows, $categoryId);
            } catch (\Throwable $e) {
                if (class_exists('\\PhpOffice\\PhpSpreadsheet\\Exception') && $e instanceof \PhpOffice\PhpSpreadsheet\Exception) {
                    $msg = 'Lỗi đọc file Excel: ' . $e->getMessage();
                } else {
                    $msg = 'Lỗi: ' . $e->getMessage();
                }
                return [
                    'success' => 0,
                    'errors' => [$msg],
                    'warnings' => []
                ];
            }
        }
    } catch (\Exception $e) {
        return [
            'success' => 0,
            'errors' => ['Lỗi không xác định: ' . $e->getMessage()],
            'warnings' => []
        ];
    }

    return [
        'success' => 0,
        'errors' => ['File phải là Excel hoặc CSV'],
        'warnings' => []
    ];
}

/**
 * Xử lý các hàng dữ liệu sản phẩm
 * @param array $rows Mảng các hàng từ file
 * @param int $categoryId ID danh mục
 * @return array
 */
function processProductRows($rows, $categoryId = null)
{
    if (empty($rows)) {
        return [
            'success' => 0,
            'errors' => ['File rỗng hoặc không có dữ liệu'],
            'warnings' => []
        ];
    }

    $conn = getConnection();
    $success = 0;
    $errors = [];
    $warnings = [];

    // Lấy hàng header: bỏ qua các hàng trống phía đầu file nếu có
    $headers = null;
    while (!empty($rows)) {
        $firstRow = $rows[0];
        $hasNonEmpty = false;
        foreach ($firstRow as $cell) {
            if ($cell !== null && trim((string)$cell) !== '') {
                $hasNonEmpty = true;
                break;
            }
        }
        if ($hasNonEmpty) {
            $headers = array_shift($rows);
            break;
        }
        // remove this empty row and continue
        array_shift($rows);
    }

    if ($headers === null) {
        return [
            'success' => 0,
            'errors' => ['File rỗng hoặc không tìm thấy header hợp lệ'],
            'warnings' => []
        ];
    }

    // Ánh xạ các cột
    $headerMap = mapHeaderColumns($headers);
    if (!$headerMap['valid']) {
        return [
            'success' => 0,
            'errors' => ['File không có cột yêu cầu: ' . implode(', ', $headerMap['missing'])],
            'warnings' => []
        ];
    }

    // Bắt đầu transaction
    try {
        $conn->beginTransaction();

        foreach ($rows as $rowIndex => $row) {
            $lineNum = $rowIndex + 2; // +2 vì 1 header row + array index bắt đầu từ 0

            // Bỏ qua hàng trống
            if (empty(array_filter($row))) {
                continue;
            }

            // Lấy dữ liệu từ hàng (matches product_add.php behavior)
            $name = sanitize((string)($row[$headerMap['name']] ?? ''));
            $category = (string)($row[$headerMap['category']] ?? '');
            $price = $row[$headerMap['price']] ?? '';
            $sale_price = $row[$headerMap['sale_price']] ?? '';
            $unit = sanitize((string)($row[$headerMap['unit']] ?? 'kg'));
            $stock = isset($headerMap['stock']) ? (int)$row[$headerMap['stock']] : 0;
            $description = sanitize((string)($row[$headerMap['description']] ?? ''));
            $is_organic = isset($headerMap['is_organic']) ? (strtolower(trim((string)$row[$headerMap['is_organic']] ?? 'yes')) === 'yes' ? 1 : 0) : 1;
            $is_new = isset($headerMap['is_new']) ? (strtolower(trim((string)$row[$headerMap['is_new']] ?? 'no')) === 'yes' ? 1 : 0) : 0;
            $imagePath = ''; // import doesn't handle image uploads; leave empty
            $is_featured = 0; // default to not featured

            // Kiểm tra dữ liệu bắt buộc
            if (empty($name)) {
                $errors[] = "Hàng $lineNum: Tên sản phẩm không được để trống";
                continue;
            }

            if (empty($price) || !is_numeric($price)) {
                $errors[] = "Hàng $lineNum: Giá không hợp lệ (phải là số)";
                continue;
            }

            // Xử lý danh mục
            $catId = $categoryId;
            if (!empty($category)) {
                $catId = getCategoryIdByName($category);
                if (!$catId) {
                    $warnings[] = "Hàng $lineNum: Danh mục '$category' không tìm thấy, sẽ để trống";
                    $catId = null;
                }
            }

            // Tạo hoặc dùng slug nếu có trong file
            if (isset($headerMap['slug'])) {
                $slug = sanitize((string)($row[$headerMap['slug']] ?? ''));
                if (empty($slug)) {
                    $slug = slugify($name);
                }
            } else {
                $slug = slugify($name);
            }

            // Kiểm tra slug đã tồn tại chưa
            $existingSlug = $conn->prepare("SELECT id FROM products WHERE slug = ?");
            $existingSlug->execute([$slug]);
            if ($existingSlug->fetch()) {
                $warnings[] = "Hàng $lineNum: Sản phẩm '$name' đã tồn tại (slug trùng)";
                continue;
            }

            // Chuẩn hóa giá
            $price = (float)$price;
            $sale_price = !empty($sale_price) && is_numeric($sale_price) ? (float)$sale_price : null;
            $stock = (int)$stock;

            // Insert vào database (match admin/product_add.php columns)
            try {
                $sql = "INSERT INTO products (category_id, name, slug, description, price, sale_price, unit, image, stock, is_organic, is_new, is_featured) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    $catId,
                    $name,
                    $slug,
                    $description,
                    $price,
                    $sale_price,
                    $unit,
                    $imagePath,
                    $stock,
                    $is_organic,
                    $is_new,
                    $is_featured
                ]);

                if ($result) {
                    $success++;
                } else {
                    $errors[] = "Hàng $lineNum: Lỗi khi thêm vào database";
                }
            } catch (\Exception $e) {
                $errors[] = "Hàng $lineNum: " . $e->getMessage();
            }
        }

        $conn->commit();
    } catch (\Exception $e) {
        $conn->rollBack();
        return [
            'success' => 0,
            'errors' => ['Lỗi import: ' . $e->getMessage()],
            'warnings' => $warnings
        ];
    }

    return [
        'success' => $success,
        'errors' => $errors,
        'warnings' => $warnings
    ];
}

/**
 * Ánh xạ các cột từ header
 * @param array $headers Mảng header
 * @return array ['valid' => bool, 'missing' => array, ...]
 */
function mapHeaderColumns($headers)
{
    // Normalize headers: trim, remove UTF-8 BOM (Byte Order Mark) and lowercase
    $headerLower = [];
    foreach ($headers as $h) {
        // Cast to string first to avoid passing null to trim() (deprecated in PHP 8.1+)
        $h = trim((string)$h);
        // Remove UTF-8 BOM if present (three-byte sequence \xEF\xBB\xBF)
        if (strncmp($h, "\xEF\xBB\xBF", 3) === 0) {
            $h = substr($h, 3);
        }
        $hLower = function_exists('mb_strtolower') ? mb_strtolower($h) : strtolower($h);
        $headerLower[] = $hLower;
    }

    // Các cột bắt buộc
    $requiredColumns = ['name', 'price'];
    $columnMap = [];

    // Tìm các cột bắt buộc
    foreach ($requiredColumns as $col) {
        $index = array_search($col, $headerLower);
        if ($index === false) {
            // Thử tìm với các biến thể
            $variations = getColumnVariations($col);
            $found = false;
            foreach ($variations as $var) {
                $index = array_search($var, $headerLower);
                if ($index !== false) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return [
                    'valid' => false,
                    'missing' => [$col]
                ];
            }
        }
        $columnMap[$col] = $index;
    }

    // Tìm các cột không bắt buộc
    $optionalColumns = ['category', 'description', 'unit', 'stock', 'sale_price', 'is_organic', 'is_new', 'slug'];
    foreach ($optionalColumns as $col) {
        $index = array_search($col, $headerLower);
        if ($index === false) {
            $variations = getColumnVariations($col);
            foreach ($variations as $var) {
                $index = array_search($var, $headerLower);
                if ($index !== false) break;
            }
        }
        if ($index !== false) {
            $columnMap[$col] = $index;
        }
    }

    $columnMap['valid'] = true;
    return $columnMap;
}

/**
 * Lấy các biến thể tên cột (hỗ trợ tiếng Việt và tiếng Anh)
 * @param string $column
 * @return array
 */
function getColumnVariations($column)
{
    $variations = [
        'name' => ['tên sản phẩm', 'tên', 'product name', 'product', 'name'],
        'price' => ['giá', 'giá bán', 'price', 'unit price', 'đơn giá'],
        'sale_price' => ['giá giảm', 'giá khuyến mãi', 'sale price', 'discount price', 'giá sale'],
        'category' => ['danh mục', 'category', 'cat', 'loại'],
        'description' => ['mô tả', 'description', 'desc', 'miêu tả'],
        'unit' => ['đơn vị', 'unit', 'đơn vị tính'],
        'stock' => ['tồn kho', 'stock', 'qty', 'số lượng', 'kho'],
        'is_organic' => ['hữu cơ', 'organic', 'có hữu cơ'],
        'is_new' => ['mới', 'is new', 'new', 'sản phẩm mới'],
        'slug' => ['slug']
    ];

    return $variations[$column] ?? [];
}

/**
 * Tìm ID danh mục theo tên
 * @param string $categoryName
 * @return int|false
 */
function getCategoryIdByName($categoryName)
{
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? OR slug = ? LIMIT 1");
    $stmt->execute([$categoryName, slugify($categoryName)]);
    $result = $stmt->fetch();
    return $result ? $result['id'] : false;
}

/**
 * Chuyển đổi chuỗi thành slug
 * @param string $str
 * @return string
 */
function slugify($str)
{
    // 1. Chuyển sang lowercase
    $str = mb_strtolower(trim($str), 'UTF-8');

    // 2. Loại bỏ dấu tiếng Việt
    $unicode = [
        'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
        'd' => 'đ',
        'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'i' => 'í|ì|ỉ|ĩ|ị',
        'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'y' => 'ý|ỳ|ỷ|ỹ|ỵ'
    ];
    foreach ($unicode as $nonUnicode => $uni) {
        $str = preg_replace("/($uni)/i", $nonUnicode, $str);
    }

    // 3. Thay tất cả ký tự không phải a-z, 0-9 bằng dấu "-"
    $str = preg_replace('/[^a-z0-9]+/i', '-', $str);

    // 4. Loại bỏ "-" thừa đầu cuối
    $str = trim($str, '-');

    return $str;
}
