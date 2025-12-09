<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Bùi Minh Tài</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #bcb8b6 0%, #988f86 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #54504a;
        }

        .container {
            width: 100%;
            max-width: 600px;
        }

        .card {
            background: #e0e0e5;
            border-radius: 12px;
            padding: 40px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            background-color: #e0e0e3;
        }

        /* Header Section */
        .header {
            display: flex;
            flex-direction: column;
            gap: 25px;
            margin-bottom: 35px;
            align-items: center;
            text-align: center;
        }

        .avatar-box {
            flex-shrink: 0;
            position: relative;
        }

        .avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #7d7d7d 0%, #54504a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            border: 3px solid #988f86;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .avatar-box:hover .avatar {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        }

        .name {
            font-size: 24px;
            font-weight: 600;
            color: #54504a;
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #7d7d7d;
            margin-bottom: 8px;
            justify-content: center;
        }

        .info-icon {
            color: #988f86;
            width: 16px;
            text-align: center;
        }

        .label {
            font-weight: 500;
            color: #54504a;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #54504a;
            margin-top: 30px;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #988f86;
            padding-bottom: 12px;
        }

        .labs-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .lab-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: #bcb8b6;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 1px solid #988f86;
        }

        .lab-item:hover {
            background: #988f86;
            border-color: #54504a;
            transform: translateX(6px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .lab-number {
            color: #54504a;
            font-weight: 600;
        }

        .lab-item:hover .lab-number {
            color: #e0e0e5;
        }

        .lab-name {
            font-size: 16px;
            color: #54504a;
            font-weight: 600;
        }

        .lab-item:hover .lab-name {
            color: #ffffff;
        }

        .lab-link {
            font-size: 12px;
            color: #54504a;
            text-decoration: none;
            padding: 6px 12px;
            background: #e0e0e5;
            border-radius: 4px;
            transition: 0.3s;
        }

        .lab-link:hover {
            background: #54504a;
            color: #ffffff;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="card">
            <!-- Header -->
            <div class="header">
                <div class="avatar-box">
                    <img src="image.png" alt="Avatar" class="avatar">
                </div>
                <div class="info-box">
                    <div class="name">Bùi Minh Tài</div>
                    
                    <div class="info-item">
                        <span class="info-icon">🆔</span>
                        <span class="label">MSSV:</span>
                        <span>DH52201380</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-icon">📅</span>
                        <span class="label">Ca học:</span>
                        <span>T4 - Ca 4</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-icon">🎓</span>
                        <span class="label">Lớp:</span>
                        <span>D22_TH07</span>
                    </div>
                </div>
            </div>

            <!-- Labs Section -->
            <div class="section-title">Danh sách bài Lab</div>

            <div class="labs-list">
                <div class="lab-item">
                    <div class="lab-number">Lab 01</div>
                    <a href="lab01/" class="lab-link">Xem chi tiết →</a>
                </div>

                <div class="lab-item">
                    <div class="lab-number">Lab 02</div>
                    <a href="lab02/" class="lab-link">Xem chi tiết →</a>
                </div>

                <div class="lab-item">
                    <div class="lab-number">Lab 03</div>
                    <a href="lab03/" class="lab-link">Xem chi tiết →</a>
                </div>

                <div class="lab-item">
                    <div class="lab-number">Lab 04</div>
                    <a href="lab04/" class="lab-link">Xem chi tiết →</a>
                </div>

                <div class="lab-item">
                    <div class="lab-number">Lab 05</div>
                    <a href="lab05/" class="lab-link">Xem chi tiết →</a>
                </div>

                <div class="lab-item">
                    <div class="lab-number">Lab 06</div>
                    <a href="lab06/" class="lab-link">Xem chi tiết →</a>
                </div>

                <div class="lab-item">
                    <div class="lab-number">Lab 07</div>
                    <a href="lab07/" class="lab-link">Xem chi tiết →</a>
                </div>

                <div class="lab-item">
                    <div class="lab-number">Lab 08</div>
                    <a href="lab08/" class="lab-link">Xem chi tiết →</a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
