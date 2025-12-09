<!DOCTYPE html>
<html lang="en">

<head>
    <title>Lab5_3</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
        }

        .exam-form {
            max-width: 800px;
            margin: 0 auto;
        }

        .question-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background: #f9f9f9;
        }

        .question-text {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .options-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .options-list li {
            margin-bottom: 5px;
            padding: 5px;
        }

        .submit-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }

        /* Hiển thị kết quả */
        .results-box {
            border: 3px solid #28a745;
            padding: 20px;
            margin-top: 30px;
            border-radius: 8px;
            background: #e9f8ed;
        }

        .score {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }

        .correct {
            color: green;
            font-weight: bold;
        }

        .incorrect {
            color: red;
            font-weight: bold;
        }

        .answer-detail {
            font-size: 0.9em;
            margin-top: 5px;
            padding-left: 20px;
        }
    </style>
</head>

<body>
    <?php
    /**
     * Mảng chứa danh sách các câu hỏi trắc nghiệm (n câu hỏi)
     * Mỗi câu hỏi là một mảng liên hợp.
     */
    $questions = [
        [
            'id' => 1,
            'question' => 'Thủ đô của Việt Nam là gì?',
            'options' => [
                'A' => 'TP. Hồ Chí Minh',
                'B' => 'Hà Nội',
                'C' => 'Đà Nẵng',
                'D' => 'Huế'
            ],
            'answer' => 'B'
        ],
        [
            'id' => 2,
            'question' => 'Ngôn ngữ lập trình nào thường được sử dụng cho phát triển web phía máy chủ (backend)?',
            'options' => [
                'A' => 'HTML',
                'B' => 'CSS',
                'C' => 'PHP',
                'D' => 'JavaScript (chỉ frontend)'
            ],
            'answer' => 'C'
        ],
        [
            'id' => 3,
            'question' => '4 * 5 bằng bao nhiêu?',
            'options' => [
                'A' => '9',
                'B' => '1',
                'C' => '20',
                'D' => '10'
            ],
            'answer' => 'C'
        ],
        [
            'id' => 4,
            'question' => 'Mặt trời mọc ở hướng nào?',
            'options' => [
                'A' => 'Tây',
                'B' => 'Đông',
                'C' => 'Nam',
                'D' => 'Bắc'
            ],
            'answer' => 'B'
        ],
        [
            'id' => 5,
            'question' => 'Đơn vị tiền tệ của Nhật Bản là gì?',
            'options' => [
                'A' => 'Won',
                'B' => 'Yuan',
                'C' => 'Yen',
                'D' => 'Dollar'
            ],
            'answer' => 'C'
        ]
    ];
    //Cài đặt đề thi
    $n = count($questions); //Số lượng câu hỏi
    $m = 3; //Số lượng câu muốn lấy random
    //Kiểm tra điều kiện số câu hỏi lượng lấy phải nhỏ hơn số lượng câu hỏi đang có
    if ($m >= $n) {
        echo ("Lỗi, số câu hỏi cần lấy ($m) phải nhỏ hơn số lượng câu hỏi có sẳn ($n)");
    }
    // Lấy ngẫu nhiên $m câu hỏi (Chỉ chạy lần đầu hoặc khi chưa nộp bài)
    if (!isset($_POST['submit_exam'])) {
        $random_keys = array_rand($questions, $m);
        $exam_questions = [];
        foreach ($random_keys as $key) {
            $exam_questions[] = $questions[$key];
        }
        // Lưu tạm thời các câu hỏi đã chọn (hoặc ID của chúng) vào Session 
        // để giữ nguyên đề thi sau khi submit (trong ví dụ này, chúng ta giả lập)
        // Trong môi trường thực tế, bạn cần dùng Session để lưu trạng thái.
        // Ở đây, vì đề thi cố định, ta dùng cách đơn giản hóa:
        $current_exam_question = $exam_questions;
    } else {
        // Nếu đã submit, ta cần đảm bảo mảng câu hỏi hiển thị không thay đổi.
        // Do ví dụ này không dùng Session, ta giả định lại việc lấy ngẫu nhiên 
        // trong môi trường thực tế cần phải dùng Session hoặc hidden fields.

        // Đơn giản hóa: Nếu đã submit, ta lấy lại $m câu hỏi ngẫu nhiên TỪ ĐẦU
        $random_keys = array_rand($questions, $m);
        $current_exam_question = [];
        foreach ($random_keys as $key) {
            $current_exam_question[] = $questions[$key];
        }
    }

    $results = [];
    $total_score = 0;
    $score_question = 1; //Mỗi câu đúng đc 1 điểm

    //Xử Lý Nộp Bài
    if (isset($_POST['submit_exam'])) {
        $user_answers = $_POST['answer'] ?? [];
        foreach ($current_exam_question as $ceq) {
            $ceq_id = $ceq['id'];
            $user_choice = $user_answers[$ceq_id] ?? null;
            $correct_answer = $ceq['answer'];
            $is_correct = ($user_choice === $correct_answer);

            $results[] = [
                'question_id' => $ceq_id,
                'question_text' => $ceq['question'],
                'user_choice' => $user_choice,
                'correct_answer' => $correct_answer,
                'is_correct' => $is_correct
            ];

            if ($is_correct) {
                $total_score += $score_question;
            }
        }
    }
    ?>
    <div class="exam-form">
        <h1>Đề Thi Trắc Nghiệm Ngẫu Nhiên (<?php echo $m; ?> Câu) </h1>
        <?php if (isset($_POST['submit_exam'])): // Hiển thị kết quả sau khi nộp bài 
        ?>

            <div class="results-box">
                <h2>Kết Quả Bài Thi</h2>
                <p>Tổng điểm của bạn là: <span class="score"><?php echo $total_score; ?> / <?php echo $m * $score_question; ?></span></p>

                <hr>
                <h3>Chi tiết đáp án:</h3>

                <?php $count = 1;
                foreach ($results as $r): ?>
                    <div class="question-box">
                        <div class="question-text">Câu <?php echo $count; ?>. <?php echo htmlspecialchars($r['question_text']); ?></div>

                        <?php if ($r['is_correct']): ?>
                            <p class="correct">
                                Đúng! Bạn chọn: <?php echo htmlspecialchars($r['user_choice'] ?? 'Chưa chọn'); ?> </p>
                        <?php else: ?>
                            <p class="incorrect">
                                Sai! Bạn chọn: <?php echo htmlspecialchars($r['user_choice'] ?? 'Chưa chọn'); ?>
                            </p>
                            <div class="answer-detail">
                                Đáp án đúng là: <?php echo htmlspecialchars($r['correct_answer']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php $count++;
                endforeach; ?>

                <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <button type="submit" class="submit-btn" style="background-color: #dc3545;">Làm lại bài khác</button>
                </form>
            </div>

        <?php else: // Hiển thị Form làm bài thi 
        ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

                <?php $count = 1;
                foreach ($current_exam_question as $ceq): ?>
                    <div class="question-box">
                        <div class="question-text">Câu <?php echo $count; ?>. <?php echo htmlspecialchars($ceq['question']); ?></div>
                        <ul class="options-list">

                            <?php foreach ($ceq['options'] as $key => $option): ?>
                                <li>
                                    <label>
                                        <input type="radio"
                                            name="answer[<?php echo $ceq['id']; ?>]"
                                            value="<?php echo htmlspecialchars($key); ?>"
                                            required>
                                        <?php echo htmlspecialchars($key); ?>. <?php echo htmlspecialchars($option); ?>
                                    </label>
                                </li>
                            <?php endforeach; ?>

                        </ul>
                    </div>
                <?php $count++;
                endforeach; ?>

                <button type="submit" name="submit_exam" class="submit-btn">Nộp Bài</button>
            </form>

        <?php endif; ?>
    </div>
</body>

</html>