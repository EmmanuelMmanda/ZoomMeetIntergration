<?php include 'assets/header.html'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootcamp</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.css">
    <style>
        /* Custom styles for event section */
        #events {
            padding-top: 80px;
            padding-bottom: 40px;
        }
        .event-card {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .event-card:hover {
            background-color: #f8f9fa; /* Light gray background on hover */
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            cursor: pointer;
        }
        /* Style for alert icon */
        .alert-icon {
            width: 40px;
            height: 40px;
            margin-left: 10px;
        }
    </style>
</head>
<body style="margin-top: 70px;">

<!-- Hero -->
<div class="row justify-content-center pb-4" style="background-image: url('assets/image/img.jpg'); background-size: cover; height: 80vh; background-position: center;">
    <div class="container col-md-1 float-left"></div>
    <div class="container col-md-5 float-left" id="title">
        <h1 class="h5">WELCOME TO</h1>
        <h1 class="" style="font-size: 5em;"><b>BOOTCAMP </b></h1> 
        <a href="register.php" class="btn btn-lg bg-white text-primary"><b>Create Account</b></a>
       
    </div>
    <div class="container col-md-4 float-left"></div>
</div>

<!-- Events Added by Admin -->
<div id="events" class="row justify-content-center bg-light pt-4 pb-4">
    <div class="container col-md-8">
        <h1 class="text-center pb-3">Events Added by Admin</h1>

        <!-- Bootstrap Carousel for Event Display -->
        <div id="eventCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">

                <?php
                include 'includes/db.php'; // Adjust path as necessary

                $stmt = $conn->prepare('SELECT * FROM Events ORDER BY event_date DESC, event_time DESC');
                $stmt->execute();
                $events = $stmt->get_result();
                
                $first = true;
                while ($event = $events->fetch_assoc()): ?>
                    <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                        
                        <div class="card event-card">
                                                                                                  <!-- Alert icon next to announcement button -->
        <img src="assets/image/new.gif" alt="New Announcement" class="alert-icon">
                            <div class="card-body">
   


                                <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                                <p class="card-text"><b>Date:</b> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                <p class="card-text"><b>Time:</b> <?php echo htmlspecialchars($event['event_time']); ?></p>
                                <p class="card-text"><b>Location:</b> <?php echo htmlspecialchars($event['location']); ?></p>
                            </div>
       
                        </div>
                    </div>
                    <?php $first = false; ?>
                <?php endwhile; ?>

                <?php if ($events->num_rows === 0): ?>
                    <p class="text-center mt-3">No events currently available.</p>
                <?php endif; ?>
   

            </div>

           
            <button class="carousel-control-prev" type="button" data-bs-target="#eventCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#eventCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
       
        </div>

    </div>
</div>

<!-- About Us -->
<div id="about" class="row justify-content-center pt-4 pb-4">
    <div class="container col-md-2 float-left"></div>
    <div class="container col-md-4 float-left">
        <h1>About Us</h1>
        <h3 class="h5 small"> Hello <span class="text-primary">World!</span></h3>
        <p>
            Our Bootcamp is a training program tailored to teach core skills and prepare students for the professional world, online courses combine theory and practical skills.
            Our Bootcamp is advanced and technical, courses are for beginner or advanced and focus on fundamentals and advanced skills.
        </p>
        <a href="register.php" class="btn btn-md btn-none text-white">Create Account</a>
    </div>
    <div class="container col-md-6 float-left">
        <img src="assets/image/imgb.png" class="img-fluid" height="50%" width="50%">
    </div>
</div>

<!-- What to Expect -->
<div class="row title bg-primary text-white pt-4 pb-4">
    <h1 class="text-center pt-4">What to Expect</h1>
</div>
<div id="courses" class="row justify-content-center bg-primary text-dark pb-5">
    <div class="card col-md-3 float-left m-3 p-3">
        <div class="card-header bg-white text-center">
            <i class="fa fa-desktop fa-2x text-primary"></i>
            <h6 class="pt-2"><b>Feature-Rich Coding Workspace</b></h6>
        </div>
        <div class="card-body text-center">
            At its core, coding is an applied skill. That's why all of our lessons come with a feature-rich coding workspace where you can write, execute, debug, and save code to seamlessly solve our custom problem sets.
        </div>
    </div>

    <!-- Add more cards as needed -->

</div>

<!-- Footer -->
<?php include 'assets/footer.html'; ?>

<script src="assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>
</html>
