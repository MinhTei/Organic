<?php
    $url = "http://thethao.vnexpress.net/";
    // Nếu allow_url_fopen bật thì thử dùng file_get_contents
    $content = file_get_contents($url);
    if ($content !== false) {
        echo "✔️ Đọc được trang web. <br/>";
        // Biểu thức chính quy tìm tất cả các thẻ h3 chứa class="title-news"
        $pattern = '/<h3\s+class=["\']title-news["\'][^>]*>(.*?)<\/h3>/is';
        /*
            <h3: bắt đầu thẻ <h3>.
            \s+: có ít nhất một khoảng trắng sau <h3.
            class=["\']title-news["\']: "title-news" hoặc 'title-news' đều đc.
            ["\']: dấu nháy kép " " hoặc nháy đơn ' '.
            [^>]*: khớp với mọi ký tự ngoại trừ >, tức là các thuộc tính khác trong thẻ <h3 ...>.
            (.*?): Nhóm ()
                .: khớp mọi ký tự.
                *?: lặp lại nhiều lần nhưng ở chế độ non-greedy (tham lam ít nhất có thể).
                => Ý nghĩa: lấy nội dung bên trong thẻ <h3> </h3>.
        */
        // Mảng để lưu kết quả
        preg_match_all($pattern, $content, $arr);

        // Kiểm tra và in ra kết quả dạng bảng
        if (!empty($arr[0])) {
            $i = 0;
             ?>
            <table width=100% border=1 cellspacing=0 cellpadding=5>
                <tr>
                    <td>STT</td>
                    <td>Nội dung</td>
                </tr>
            <?php
                // Duyệt qua từng phần tử trong mảng kết quả
                foreach ($arr[0] as $h3Content) {
                    // In ra nội dung của từng thẻ h3 có ptu con chứa class="title-news"
                    echo "<tr><td>" .  $i++ . "</td><td>" . (htmlspecialchars($h3Content));
                }
            } else {
                echo "Không tìm thấy các thẻ <h3 class=\'title-news\'>";
            }
            echo "</td></table>";
    } else {
        echo "❌ Không thể đọc trang web. Hãy kiểm tra lại cấu hình allow_url_fopen.";
    }
?>