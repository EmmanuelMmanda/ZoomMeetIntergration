<?php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$exam_id = isset($_GET['exam_id']) ? $_GET['exam_id'] : null;

if (!$exam_id) {
    header('Location: exam_selection.php');
    exit();
}

// Check if the student has already taken this exam
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM Answers WHERE user_id = ? AND question_id IN (SELECT id FROM Questions WHERE exam_id = ?)');
$stmt->bind_param('ii', $user_id, $exam_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($result['count'] > 0) {
    echo "<script>alert('You have already taken this exam.'); window.location.href='exam_selection.php';</script>";
    exit();
}

// Fetch exam details
$stmt = $conn->prepare('SELECT * FROM Exams WHERE id = ?');
$stmt->bind_param('i', $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exam) {
    header('Location: exam_selection.php');
    exit();
}

// Fetch questions for the exam
$stmt = $conn->prepare('SELECT * FROM Questions WHERE exam_id = ?');
$stmt->bind_param('i', $exam_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include 'header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Exam</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
    <style>
        #timer {
            font-size: 24px;
            font-weight: bold;
            color: red;
            text-align: right;
        }
        #question-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mt-4 mb-4"><?php echo $exam['title']; ?></h2>
        <p><?php echo $exam['description']; ?></p>
        <button id="start-exam" class="btn btn-primary">Start Exam</button>
    </div>

    <div class="container" id="exam-container" style="display: none;">
        <div id="timer" class="mb-4">Time Remaining: <span id="time">00:00</span></div>
        <div id="question-container"></div>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
    <script>
    let questions = <?php echo json_encode($questions); ?>;
    let currentQuestionIndex = 0;
    let examDuration = <?php echo $exam['duration']; ?>;
    let timeRemaining = examDuration * 60;
    let timer;
    let answers = {};

    document.getElementById('start-exam').addEventListener('click', function() {
        document.querySelector('.container').style.display = 'none';
        document.getElementById('exam-container').style.display = 'block';
        displayQuestion();
        startTimer();
    });

    function displayQuestion() {
        if (currentQuestionIndex >= questions.length) {
            submitExam();
            return;
        }

        let question = questions[currentQuestionIndex];
        let options = JSON.parse(question.options);
        let questionHTML = `
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Question ${currentQuestionIndex + 1} of ${questions.length}</h5>
                    <p class="card-text">${question.question_text}</p>
                    <div class="form-group">
        `;

        if (question.question_type === 'multiple_choice') {
            options.forEach((option, index) => {
                questionHTML += `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer_${question.id}" value="${index}" ${answers[question.id] === index.toString() ? 'checked' : ''} onchange="saveAnswer(${question.id}, this.value)">
                        <label class="form-check-label">${option}</label>
                    </div>
                `;
            });
        } else if (question.question_type === 'true_false') {
            questionHTML += `
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answer_${question.id}" value="0" ${answers[question.id] === '0' ? 'checked' : ''} onchange="saveAnswer(${question.id}, this.value)">
                    <label class="form-check-label">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answer_${question.id}" value="1" ${answers[question.id] === '1' ? 'checked' : ''} onchange="saveAnswer(${question.id}, this.value)">
                    <label class="form-check-label">False</label>
                </div>
            `;
        }

        questionHTML += `
                    </div>
                </div>
            </div>
            <button onclick="nextQuestion()" class="btn btn-primary">Next</button>
        `;
        document.getElementById('question-container').innerHTML = questionHTML;
    }

    function saveAnswer(questionId, answer) {
        answers[questionId] = answer;
    }

    function nextQuestion() {
        currentQuestionIndex++;
        displayQuestion();
    }

    function startTimer() {
        timer = setInterval(function() {
            if (timeRemaining <= 0) {
                clearInterval(timer);
                submitExam();
            } else {
                timeRemaining--;
                let minutes = Math.floor(timeRemaining / 60);
                let seconds = timeRemaining % 60;
                document.getElementById('time').innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            }
        }, 1000);
    }

    function submitExam() {
        clearInterval(timer);
        let formData = new FormData();
        formData.append('exam_id', <?php echo $exam_id; ?>);
        for (let questionId in answers) {
            formData.append(`answers[${questionId}]`, answers[questionId]);
        }

        fetch('submit_exam.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `exam_result.php?exam_id=<?php echo $exam_id; ?>`;
            } else {
                alert('An error occurred while submitting the exam. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting the exam. Please try again.');
        });
    }
    </script>
</body>
</html>