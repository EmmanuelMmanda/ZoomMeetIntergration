<?php
include('../includes/db.php');

// Fetch assignments from the database
$sql = "SELECT Assignments.*, Courses.title AS course_title FROM Assignments JOIN Courses ON Assignments.course_id = Courses.id";
$result = $conn->query($sql);
?>

<?php
include('header.html');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignments</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .container {
            max-width: 800px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-body {
            padding: 20px;
        }
        .assignment-title {
            font-size: 1.2em;
        }
        .course-title {
            font-size: 0.9em;
            color: #6c757d;
        }
        .due-date {
            font-size: 0.9em;
            color: #dc3545;
        }
        .file-link {
            text-decoration: none;
            color: #007bff;
        }
        .file-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Available Assignments</h2>
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="assignment-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="course-title">Course: <?= htmlspecialchars($row['course_title']) ?></p>
                                <p class="due-date">Due Date: <?= date('F j, Y, g:i a', strtotime($row['due_date'])) ?></p>
                                <p><?= htmlspecialchars($row['description']) ?></p>
                                <?php if (!empty($row['file_path'])): ?>
                                    <p><a href="../uploads/<?= htmlspecialchars($row['file_path']) ?>" class="file-link" target="_blank">Download Attachment</a></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-md-12">
                    <div class="alert alert-info" role="alert">
                        No assignments available.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Add any additional JavaScript for interactivity -->
</body>
</html>

<?php
$conn->close();
?>
