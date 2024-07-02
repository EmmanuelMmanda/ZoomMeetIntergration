<?php
include '../includes/db.php';

session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle form submission for adding or updating a course
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addCourse'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $instructor_id = $_POST['instructor_id'];

        $stmt = $conn->prepare('INSERT INTO Courses (title, description, instructor_id) VALUES (?, ?, ?)');
        $stmt->bind_param('ssi', $title, $description, $instructor_id);
        $stmt->execute();
        $stmt->close();

        // Redirect to refresh the page and prevent form resubmission
        header('Location: course_management.php');
        exit();
    } elseif (isset($_POST['updateCourse'])) {
        $id = $_POST['course_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $instructor_id = $_POST['instructor_id'];

        $stmt = $conn->prepare('UPDATE Courses SET title = ?, description = ?, instructor_id = ? WHERE id = ?');
        $stmt->bind_param('ssii', $title, $description, $instructor_id, $id);
        $stmt->execute();
        $stmt->close();

        // Redirect to refresh the page and prevent form resubmission
        header('Location: course_management.php');
        exit();
    }
}

// Handle delete request for a course
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM Courses WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    // Redirect to refresh the page and prevent form resubmission
    header('Location: course_management.php');
    exit();
}

// Retrieve all courses and instructors
$stmt = $conn->query('SELECT Courses.id, Courses.title, Courses.description, Users.fullname FROM Courses LEFT JOIN Users ON Courses.instructor_id = Users.id');
$courses = $stmt->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Retrieve all instructors
$stmt = $conn->query('SELECT id, fullname FROM Users WHERE role = "instructor"');
$instructors = $stmt->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>



<?php
include 'header.html';
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Management</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
</head>
<body>

<div class="container mt-5">
    <h2 style="text-align:center;">Course Management</h2>

    <!-- Add Course Form -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            Add Course
        </div>
        <div class="card-body">
            <form action="" method="post">
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="instructor_id">Instructor:</label>
                    <select class="form-control" id="instructor_id" name="instructor_id" required>
                        <option value="">Select Instructor</option>
                        <?php foreach ($instructors as $instructor): ?>
                            <option value="<?php echo $instructor['id']; ?>"><?php echo $instructor['fullname']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" name="addCourse">Add Course</button>
            </form>
        </div>
    </div>

    <!-- List of Courses -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            List of Courses
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Instructor</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['title']); ?></td>
                        <td><?php echo htmlspecialchars($course['description']); ?></td>
                        <td><?php echo htmlspecialchars($course['fullname']); ?></td>
                        <td>
                            <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="course_management.php?delete=<?php echo $course['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this course?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                </table>
                </div>
    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>

