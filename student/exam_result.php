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

// Fetch exam details
$stmt = $conn->prepare('SELECT e.*, c.title AS course_title FROM Exams e JOIN Courses c ON e.course_id = c.id WHERE e.id = ?');
$stmt->bind_param('i', $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exam) {
    header('Location: exam_selection.php');
    exit();
}

// Fetch questions and answers
$stmt = $conn->prepare('
    SELECT q.*, a.answer_text AS user_answer, a.is_correct
    FROM Questions q
    LEFT JOIN Answers a ON q.id = a.question_id AND a.user_id = ?
    WHERE q.exam_id = ?
    ORDER BY q.id
');
$stmt->bind_param('ii', $user_id, $exam_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate score
$total_questions = count($questions);
$correct_answers = array_sum(array_column($questions, 'is_correct'));
$score = ($correct_answers / $total_questions) * 100;

include 'header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Result</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Exam Result</h2>
        <h3><?php echo $exam['title']; ?> (<?php echo $exam['course_title']; ?>)</h3>
        <p><strong>Score: </strong><?php echo number_format($score, 2); ?>% (<?php echo $correct_answers; ?> out of <?php echo $total_questions; ?> correct)</p>

        <h4 class="mt-4">Question Review</h4>
        <?php foreach ($questions as $index => $question): ?>
            <?php 
                $options = json_decode($question['options'], true); 
                $correct_answer_index = (int)$question['correct_answer'];
                $correct_answer_text = $options[$correct_answer_index];
                $user_answer_text = $options[$question['user_answer']];
            ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Question <?php echo $index + 1; ?></h5>
                    <p class="card-text"><?php echo $question['question_text']; ?></p>
                    <p><strong>Your Answer: </strong><?php echo $user_answer_text; ?></p>
                    <p><strong>Correct Answer: </strong><?php echo $correct_answer_text; ?></p>
                    <?php if ($question['is_correct']): ?>
                        <p class="text-success">Correct</p>
                    <?php else: ?>
                        <p class="text-danger">Incorrect</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <a href="exam_selection.php" class="btn btn-primary">Back to Exam Selection</a>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>
