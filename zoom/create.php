<?php
session_start();
require '../vendor/autoload.php';
require '../includes/db.php';
use UDX\Zoom\Zoom;
require '../mail/EmailService.php';

// Ensure the user is an instructor and logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit();
}

// Replace placeholders with actual credentials
$accountId = 'CmXayDt2TWC2LFBOCWmGZA';
$clientId = 'ZB1fYKvRly7Nmr0bvI58g';
$clientSecret = '8l50qvRJzlfecyjzz33Zj7E9QOWkNG3b';
$myMeetingEmail = $_SESSION['user_email']; 

// Initialize Zoom client
$zoom = new Zoom($accountId, $clientId, $clientSecret);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $topic = $_POST['topic'];
    $password = $_POST['password'];
    $scheduled_at = $_POST['scheduled_at'];
    $duration = $_POST['duration'];

    try {
        // Prepare meeting data
        $meetingData = [
            'topic' => $topic,
            'start_date' => $scheduled_at,
            'duration' => $duration,
            'password' => $password
        ];

        // Create the meeting using the Zoom API
        $meetingEndpoint = $zoom->meetings();
        $meetingDetails = $meetingEndpoint->create($myMeetingEmail, $meetingData);

        // // Debug: Print the response from the Zoom API
        // echo "Meeting Created: <pre>";
        // print_r($meetingDetails);
        // echo "</pre>";

        
        // Extract necessary data from the associative array
        $meeting_id = $meetingDetails['id'];
        $join_url = $meetingDetails['join_url'];
        $start_url = $meetingDetails['start_url'];

        // // Insert session into the database
        $stmt = $conn->prepare("INSERT INTO LiveSessions (course_id, meeting_id, topic, start_url, join_url, scheduled_at, duration) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssi", $course_id, $meeting_id, $topic, $start_url, $join_url, $scheduled_at, $duration);

        if ($stmt->execute()) {
            //send email to all student in the scheduledclas using there emails ..
            $emailService = new EmailService($conn);
            $emailService->sendEmail($password,$meeting_id,$topic, $join_url, $scheduled_at, $duration,$course_id);

            // redirect to sessions
            header("Location: ../instructor/view_sessions.php?success=Live session created successfully!");
            echo "<div class='alert alert-success mt-3'>Live session created successfully!</div>";
        } else {
            echo "<div class='alert alert-danger mt-3'>Error: " . $stmt->error . "</div>";
        }

        $stmt->close();
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}



$conn->close();
?>
