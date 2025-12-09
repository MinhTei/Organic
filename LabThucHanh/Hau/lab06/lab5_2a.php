<?php
header("Content-Type: text/html; charset=UTF-8");

// URL muốn lấy
$url = "https://vnexpress.net/";   // Thay đổi tùy ý

// --- cURL tải trang ---
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_ENCODING => ""
]);

$html = curl_exec($ch);
curl_close($ch);

if (!$html) {
    die("❌ Không thể tải trang: $url");
}

echo "✔️ Đã tải trang: $url<br><br>";

// Parse HTML
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($html);
$xpath = new DOMXPath($doc);

// Lấy tất cả <a>
$nodes = $xpath->query('//a');

echo "<h3>📌 TIÊU ĐỀ TRANG VNEEXPRESS</h3>";

$count = 0;

foreach ($nodes as $a) {

    // Đảm bảo $a là DOMElement thì mới dùng getAttribute()
    if (!($a instanceof DOMElement)) {
        continue;
    }

    $text = trim($a->textContent);   // textContent chính xác hơn nodeValue
    $href = $a->getAttribute('href');

    // Bỏ qua link không hợp lệ
    if (!$text || strlen($text) < 20) continue;
    if (strpos($href, 'javascript:') === 0) continue;
    if ($href === '#' || !$href) continue;

    // Chuẩn hóa link tương đối
    if (strpos($href, 'http') !== 0) {
        $href = rtrim($url, '/') . '/' . ltrim($href, '/');
    }

    echo "<p><a href='$href' target='_blank'>$text</a></p>";
    $count++;

    if ($count >= 20) break;
}

if ($count == 0) {
    echo "<p>❌ Không tìm thấy tiêu đề nào phù hợp.</p>";
}
