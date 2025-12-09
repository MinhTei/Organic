<?php
header("Content-Type: text/html; charset=UTF-8");

$url = "https://tuoitre.vn/";

$html = @file_get_contents($url);

if (!$html) {
    die(" Không tải được trang $url");
}
echo "Đã tải thành công trang: <strong>$url</strong><br><br>";
// Parse HTML
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($html);
libxml_clear_errors();

$xpath = new DOMXPath($dom);

// Tuổi Trẻ: tiêu đề thường nằm trong <h3 class="title-news"> hoặc <a class="box-category-link">
$nodes = $xpath->query("//h3/a | //a[contains(@class,'box-category-link')]");

echo "<h2>📌 TIÊU ĐỀ TRANG TUỔI TRẺ</h2>";

$count = 0;

foreach ($nodes as $a) {

    if (!($a instanceof DOMElement)) continue;

    $text = trim($a->textContent);
    $href = $a->getAttribute("href");

    if (!$text || strlen($text) < 20) continue;
    if (!$href || $href === "#" || strpos($href, "javascript:") === 0) continue;

    // Nếu link tương đối → chuyển thành tuyệt đối
    if (strpos($href, "http") !== 0) {
        $href = "https://tuoitre.vn" . $href;
    }

    echo "<p><a href='$href' target='_blank'>$text</a></p>";

    $count++;
    if ($count >= 30) break;
}

if ($count == 0) {
    echo "<p>Không tìm được tiêu đề phù hợp.</p>";
}
?>
