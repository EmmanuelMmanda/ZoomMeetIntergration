<?php
session_start();
include('../includes/db.php');

// Ensure the user is an instructor
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'instructor') {
    header('Location: ../login.php');
    exit();
}

$instructor_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'] ?? null;
    $type = $_POST['type'] ?? null;
    $url = '';

    // Handle file upload
    if ($type != 'link') {
        if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] == UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['resource_file']['tmp_name'];
            $file_name = $_FILES['resource_file']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Allowed file extensions
            $allowed_exts = ['pdf', 'mp4', 'mkv', 'avi', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'jpeg', 'jpg', 'png', 'xls', 'xlsx', 'zip', 'rar'];

            if (in_array($file_ext, $allowed_exts)) {
                $upload_dir = '../uploads/';
                // Ensure the upload directory exists
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $dest_path = $upload_dir . basename($file_name);

                if (move_uploaded_file($file_tmp_path, $dest_path)) {
                    $url = $dest_path;
                } else {
                    $message = 'Error moving the uploaded file.';
                }
            } else {
                $message = 'Invalid file type. Allowed types: ' . implode(', ', $allowed_exts);
            }
        } else {
            $message = 'No file uploaded or there was an upload error.';
        }
    } else {
        $url = $_POST['url'] ?? '';
    }

    // Validate inputs
    if (empty($course_id) || empty($type) || empty($url)) {
        $message = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("INSERT INTO Resources (course_id, type, url) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $course_id, $type, $url);

        if ($stmt->execute()) {
            $message = 'Resource added successfully!';
        } else {
            $message = 'Error adding resource.';
        }

        $stmt->close();
    }
}

// Fetch courses assigned to the instructor
$sql = "SELECT * FROM Courses WHERE instructor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>
<?php
include('header.html')
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Resource</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Add Resource</h2>
        <?php if ($message): ?>
            <script>
                Swal.fire({
                    title: 'Notification',
                    text: '<?php echo $message; ?>',
                    icon: '<?php echo $message == "Resource added successfully!" ? "success" : "error"; ?>',
                    confirmButtonText: 'OK'
                });
            </script>
        <?php endif; ?>
        <form action="add_resource.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="course_id">Course</label>
                <select name="course_id" id="course_id" class="form-control" required>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo $course['title']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="type">Type</label>
                <select name="type" id="type" class="form-control" required onchange="toggleFileInput()">
                    <option value="">Select Type</option>
                    <option value="note">Notes</option>
                    <option value="tutorial">Tutorial</option>
                    <option value="video">Video</option>
                    <option value="link">Link</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group" id="file_input_group" style="display: none;">
                <label for="resource_file">Upload File</label>
                <input type="file" name="resource_file" id="resource_file" class="form-control">
            </div>
            <div class="form-group" id="url_input_group" style="display: none;">
                <label for="url">URL</label>
                <input type="text" name="url" id="url" class="form-control" placeholder="Enter URL">
            </div>
            <button type="submit" class="btn btn-primary">Add Resource</button>
        </form>
    </div>
    <script>
        function toggleFileInput() {
            var type = document.getElementById('type').value;
            var fileInputGroup = document.getElementById('file_input_group');
            var urlInputGroup = document.getElementById('url_input_group');

            if (type === 'link') {
                fileInputGroup.style.display = 'none';
                urlInputGroup.style.display = 'block';
            } else {
                fileInputGroup.style.display = 'block';
                urlInputGroup.style.display = 'none';
            }
        }
    </script>
</body>
</html>
