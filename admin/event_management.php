<?php
include '../includes/db.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';

// Handle event addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $location = $_POST['location'] ?? '';

    // Validate inputs (basic validation)
    if (empty($title) || empty($event_date) || empty($event_time) || empty($location)) {
        $message = 'All fields are required.';
    } else {
        // Insert event into database
        $stmt = $conn->prepare('INSERT INTO Events (title, description, event_date, event_time, location) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $title, $description, $event_date, $event_time, $location);

        if ($stmt->execute()) {
            $message = 'Event added successfully.';
        } else {
            $message = 'Failed to add event. Please try again.';
        }

        $stmt->close();
    }
}

// Handle event deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'] ?? '';

    if (!empty($event_id)) {
        // Delete event from database
        $stmt = $conn->prepare('DELETE FROM Events WHERE id = ?');
        $stmt->bind_param('i', $event_id);

        if ($stmt->execute()) {
            $message = 'Event deleted successfully.';
        } else {
            $message = 'Failed to delete event. Please try again.';
        }

        $stmt->close();
    } else {
        $message = 'Event ID is missing.';
    }
}

// Fetch all events
$stmt = $conn->prepare('SELECT * FROM Events ORDER BY event_date DESC, event_time DESC');
$stmt->execute();
$events = $stmt->get_result();
$stmt->close();

include 'header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Management</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Event Management</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">Add New Event</button>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($event = $events->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                        <td><?php echo htmlspecialchars($event['description']); ?></td>
                        <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($event['event_time']); ?></td>
                        <td><?php echo htmlspecialchars($event['location']); ?></td>
                        <td>
                            <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <form action="" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <button type="submit" name="delete_event" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="event_management.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addEventModalLabel">Add New Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="event_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="event_date" name="event_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="event_time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="event_time" name="event_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="add_event">Add Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>
