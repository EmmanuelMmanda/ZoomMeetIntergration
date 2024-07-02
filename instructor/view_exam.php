<?php
include '../includes/db.php';
session_start();

// Check if the user is logged in and is an instructor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit();
}

$instructor_id = $_SESSION['user_id'];
$exam_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$exam_id) {
    header('Location: exam_management.php');
    exit();
}

// Fetch exam details
$stmt = $conn->prepare("
    SELECT e.*, c.title AS course_title 
    FROM Exams e
    JOIN Courses c ON e.course_id = c.id
    WHERE e.id = ? AND c.instructor_id = ?
");
$stmt->bind_param('ii', $exam_id, $instructor_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exam) {
    header('Location: exam_management.php');
    exit();
}

// Fetch questions for the exam
$stmt = $conn->prepare("
    SELECT q.* 
    FROM Questions q
    WHERE q.exam_id = ?
    ORDER BY q.id
");
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
    <title>View Exam</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
</head>
<body>
    <div class="container mt-4">
        <h2>View Exam</h2>
        
        <div class="mb-3">
            <h4>Exam Details</h4>
            <p><strong>Title:</strong> <?php echo htmlspecialchars($exam['title']); ?></p>
            <p><strong>Course:</strong> <?php echo htmlspecialchars($exam['course_title']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($exam['description']); ?></p>
            <p><strong>Scheduled At:</strong> <?php echo date('Y-m-d H:i', strtotime($exam['scheduled_at'])); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($exam['duration']); ?> minutes</p>
        </div>

        <h4>Questions</h4>
        <div id="questions">
            <?php foreach ($questions as $index => $question): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Question <?php echo $index + 1; ?></h5>
                        <p><strong>Question Text:</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
                        <p><strong>Question Type:</strong> <?php echo htmlspecialchars($question['question_type']); ?></p>
                        <?php if ($question['question_type'] === 'multiple_choice' && $question['options']): ?>
                            <p><strong>Options:</strong></p>
                            <ul>
                                <?php 
                                $options = json_decode($question['options'], true);
                                foreach ($options as $option_index => $option): ?>
                                    <li><?php echo htmlspecialchars($option); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <p><strong>Correct Answer:</strong> 
                            <?php 
                            if ($question['question_type'] === 'multiple_choice') {
                                echo htmlspecialchars($options[$question['correct_answer']]);
                            } else {
                                echo htmlspecialchars($question['correct_answer']);
                            }
                            ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="exam_management.php" class="btn btn-secondary">Back to Exam Management</a>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>
