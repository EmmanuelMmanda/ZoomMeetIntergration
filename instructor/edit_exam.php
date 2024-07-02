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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $scheduled_at = $_POST['scheduled_at'];
    $duration = $_POST['duration'];
    $questions = $_POST['questions'];

    $conn->begin_transaction();

    try {
        // Update exam details
        $stmt = $conn->prepare("
            UPDATE Exams 
            SET title = ?, description = ?, scheduled_at = ?, duration = ? 
            WHERE id = ? AND course_id IN (SELECT id FROM Courses WHERE instructor_id = ?)
        ");
        $stmt->bind_param('ssiiii', $title, $description, $scheduled_at, $duration, $exam_id, $instructor_id);
        $stmt->execute();
        $stmt->close();

        // Update questions
        foreach ($questions as $question_id => $question) {
            $question_text = $question['text'];
            $question_type = $question['type'];
            $options = isset($question['options']) ? json_encode(explode(', ', $question['options'])) : null;
            $correct_answer = $question['correct_answer'];

            $stmt = $conn->prepare("
                UPDATE Questions 
                SET question_text = ?, question_type = ?, options = ?, correct_answer = ?
                WHERE id = ? AND exam_id = ?
            ");
            $stmt->bind_param('sssiii', $question_text, $question_type, $options, $correct_answer, $question_id, $exam_id);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        $success_message = "Exam updated successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error updating exam. Please try again.";
    }
}

include 'header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Exam</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Edit Exam</h2>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($exam['title']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" required><?php echo htmlspecialchars($exam['description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="scheduled_at" class="form-label">Scheduled At</label>
                <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at" value="<?php echo date('Y-m-d\TH:i', strtotime($exam['scheduled_at'])); ?>" required>
            </div>
            <div class="mb-3">
                <label for="duration" class="form-label">Duration (minutes)</label>
                <input type="number" class="form-control" id="duration" name="duration" value="<?php echo htmlspecialchars($exam['duration']); ?>" required>
            </div>

            <h4>Questions</h4>
            <div id="questions">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Question <?php echo $index + 1; ?></h5>
                            <div class="mb-3">
                                <label for="questions[<?php echo $question['id']; ?>][text]" class="form-label">Question Text</label>
                                <textarea class="form-control" id="questions[<?php echo $question['id']; ?>][text]" name="questions[<?php echo $question['id']; ?>][text]" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="questions[<?php echo $question['id']; ?>][type]" class="form-label">Question Type</label>
                                <select class="form-select" id="questions[<?php echo $question['id']; ?>][type]" name="questions[<?php echo $question['id']; ?>][type]" required>
                                    <option value="multiple_choice" <?php echo ($question['question_type'] === 'multiple_choice') ? 'selected' : ''; ?>>Multiple Choice</option>
                                    <option value="true_false" <?php echo ($question['question_type'] === 'true_false') ? 'selected' : ''; ?>>True/False</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="questions[<?php echo $question['id']; ?>][options]" class="form-label">Options (for multiple choice, separated by commas)</label>
                                <input type="text" class="form-control" id="questions[<?php echo $question['id']; ?>][options]" name="questions[<?php echo $question['id']; ?>][options]" value="<?php echo htmlspecialchars(is_null(json_decode($question['options'], true)) ? '' : implode(', ', json_decode($question['options'], true))); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="questions[<?php echo $question['id']; ?>][correct_answer]" class="form-label">Correct Answer</label>
                                <input type="text" class="form-control" id="questions[<?php echo $question['id']; ?>][correct_answer]" name="questions[<?php echo $question['id']; ?>][correct_answer]" value="<?php echo htmlspecialchars($question['correct_answer']); ?>" required>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-primary">Update Exam</button>
            <a href="exam_management.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>
