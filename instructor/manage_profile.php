<?php
include '../includes/db.php';
session_start();

// Check if the user is logged in and is an instructor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit();
}

$instructor_id = $_SESSION['user_id'];
$message = '';

// Fetch current instructor details
$stmt = $conn->prepare('SELECT * FROM Users WHERE id = ?');
$stmt->bind_param('i', $instructor_id);
$stmt->execute();
$instructor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $address = $_POST['address'] ?? '';

    if (empty($email)) {
        $message = 'Email is required.';
    } else {
        // Check if email already exists for another user
        $stmt = $conn->prepare('SELECT id FROM Users WHERE email = ? AND id != ?');
        $stmt->bind_param('si', $email, $instructor_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = 'Email is already in use.';
        } else {
            // Update instructor details
            $stmt->close();
            $stmt = $conn->prepare('UPDATE Users SET email = ?, fullname = ?, gender = ?, phone_number = ?, address = ? WHERE id = ?');
            $stmt->bind_param('sssssi', $email, $fullname, $gender, $phone_number, $address, $instructor_id);

            if ($stmt->execute()) {
                $message = 'Profile updated successfully.';
                // Refresh the instructor details
                $stmt->close();
                $stmt = $conn->prepare('SELECT * FROM Users WHERE id = ?');
                $stmt->bind_param('i', $instructor_id);
                $stmt->execute();
                $instructor = $stmt->get_result()->fetch_assoc();
            } else {
                $message = 'Error updating profile. Please try again.';
            }
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
    <title>Manage Profile</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Manage Profile</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="manage_profile.php" method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($instructor['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="fullname" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($instructor['fullname']); ?>">
            </div>
            <div class="mb-3">
                <label for="gender" class="form-label">Gender</label>
                <select class="form-control" id="gender" name="gender">
                    <option value="male" <?php echo $instructor['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo $instructor['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                    
                </select>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($instructor['phone_number']); ?>">
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address"><?php echo htmlspecialchars($instructor['address']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>
