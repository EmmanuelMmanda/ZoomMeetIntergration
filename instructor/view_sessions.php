<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit();
}

include ('../includes/db.php');
include ('header.html');

$instructor_id = $_SESSION['user_id'];

// Fetch live sessions for the instructor's courses
$sql = "
    SELECT ls.id, c.title AS course_title, ls.topic, ls.start_url, ls.join_url, ls.scheduled_at, ls.duration 
    FROM LiveSessions ls
    JOIN Courses c ON ls.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY ls.scheduled_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Live Sessions</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <?php
         if($_GET['success'] ?? null) {    
            echo '<p class="alert alert-success">
                Zoom Live Session was Created and Schedule Emails Send to Participants';
            }
                ?>
        </p>
        <h2>Live Sessions</h2>
        <a href="./create_session.php" class="btn btn-primary">Create New </a>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Topic</th>
                        <th>Start URL</th>
                        <th>Join URL</th>
                        <th>Scheduled At</th>
                        <th>Duration (minutes)</th>
                        <th>Status</th> <!-- New column for Status -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        // Convert times to DateTime objects
                        $current_time = new DateTime('now', new DateTimeZone('EAT')); // Adjust timezone as needed
                        $scheduled_time = new DateTime($row['scheduled_at'], new DateTimeZone('EAT')); // Ensure this matches your database timezone
                
                        // Calculate the end time
                        $duration = (int) $row['duration']; // Convert duration to an integer
                        $end_time = clone $scheduled_time;
                        $end_time->modify("+$duration minutes");

                        // Determine session status
                        $status = ($current_time > $end_time) ? 'Expired' : 'Active';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['course_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['topic']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($row['start_url']); ?>" target="_blank">Start</a></td>
                            <td>
                                <?php if ($current_time >= $scheduled_time && $current_time <= $end_time): ?>
                                    <a href="<?php echo htmlspecialchars($row['join_url']); ?>" target="_blank">Join</a>
                                <?php else: ?>
                                    <span>Unavailable</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['scheduled_at']); ?></td>
                            <td><?php echo htmlspecialchars($row['duration']); ?></td>
                            <td><?php echo $status; ?></td> <!-- Display session status -->
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No live sessions available.</div>
        <?php endif; ?>
    </div>


    <script>
        function showAlert() {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'The session is not yet started!',
                footer: 'Please check the scheduled time and try again later.'
            });
        }

        // Event handler for Join Session links
        $(document).on('click', '.join-session', function (e) {
            e.preventDefault();
            var url = $(this).data('url');
            $('#sessionModal').find('iframe').attr('src', url);
            $('#sessionModal').modal('show');
        });
    </script>

    <!-- Modal -->
    <div class="modal fade" id="sessionModal" tabindex="-1" aria-labelledby="sessionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sessionModalLabel">Join Live Session</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe src="" width="100%" height="500px" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>