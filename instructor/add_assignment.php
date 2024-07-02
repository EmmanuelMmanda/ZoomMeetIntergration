<?php
include('../includes/db.php');

// Fetch courses assigned to the instructor (assuming instructor_id is stored in session)
session_start();
$instructor_id = $_SESSION['user_id'];
$sql = "SELECT * FROM Courses WHERE instructor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$courses = $stmt->get_result();
?>

<?php
include('header.html')
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Assignment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            max-width: 600px;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-body {
            padding: 20px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #007bff;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title text-center">Add Assignment</h3>
                <form id="assignmentForm" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="course">Course</label>
                        <select id="course" name="course_id" class="form-control" required>
                            <option value="">Select Course</option>
                            <?php while($course = $courses->fetch_assoc()): ?>
                                <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="title">Assignment Title</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input type="datetime-local" id="due_date" name="due_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="file">Upload File</label>
                        <input type="file" id="file" name="file" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Add Assignment</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#assignmentForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                
                $.ajax({
                    url: 'process_assignment.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Assignment Added',
                            text: response,
                        });
                        $('#assignmentForm')[0].reset();
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'There was an error adding the assignment.',
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
