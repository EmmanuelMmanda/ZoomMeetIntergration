<?php
include '../includes/db.php';

session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM Users WHERE id = ? AND role = "instructor"');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: manage_instructors.php');
    exit();
}

// Retrieve all instructors
$stmt = $conn->prepare('SELECT id, fullname, email, gender, phone_number, address FROM Users WHERE role = "instructor"');
$stmt->execute();
$result = $stmt->get_result();
$instructors = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>



<?php
include 'header.html';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bootcamp | Manage Instructors</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css"> 
</head>
<body>
    <div class="container mt-5">
        <h2 style="text-align:center;">Manage Instructors</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($instructors): ?>
                    <?php foreach ($instructors as $index => $instructor): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($instructor['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($instructor['email']); ?></td>
                            <td><?php echo htmlspecialchars($instructor['gender']); ?></td>
                            <td><?php echo htmlspecialchars($instructor['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($instructor['address']); ?></td>
                            <td>
                                <a href="manage_instructors.php?delete=<?php echo $instructor['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this instructor?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No instructors found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>
