<?php
include '../includes/db.php';

session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Initialize messages
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phoneNumber = $_POST['phoneNumber'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $gender = $_POST['gender'];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $error_message = 'Passwords do not match!';
    } else {
        // Check for duplicate username or email
        $stmt = $conn->prepare('SELECT COUNT(*) FROM Users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $email, $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error_message = 'Username or Email already exists!';
        } else {
            // Hash the password
            $hashedPassword = md5($password);

            // Prepare and execute the SQL statement
            $stmt = $conn->prepare('INSERT INTO Users (username, password, email, fullname, gender, phone_number, address, college, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssssssss', $email, $hashedPassword, $email, $fullName, $gender, $phoneNumber, $address, $college, $role);
            $role = 'instructor';
            $college = 'N/A'; // Adjust as necessary
            if ($stmt->execute()) {
                $success_message = 'Instructor registered successfully!';
            } else {
                $error_message = 'Error: Could not register instructor. Please try again.';
            }
            $stmt->close();
        }
    }
}
?>

<?php

include 'header.html';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bootcamp | Register Instructor</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css"> 
</head>
<body>
    <div class="container mt-5">
        <h2 style="text-align:center;">Register Instructor</h2>

        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text">Full Name</span>
                </div>
                <input type="text" class="form-control" name="fullName" placeholder="Enter Full Name" required>
            </div>

            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text">Email</span>
                </div>
                <input type="email" class="form-control" name="email" placeholder="example@gmail.com" required>
            </div>

            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text">Address</span>
                </div>
                <input type="text" class="form-control" name="address" placeholder="Enter Address" required>
            </div>

            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text">Phone</span>
                </div>
                <input type="text" class="form-control" name="phoneNumber" placeholder="06234..." required>
            </div>


            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text">Gender</span>
                </div>
                <select class="form-control" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>

            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text">Password</span>
                </div>
                <input type="password" class="form-control" name="password" placeholder="Enter Password" required>
            </div>

            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text">Confirm Password</span>
                </div>
                <input type="password" class="form-control" name="confirmPassword" placeholder="Confirm Password" required>
            </div>

          

            <div class="input-group mb-3">


        <!--INASAIDIA BUTTON ISIWE JUU YA SISE BAR -->
                <button style="z-index: 0;" type="submit" name="Register" class="btn btn-success btn-block">Add Instructor</button>
            </div>
        </form>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>
