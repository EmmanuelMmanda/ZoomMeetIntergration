<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit();
}
?>
<?php include('header.html') ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Live Session</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Create Live Session</h2>
        <form action="../zoom/create.php" method="post">
            <div class="form-group">
                <label for="course_id">Course</label>
                <select class="form-control" id="course_id" name="course_id" required>
                    <?php
                    include('../includes/db.php');
                    $instructor_id = $_SESSION['user_id'];
                    $sql = "SELECT id, title FROM Courses WHERE instructor_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $instructor_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['title'] . "</option>";
                        }
                    } else {
                        echo "<option value=''>No courses available</option>";
                    }
                    $stmt->close();
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="topic">Meeting Topic</label>
                <input type="text" class="form-control" id="topic" name="topic" required>
            </div>
            <div class="form-group">
                <label for="password">Meeting Password</label>
                <input type="text" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="scheduled_at">Scheduled Time</label>
                <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at" required>
            </div>
            <div class="form-group">
                <label for="duration">Duration (minutes)</label>
                <input type="number" class="form-control" id="duration" name="duration" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Session</button>
        </form>
    </div>
</body>
</html>
