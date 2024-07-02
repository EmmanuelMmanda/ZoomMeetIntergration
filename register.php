<?php
include('includes/db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Register'])) {
    $fullName = $_POST['fullName'];
    $gender = $_POST['gender'];
    $college = $_POST['collegeName'];
    $password = md5($_POST['password']);
    $confirmPassword = md5($_POST['confirmPassword']);
    $course_id = $_POST['course']; // Get selected course ID
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phoneNumber = $_POST['phoneNumber'];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match. Please try again.';
    } else {
        // Check if the email already exists
        $stmt = $conn->prepare('SELECT id FROM Users WHERE email = ? OR username = ?');
        $stmt->bind_param('ss', $email, $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'Email already exists. Please try another one.';
        } else {
            // Insert new user into database
            $stmt = $conn->prepare('INSERT INTO Users (username, password, email, fullname, gender, phone_number, address, college, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $role = 'student';
            $stmt->bind_param('sssssssss', $email, $password, $email, $fullName, $gender, $phoneNumber, $address, $college, $role);

            if ($stmt->execute()) {
                // Get the user ID of the newly inserted user
                $user_id = $stmt->insert_id;

                // Insert into Enrollments table
                $stmt = $conn->prepare('INSERT INTO Enrollments (user_id, course_id) VALUES (?, ?)');
                $stmt->bind_param('ii', $user_id, $course_id);
                if ($stmt->execute()) {
                    $success = 'User registered and enrolled successfully!';
                } else {
                    $error = 'Error enrolling user: ' . $stmt->error;
                }
            } else {
                $error = 'Error registering user: ' . $stmt->error;
            }
        }

        $stmt->close();
    }
}

// Retrieve courses from the database
$courses = [];
$result = $conn->query('SELECT id, title FROM Courses');
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>

<?php
include 'assets/header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootcamp | Create Account</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
</head>
<body>
<div class="header bg-primary">
    <div class="row justify-content-center pt-3">
        <div class="container col-md-1"></div>
        <div class="container col-md-8 p-2 pl-4 text-white"><h1><strong>Bootcamp</strong></h1></div>
        <div class="container col-md-3 p-2 pl-3 text-white">
            <a href="index.php" class="btn btn-md btn-none text-white">HOME</a>|
            <a href="login.php" class="btn btn-md btn-none text-white">SIGN IN</a>|
            <a href="register.php" class="btn btn-md btn-none text-white">SIGN UP</a>|
        </div>
    </div>
</div>

<div class="row mt-6 col-md-10 mt-4" style="color:gray;">
    <div class="container col-md-10 float-left">
        <h3 class="text-left"><b>SIGN UP</b></h3>
        
        <?php if (isset($error)): ?>
            <div style="color: red; text-align: center; margin-bottom: 10px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div style="color: green; text-align: center; margin-bottom: 10px;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post">
            <div class="row">
                <div class="col-md-6 float-left">
                    <p><h5 style="color: gray;">Personal Details</h5></p>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Full name</span>
                        </div>
                        <input type="text" class="form-control" placeholder="Enter Full Name" name="fullName" required>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Gender</span>
                        </div>
                        <select class="form-control" name="gender" required>
                            <option value="">-- Select Your Gender --</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">College</span>
                        </div>
                        <input type="text" class="form-control" name="collegeName" placeholder="Enter College" required>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Password</span>
                        </div>
                        <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Confirm Password</span>
                        </div>
                        <input type="password" class="form-control" name="confirmPassword" placeholder="Confirm password" required>
                    </div>
                </div>

                <div class="col-md-6 float-right">
                    <p><h5 style="color: gray;">Course Details</h5></p>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Course</span>
                        </div>
                        <select class="form-control" name="course" required>
                            <option value="">-- Select Course --</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>"><?php echo $course['title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <p class="pt-"><h5 style="color: gray;">Contact Details</h5></p>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Email</span>
                        </div>
                        <input type="email" class="form-control" name="email" placeholder="Eg. name@gmail.com" required>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Address</span>
                        </div>
                        <input type="text" class="form-control" name="address" placeholder="Enter Your Address" required>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Phone</span>
                        </div>
                        <input type="text" class="form-control" name="phoneNumber" placeholder="Eg. 712345678" required>
                    </div>

                    <button class="btn btn-block btn-primary" type="submit" name="Register"><b>CLICK TO SIGN UP</b></button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include 'assets/footer.html'; ?>
</body>
</html>
