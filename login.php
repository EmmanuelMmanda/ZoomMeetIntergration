<?php
include ('includes/db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Log'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    // Fetch user data from database
    $stmt = $conn->prepare('SELECT id, email, username, password, role FROM Users WHERE email = ? OR username = ?');
    $stmt->bind_param('ss', $email, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $emailFromDB, $username, $hashed_password, $role);
        $stmt->fetch();

        // Debugging: Output hashed password and input password
        // echo "Hashed password from DB: " . htmlspecialchars($hashed_password) . "<br>";
        // echo "Password entered (hashed): " . htmlspecialchars($password) . "<br>";

        // Use password_verify to compare the entered password with the hashed password
        if ($password === $hashed_password) {
            session_start(); // Start the session if not already started
            $_SESSION['user_id'] = $id;
            $_SESSION['user_email'] = $emailFromDB;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            // Redirect based on role
            switch ($role) {
                case 'admin':
                    header('Location: admin/manage_students.php');
                    break;
                case 'instructor':
                    header('Location: instructor/view_students.php');
                    break;
                case 'student':
                    header('Location: student/take_exam.php');
                    break;
                default:
                    header('Location: login.php');
            }
            exit();
        } else {
            $error = 'Invalid password. Please try again.';
        }
    } else {
        $error = 'No account found with that email or username.';
    }
    $stmt->close();
}

include 'assets/header.html';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="row">
        <div class="container" style="width: 350px;">
            <div class="card-body"
                style="background-color:white; margin-top:100px; border-radius: 2%;border: 1px solid gray;">
                <h1 style="text-align:center; color:gray;">Login</h1>
                <?php if (isset($error)): ?>
                    <div style="color: red; text-align: center; margin-bottom: 10px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <form action="" method="post">
                    <div class="col-12">
                        <input type="email" name="email" placeholder="Enter email" class="form-control" required>
                    </div>
                    <div class="col-12 my-4">
                        <input type="password" name="password" placeholder="Enter password" class="form-control"
                            required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="form-control col-md-12 btn-primary" style="color:white;"
                            name="Log">LOGIN</button>
                    </div>
                    <div style="margin-top: 2%;">
                        You don't have an account?
                        <a href="register.php" class="btn btn-sm btn-primary">Create Account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'assets/footer.html'; ?>
</body>

</html>