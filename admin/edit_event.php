<?php
include '../includes/db.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';

// Fetch event details based on provided event ID
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Fetch event details
    $stmt = $conn->prepare('SELECT * FROM Events WHERE id = ?');
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$event) {
        // Event not found
        header('Location: event_management.php');
        exit();
    }
} else {
    // Redirect if no event ID provided
    header('Location: event_management.php');
    exit();
}

// Handle event update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $location = $_POST['location'] ?? '';

    // Validate inputs (basic validation)
    if (empty($title) || empty($event_date) || empty($event_time) || empty($location)) {
        $message = 'All fields are required.';
    } else {
        // Update event in the database
        $stmt = $conn->prepare('UPDATE Events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ? WHERE id = ?');
        $stmt->bind_param('sssssi', $title, $description, $event_date, $event_time, $location, $event_id);

        if ($stmt->execute()) {
            $message = 'Event updated successfully.';
            // Refresh event details
            $event['title'] = $title;
            $event['description'] = $description;
            $event['event_date'] = $event_date;
            $event['event_time'] = $event_time;
            $event['location'] = $location;
        } else {
            $message = 'Failed to update event. Please try again.';
        }

        $stmt->close();
    }
}

include 'header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Edit Event</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form action="edit_event.php?id=<?php echo $event_id; ?>" method="post">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($event['description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="event_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="event_date" name="event_date" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="event_time" class="form-label">Time</label>
                <input type="time" class="form-control" id="event_time" name="event_time" value="<?php echo htmlspecialchars($event['event_time']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary" name="update_event">Update Event</button>
            <a href="event_management.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>
