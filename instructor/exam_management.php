<?php
include '../includes/db.php';
session_start();

// Check if the user is logged in and is an instructor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit();
}

$instructor_id = $_SESSION['user_id'];

// Fetch all exams for the instructor's courses
$stmt = $conn->prepare("
    SELECT e.*, c.title AS course_title 
    FROM Exams e
    JOIN Courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY e.scheduled_at DESC
");
$stmt->bind_param('i', $instructor_id);
$stmt->execute();
$exams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle exam deletion
if (isset($_POST['delete_exam'])) {
    $exam_id = $_POST['exam_id'];
    
    // Start a transaction
    $conn->begin_transaction();
    
    try {
        // Delete associated answers
        $stmt = $conn->prepare("DELETE FROM Answers WHERE question_id IN (SELECT id FROM Questions WHERE exam_id = ?)");
        $stmt->bind_param('i', $exam_id);
        $stmt->execute();
        $stmt->close();
        
        // Delete questions
        $stmt = $conn->prepare("DELETE FROM Questions WHERE exam_id = ?");
        $stmt->bind_param('i', $exam_id);
        $stmt->execute();
        $stmt->close();
        
        // Delete exam
        $stmt = $conn->prepare("DELETE FROM Exams WHERE id = ? AND course_id IN (SELECT id FROM Courses WHERE instructor_id = ?)");
        $stmt->bind_param('ii', $exam_id, $instructor_id);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        $delete_message = "Exam deleted successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $delete_error = "Error deleting exam. Please try again.";
    }
    
    // Refresh the exams list
    $stmt = $conn->prepare("
        SELECT e.*, c.title AS course_title 
        FROM Exams e
        JOIN Courses c ON e.course_id = c.id
        WHERE c.instructor_id = ?
        ORDER BY e.scheduled_at DESC
    ");
    $stmt->bind_param('i', $instructor_id);
    $stmt->execute();
    $exams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

include 'header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Management</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Exam Management</h2>
        
        <?php if (isset($delete_message)): ?>
            <div class="alert alert-success"><?php echo $delete_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($delete_error)): ?>
            <div class="alert alert-danger"><?php echo $delete_error; ?></div>
        <?php endif; ?>
        
        <a href="create_exam.php" class="btn btn-primary mb-3">Create New Exam</a>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Scheduled At</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $index => $exam): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($exam['title']); ?></td>
                        <td><?php echo htmlspecialchars($exam['course_title']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($exam['scheduled_at'])); ?></td>
                        <td><?php echo $exam['duration']; ?> minutes</td>
                        <td>
                            <a href="view_exam.php?id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-info">View</a>
                       <!--     <a href="edit_exam.php?id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-primary">Edit</a> -->
                            <form action="" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this exam?');">
                                <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                                <button type="submit" name="delete_exam" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>
