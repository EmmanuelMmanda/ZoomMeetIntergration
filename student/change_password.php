<?php
include '../includes/db.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit();
}

$admin_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = 'Please fill in all fields.';
    } elseif ($new_password !== $confirm_password) {
        $message = 'New password and confirm password do not match.';
    } else {
        // Fetch the current password hash from the database
        $stmt = $conn->prepare('SELECT password FROM Users WHERE id = ?');
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (md5($current_password) !== $result['password']) {
            $message = 'Current password is incorrect.';
        } else {
            // Update the password
            $new_password_hashed = md5($new_password);
            $stmt = $conn->prepare('UPDATE Users SET password = ? WHERE id = ?');
            $stmt->bind_param('si', $new_password_hashed, $admin_id);
            if ($stmt->execute()) {
                $message = 'Password changed successfully.';
            } else {
                $message = 'Error changing password. Please try again.';
            }
            $stmt->close();
        }
    }
}

include 'header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Change Password</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="change_password.php" method="post">
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>
