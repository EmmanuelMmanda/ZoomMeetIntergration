<?php
include('../includes/db.php');

// Fetch resources from the database
$sql = "SELECT Resources.*, Courses.title AS course_title FROM Resources JOIN Courses ON Resources.course_id = Courses.id";
$result = $conn->query($sql);
?>

<?php
include('header.html')
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Resources</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .resource-card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: none;
        }
        .resource-card .card-body {
            padding: 20px;
            border-radius: 10px;
        }
        .resource-link {
            text-decoration: none;
            color: #007bff;
        }
        .resource-link:hover {
            text-decoration: underline;
        }
        .resource-type {
            font-size: 0.9em;
            color: #6c757d;
        }
        .resource-note .card-body {
            background-color: #f9f9f9;
            border-left: 5px solid #17a2b8;
        }
        .resource-tutorial .card-body {
            background-color: #fff9f2;
            border-left: 5px solid #ffc107;
        }
        .resource-video .card-body {
            background-color: #f2f9ff;
            border-left: 5px solid #007bff;
        }
        .resource-other .card-body {
            background-color: #f8f8f8;
            border-left: 5px solid #6c757d;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Available Resources</h2>
        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $resourceType = ucfirst($row['type']);
                    $resourceUrl = htmlspecialchars($row['url']);
                    $courseTitle = htmlspecialchars($row['course_title']);
                    $resourceClass = "resource-" . strtolower($row['type']);
                    echo "
                    <div class='col-md-4'>
                        <div class='card resource-card $resourceClass'>
                            <div class='card-body'>
                                <h5 class='card-title'>$courseTitle</h5>
                                <p class='resource-type'>Type: $resourceType</p>";
                    if ($row['type'] == 'video') {
                        echo "<a href='$resourceUrl' class='resource-link' target='_blank'>Watch Video</a>";
                    } elseif ($row['type'] == 'note' || $row['type'] == 'tutorial') {
                        echo "<a href='$resourceUrl' class='resource-link' target='_blank'>View Resource</a>";
                    } else {
                        echo "<a href='$resourceUrl' class='resource-link' target='_blank'>Open Link</a>";
                    }
                    echo "
                            </div>
                        </div>
                    </div>";
                }
            } else {
                echo "<p>No resources available.</p>";
            }
            $conn->close();
            ?>
        </div>
    </div>
    <script>
        // JavaScript code for additional functionality (if needed)
    </script>
</body>
</html>
