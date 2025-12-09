<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAB THỰC HÀNH CỦA HẬU</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #1a2332, #0f1419);
            color: #e4e4e7;
            padding: 40px 20px;
            min-height: 100vh;
            animation: fadeIn 0.7s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header Section */
        .profile-header {
            text-align: center;
            margin-bottom: 20px;
            animation: slideDown 0.6s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid rgba(156, 220, 254, 0.3);
            box-shadow: 0 0 20px rgba(156, 220, 254, 0.3);
            background: rgba(255, 255, 255, 0.05);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #9cdcfe;
            text-shadow: 0 0 10px rgba(156, 220, 254, 0.4);
            margin-bottom: 12px;
        }

        .profile-info {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .info-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(156, 220, 254, 0.1);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            color: #9cdcfe;
            border: 1px solid rgba(156, 220, 254, 0.2);
        }

        .info-tag span {
            font-size: 18px;
        }

        hr {
            width: 60%;
            margin: 30px auto 40px;
            border: none;
            height: 2px;
            background: linear-gradient(to right, #6ca8ff, #9cdcfe, #6ca8ff);
            border-radius: 5px;
            opacity: 0.5;
        }

        .section-title {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #9cdcfe;
            text-shadow: 0 0 10px rgba(156, 220, 254, 0.4);
            margin-bottom: 30px;
        }

        /* Tasks List */
        .tasks-list {
            list-style: none;
            padding: 0;
            max-width: 500px;
            margin: auto;
        }

        .task-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px 24px;
            margin-bottom: 14px;
            border-radius: 12px;
            transition: 0.25s;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(156, 220, 254, 0.15);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.35);
            animation: slideUp 0.5s ease backwards;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .task-item:nth-child(1) { animation-delay: 0.1s; }
        .task-item:nth-child(2) { animation-delay: 0.15s; }
        .task-item:nth-child(3) { animation-delay: 0.2s; }
        .task-item:nth-child(4) { animation-delay: 0.25s; }
        .task-item:nth-child(5) { animation-delay: 0.3s; }
        .task-item:nth-child(6) { animation-delay: 0.35s; }
        .task-item:nth-child(7) { animation-delay: 0.4s; }
        .task-item:nth-child(8) { animation-delay: 0.45s; }

        .task-item:hover {
            background: rgba(156, 220, 254, 0.12);
            transform: translateX(8px);
            border-color: rgba(156, 220, 254, 0.4);
            box-shadow: 0 0 15px rgba(156, 220, 254, 0.3);
        }

        .task-link {
            text-decoration: none;
            color: #a8d5ff;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0.5px;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .task-link:hover {
            color: #d0e8ff;
            text-shadow: 0 0 8px rgba(208, 232, 255, 0.6);
        }

        .task-arrow {
            font-size: 20px;
            transition: transform 0.2s ease;
        }

        .task-item:hover .task-arrow {
            transform: translateX(5px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 30px 15px;
            }

            .profile-name {
                font-size: 26px;
            }

            .profile-avatar {
                width: 80px;
                height: 80px;
            }

            .info-tag {
                font-size: 12px;
                padding: 6px 12px;
            }

            .section-title {
                font-size: 24px;
            }

            .task-link {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <img src="image.png" alt="Avatar">
            </div>
            <h1 class="profile-name">NGUYỄN TRUNG HẬU</h1>
            <div class="profile-info">
                <div class="info-tag">
                    <span class="material-symbols-outlined">badge</span>
                    DH52200651
                </div>
                <div class="info-tag">
                    <span class="material-symbols-outlined">group</span>
                    D22_TH07
                </div>
                <div class="info-tag">
                    <span class="material-symbols-outlined">calendar_month</span>
                    Ca học: Thứ 7-Ca1 
                </div>
            </div>
        </div>

        <hr>

        <!-- Tasks Section -->
        <h2 class="section-title">Danh sách bài Lab Thực Hành</h2>

        <ul class="tasks-list">
            <li class="task-item">
                <a href="lab01/" class="task-link">
                    <span>Bài tập Lab 01</span>
                    <span class="material-symbols-outlined task-arrow">arrow_forward</span>
                </a>
            </li>
            <li class="task-item">
                <a href="lab02/" class="task-link">
                    <span>Bài tập Lab 02</span>
                    <span class="material-symbols-outlined task-arrow">arrow_forward</span>
                </a>
            </li>
            <li class="task-item">
                <a href="lab03/" class="task-link">
                    <span>Bài tập Lab 03</span>
                    <span class="material-symbols-outlined task-arrow">arrow_forward</span>
                </a>
            </li>
            <li class="task-item">
                <a href="lab04/" class="task-link">
                    <span>Bài tập Lab 04</span>
                    <span class="material-symbols-outlined task-arrow">arrow_forward</span>
                </a>
            </li>
            <li class="task-item">
                <a href="lab05/" class="task-link">
                    <span>Bài tập Lab 05</span>
                    <span class="material-symbols-outlined task-arrow">arrow_forward</span>
                </a>
            </li>
            <li class="task-item">
                <a href="lab06/" class="task-link">
                    <span>Bài tập Lab 06</span>
                    <span class="material-symbols-outlined task-arrow">arrow_forward</span>
                </a>
            </li>
            <li class="task-item">
                <a href="lab07/" class="task-link">
                    <span>Bài tập Lab 07</span>
                    <span class="material-symbols-outlined task-arrow">arrow_forward</span>
                </a>
            </li>
            <li class="task-item">
                <a href="lab08/" class="task-link">
                    <span>Bài tập Lab 08</span>
                    <span class="material-symbols-outlined task-arrow">arrow_forward</span>
                </a>
            </li>
        </ul>
    </div>
</body>

</html>