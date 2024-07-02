<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Path to autoload.php for PHPMailer
require '../includes/db.php';

class EmailService
{
    private $mail;
    private $db;

    public function __construct($conn)
    {
        $this->mail = new PHPMailer(true);
        $this->db = $conn;
        $this->configureSMTP();
    }

    private function configureSMTP()
    {
        $this->mail->isSMTP();
        $this->mail->Host = 'mail.vmpower.co.tz'; // Change this to your SMTP host
        $this->mail->Port = 587; // Change this to your SMTP port (e.g., 587 for TLS, 465 for SSL)
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'info@vmpower.co.tz'; // Change this to your SMTP username
        $this->mail->Password = 'Info@2024'; // Change this to your SMTP password
        $this->mail->SMTPSecure = 'tls'; // Change this to 'ssl' or 'tls' based on your SMTP server configuration
    }

    public function sendEmail($meetingid, $course, $joinLink, $scheduledAt, $duration, $course_id)
    {


        $getRecipientEmails = $this->getRecipientEmail($course_id);
        if (empty($getRecipientEmails)) {
            return $this->returnResponse(500, "No recipient emails.");
        }

        try {
            foreach ($getRecipientEmails as $recipient) {
                $this->mail->clearAddresses(); // Clear previous addresses
                $this->mail->setFrom('info@vmpower.co.tz', 'Online BootCamp');
                $this->mail->addAddress($recipient); // Add recipient email address
                $this->mail->isHTML(true);
                $this->mail->Subject = 'Zoom Meeting Schedule';
                $this->mail->Body = $this->buildEmailBody($meetingid, $course, $joinLink, $scheduledAt, $duration);
                $this->mail->send();
            }
            return $this->returnResponse(200, "Sent successfully! to -> " . json_encode($getRecipientEmails));
        } catch (Exception $e) {
            return $this->returnResponse(500, "Failed to send email. " . $this->mail->ErrorInfo);
        }
    }

    private function buildEmailBody($meetingid, $course, $joinLink, $scheduledAt, $duration)
    {
        return "
        <html>
        <head>
            <title>NEW MEETING ALERT!</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    padding: 20px;
                    margin: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #fff;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                h1 {
                    color: #333;
                    text-align: center;
                }
                p {
                    margin: 0 0 20px;
                }
                strong {
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <h1>New Meeting Created!</h1>
                <br>
                <p><strong>Meeting ID:</strong> " . htmlspecialchars($meetingid) . "</p>
                <p><strong>Course:</strong> " . htmlspecialchars($course) . "</p>
                <p><strong>Join Link:</strong> <a href=\"" . htmlspecialchars($joinLink) . "\">" . htmlspecialchars($joinLink) . "</a></p>
                <p><strong>Scheduled at:</strong> " . htmlspecialchars($scheduledAt) . "</p>
                <p><strong>Duration:</strong> " . htmlspecialchars($duration) . "</p>
            </div>
        </body>
        </html>
        ";
    }

    private function returnResponse($status, $text)
    {
        // Set the HTTP status code
        http_response_code($status);

        // Create an array with status and text
        $response = ['status' => $status, 'text' => $text];

        // Convert the array to JSON and output
        echo json_encode($response);
    }


    private function getRecipientEmail($course_id, )
    {
        try {
            $emails = array();

            // Escape $course_id to prevent SQL injection
            $course_id = $this->db->real_escape_string($course_id);

            $query = "
                SELECT u.email 
                FROM enrollments e
                JOIN users u ON e.user_id = u.id 
                WHERE e.course_id = '$course_id'
            ";

            $result = $this->db->query($query);

            if ($result) {
                // Check if there are rows returned
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $emails[] = $row['email'];
                    }
                }
                $result->free_result();
            }
            return $emails;

        } catch (Exception $e) {
            error_log("Database query error: " . $e->getMessage()); // Log the error
            return null;
        }
    }
}
?>