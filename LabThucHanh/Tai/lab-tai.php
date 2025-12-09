<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách bài tập</title>

    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2d, #12121c);
            color: #e4e4e7;
            padding: 40px;
            animation: fadeIn 0.7s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #9cc2ff;
            text-shadow: 0 0 10px rgba(130, 177, 255, 0.4);
        }

        hr {
            width: 60%;
            margin: 20px auto 40px;
            border: none;
            height: 2px;
            background: linear-gradient(to right, #6ca8ff, #82b1ff, #6ca8ff);
            border-radius: 5px;
            opacity: 0.6;
        }

        ul {
            list-style: none;
            padding: 0;
            max-width: 450px;
            margin: auto;
        }

        li {
            background: rgba(255, 255, 255, 0.05);
            padding: 18px 22px;
            margin-bottom: 14px;
            border-radius: 12px;
            font-size: 18px;
            transition: 0.25s;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.35);
        }

        li:hover {
            background: rgba(130, 177, 255, 0.15);
            transform: translateX(8px);
            border-color: rgba(130, 177, 255, 0.5);
            box-shadow: 0 0 15px rgba(130, 177, 255, 0.4);
        }

        a {
            text-decoration: none;
            color: #a8c8ff;
            font-weight: bold;
            letter-spacing: 0.5px;
            transition: 0.2s;
        }

        a:hover {
            color: #d0e2ff;
            text-shadow: 0 0 8px rgba(210, 230, 255, 0.7);
        }
    </style>
</head>

<body>

    <h1>NGUYỄN TRUNG HẬU - DH52200651 - D22_TH07</h1>
    <hr>
    <h1>Danh sách bài tập</h1>

    <ul>
        <li><a href="lab02/">Bài tập Lab 02</a></li>
        <li><a href="lab03/">Bài tập Lab 03</a></li>
        <li><a href="lab04/">Bài tập Lab 04</a></li>
        <li><a href="lab05/">Bài tập Lab 05</a></li>
    </ul>

</body>

</html>