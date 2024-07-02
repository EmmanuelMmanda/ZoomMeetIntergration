<?php
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $file_path = '';

    // File upload handling
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $allowed_ext = array('pdf', 'doc', 'docx', 'ppt', 'pptx');
        $file_ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($file_ext), $allowed_ext)) {
            // Ensure uploads directory exists and is writable
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = basename($_FILES['file']['name']);
            $file_path = $upload_dir . $file_name;

            if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                die('Error uploading file.');
            }
        } else {
            die('Invalid file type.');
        }
    }

    $sql = "INSERT INTO Assignments (course_id, title, description, due_date, file_path) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issss', $course_id, $title, $description, $due_date, $file_path);

    if ($stmt->execute()) {
        echo json_encode(array('status' => 'success', 'title' => $title));
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Error adding assignment.'));
    }

    $stmt->close();
    $conn->close();
}
?>
