<?php
    // // Cách 1: Dùng file_get_contents
    // $url = 'https://vnexpress.net/the-thao';
    // $content = file_get_contents($url);
    // echo $content;


    // Cách 2: Dùng cURL
$url = 'https://vnexpress.net/the-thao';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Thêm header giống trình duyệt thật
curl_setopt($ch, CURLOPT_USERAGENT, 
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36'
);

curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Tự động theo dõi redirect
curl_setopt($ch, CURLOPT_ENCODING, "");          // Tự giải mã gzip/deflate
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // (Tắt kiểm chứng SSL – chỉ dùng để test)

// Thực thi
$content = curl_exec($ch);

// Kiểm tra lỗi
if ($content === false) {
    echo "cURL error: " . curl_error($ch);
}

curl_close($ch);

// In HTML trả về
echo $content;
?>
