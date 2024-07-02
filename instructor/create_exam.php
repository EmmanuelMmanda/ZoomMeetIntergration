<?php
include '../includes/db.php';
session_start();

// Check if the user is logged in and is an instructor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit();
}

$instructor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $scheduled_at = $_POST['scheduled_at'];
    $duration = $_POST['duration'];
    $course_id = $_POST['course_id'];

    $stmt = $conn->prepare('INSERT INTO Exams (course_id, title, description, scheduled_at, duration) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('isssi', $course_id, $title, $description, $scheduled_at, $duration);
    $stmt->execute();
    $exam_id = $stmt->insert_id;
    $stmt->close();

    if (isset($_POST['questions']) && is_array($_POST['questions'])) {
        foreach ($_POST['questions'] as $question) {
            $question_text = $question['question_text'];
            $question_type = $question['question_type'];
            $options = json_encode($question['options']); // Store options as JSON
            $correct_answer = $question['correct_answer'];

            $stmt = $conn->prepare('INSERT INTO Questions (exam_id, question_text, question_type, options, correct_answer) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('issss', $exam_id, $question_text, $question_type, $options, $correct_answer);
            $stmt->execute();
            $stmt->close();
        }
    }

    header('Location: exam_management.php');
    exit();
}

// Fetch courses associated with the logged-in instructor
$stmt = $conn->prepare('SELECT id, title FROM Courses WHERE instructor_id = ?');
$stmt->bind_param('i', $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include 'header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Exam</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-4 mb-4">Create Exam</h2>
        <form action="" method="post">
            <!-- Exam Details -->
            <div class="form-group mb-3">
                <label for="course_id">Course:</label>
                <select class="form-control" id="course_id" name="course_id" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo $course['title']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="title">Title:</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="form-group mb-3">
                <label for="description">Description:</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>
            <div class="form-group mb-3">
                <label for="scheduled_at">Scheduled Date:</label>
                <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at" required>
            </div>
            <div class="form-group mb-3">
                <label for="duration">Duration (in minutes):</label>
                <input type="number" class="form-control" id="duration" name="duration" required>
            </div>

            <!-- Questions -->
            <div id="questions-container">
                <!-- Dynamic question fields will be added here -->
            </div>
            <button type="button" id="add-question" class="btn btn-primary mb-3">Add Question</button>
            <hr>
            <button type="submit" class="btn btn-success">Create Exam</button>
        </form>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
    <script>
    document.getElementById('add-question').addEventListener('click', function() {
        var questionsContainer = document.getElementById('questions-container');
        var questionIndex = questionsContainer.getElementsByClassName('question').length;

        var questionDiv = document.createElement('div');
        questionDiv.className = 'question card mb-3';
        questionDiv.innerHTML = `
            <div class="card-body">
                <h5 class="card-title">Question ${questionIndex + 1}</h5>
                <div class="form-group mb-3">
                    <label>Question Text:</label>
                    <input type="text" class="form-control" name="questions[${questionIndex}][question_text]" required>
                </div>
                <div class="form-group mb-3">
                    <label>Question Type:</label>
                    <select class="form-control" name="questions[${questionIndex}][question_type]" onchange="toggleOptions(this, ${questionIndex})" required>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                    </select>
                </div>
                <div id="options-${questionIndex}">
                    <div class="form-group mb-3">
                        <label>Options:</label>
                        <input type="text" class="form-control mb-2" name="questions[${questionIndex}][options][]" required>
                        <input type="text" class="form-control mb-2" name="questions[${questionIndex}][options][]" required>
                        <input type="text" class="form-control mb-2" name="questions[${questionIndex}][options][]" required>
                        <input type="text" class="form-control mb-2" name="questions[${questionIndex}][options][]" required>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label>Correct Answer:</label>
                    <select class="form-control" name="questions[${questionIndex}][correct_answer]" required>
                        <option value="0">Option 1</option>
                        <option value="1">Option 2</option>
                        <option value="2">Option 3</option>
                        <option value="3">Option 4</option>
                    </select>
                </div>
            </div>
        `;

        questionsContainer.appendChild(questionDiv);
    });

    function toggleOptions(select, questionIndex) {
        var optionsDiv = document.getElementById(`options-${questionIndex}`);
        var correctAnswerSelect = select.parentNode.nextElementSibling.nextElementSibling.querySelector('select');

        if (select.value === 'true_false') {
            optionsDiv.innerHTML = `
                <div class="form-group mb-3">
                    <label>Options:</label>
                    <input type="text" class="form-control mb-2" name="questions[${questionIndex}][options][]" value="True" readonly required>
                    <input type="text" class="form-control mb-2" name="questions[${questionIndex}][options][]" value="False" readonly required>
                </div>
            `;
            correctAnswerSelect.innerHTML = `
                <option value="0">True</option>
                <option value="1">False</option>
            `;
        } else {
            optionsDiv.innerHTML = `
                <div class="form-group mb-3">
                    <label>Options:</label>
                    <input type="text" class="form-control mb-2" name="questions[${questionIndex}][options][]" required>
                    <input type="text" class="form-control mb-2" name="questions[${questionIndex}][options][]" required>
                    <input type="text" class="form-control mb-2" name="questions[${questionIndex}][options][]" required>
                    <input type="text" class="form-control mb-2" name="questions[${questionIndex}][options][]" required>
                </div>
            `;
            correctAnswerSelect.innerHTML = `
                <option value="0">Option 1</option>
                <option value="1">Option 2</option>
                <option value="2">Option 3</option>
                <option value="3">Option 4</option>
            `;
        }
    }
    </script>
</body>
</html>