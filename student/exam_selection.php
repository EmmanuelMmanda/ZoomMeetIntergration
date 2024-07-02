<?php
include '../includes/db.php';
session_start();

// Check if the user is logged in and is a student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch enrolled courses for the student
$stmt = $conn->prepare('SELECT id, title FROM Courses
                        INNER JOIN Enrollments ON Courses.id = Enrollments.course_id
                        WHERE Enrollments.user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch available exams for the courses the student is enrolled in
$exams = [];
foreach ($courses as $course) {
    $stmt = $conn->prepare('SELECT Exams.id, Exams.title, Exams.description, Exams.duration, Courses.title AS course_title
                            FROM Exams 
                            INNER JOIN Courses ON Exams.course_id = Courses.id
                            WHERE Exams.course_id = ?');
    $stmt->bind_param('i', $course['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $exams[] = $row;
    }
    $stmt->close();
}

include 'header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Selection</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-4 mb-4">Select Exam to Take</h2>
        <div class="list-group">
            <?php foreach ($exams as $exam): ?>
                <a href="take_exam.php?exam_id=<?php echo $exam['id']; ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?php echo $exam['title']; ?></h5>
                        <small><?php echo $exam['duration']; ?> minutes</small>
                    </div>
                    <p class="mb-1"><?php echo $exam['description']; ?></p>
                    <small>Course: <?php echo $exam['course_title']; ?></small>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>