<?php
header("Content-Type: text/html; charset=UTF-8");

// URL mục Xã hội của Vietnamnet
$url = "https://vietnamnet.vn";

$html = @file_get_contents($url);

if (!$html) {
    die("❌ Không tải được trang $url");
}

echo "✔️ Đã tải thành công trang: <strong>$url</strong><br><br>";

// Parse HTML
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
libxml_clear_errors();

$xpath = new DOMXPath($dom);

/*
 Vietnamnet dùng nhiều dạng tiêu đề:
  - <a class="title">...</a>
  - <h3 class="item-title"><a>...</a></h3>
  - <h2 class="inner-title"><a>...</a></h2>
*/

// XPath lấy tất cả các dạng
$nodes = $xpath->query("
    //a[contains(@class,'title')] |
    //h2[contains(@class,'title')]/a |
    //h3[contains(@class,'title')]/a |
    //h3[contains(@class,'item')]/a
");

echo "<h2>📌 TIÊU ĐỀ TRANG VIETNAMNET</h2>";

$count = 0;

foreach ($nodes as $a) {

    if (!($a instanceof DOMElement)) continue;

    $text = trim($a->textContent);
    $href = $a->getAttribute("href");

    // Lọc tiêu đề rác
    if (!$text || strlen($text) < 20) continue;
    if (!$href || $href === "#" || strpos($href, "javascript:") === 0) continue;

    // Link tương đối → chuyển thành tuyệt đối
    if (strpos($href, "http") !== 0) {
        $href = "https://vietnamnet.vn" . $href;
    }

    echo "<p><a href='$href' target='_blank'>$text</a></p>";

    $count++;
    if ($count >= 20) break;
}

if ($count == 0) {
    echo "<p>❌ Không tìm thấy tiêu đề phù hợp.</p>";
}
?>
