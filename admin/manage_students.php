<?php
include '../includes/db.php';

session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}



// Handle delete operation
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM Users WHERE id = ? AND role = "student"');
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param('i', $id);
    if ($stmt->execute() === false) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }
    $stmt->close();
    header('Location: manage_students.php');
    exit();
}

// Fetch students' data
$stmt = $conn->prepare('SELECT id, fullname, email, gender, phone_number, address, college FROM Users WHERE role = "student"');
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->execute();
$result = $stmt->get_result();
?>


<?php
include 'header.html';
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
   
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Manage Students</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>College</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $sn = 1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sn++); ?></td>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['college']); ?></td>
                        <td>
                            <a href="manage_students.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

