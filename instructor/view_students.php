<?php

session_start();

// Check if the user is logged in and is an instructor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit();
}

include '../includes/db.php';

// Fetch instructor's assigned courses
$instructor_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT id, title FROM Courses WHERE instructor_id = ?');
$stmt->bind_param('i', $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch students enrolled in each course with additional information
$students = [];
foreach ($courses as $course) {
    $course_id = $course['id'];
    $stmt = $conn->prepare('SELECT U.id, U.fullname, U.email, U.gender, U.phone_number, U.address, U.college 
                            FROM Users U 
                            INNER JOIN Enrollments E ON U.id = E.user_id 
                            WHERE E.course_id = ?');
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $students[$course_id] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>


<?php
include 'header.html';
?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">View Students</h2>
        <?php foreach ($courses as $course): ?>
            <h3>Course Name: <?php echo $course['title']; ?></h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Phone Number</th>
                        <th>Address</th>
                        <th>College</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students[$course['id']] as $index => $student): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $student['fullname']; ?></td>
                            <td><?php echo $student['email']; ?></td>
                            <td><?php echo $student['gender']; ?></td>
                            <td><?php echo $student['phone_number']; ?></td>
                            <td><?php echo $student['address']; ?></td>
                            <td><?php echo $student['college']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
