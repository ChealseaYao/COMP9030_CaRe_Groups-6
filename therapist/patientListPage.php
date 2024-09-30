<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "caredb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the request method and handle accordingly
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if this is a status update request
    if (isset($data['user_id']) && isset($data['status'])) {
        // Handle status update
        $user_id = $data['user_id'];
        $status = $data['status'];

        // Ensure the status is one of the allowed values
        if (in_array($status, ['good status', 'bad status', 'danger status'])) {
            // Update the patient's status in the database
            $sql = "UPDATE patient SET badge = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $status, $user_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }

            $stmt->close();
        } else {
            // Invalid status value
            echo json_encode(['success' => false, 'error' => 'Invalid status']);
        }
        exit;
    }

    // Check if this is a request to fetch group members
    if (isset($data['group_id'])) {
        $group_id = $data['group_id'];

        // Query to get members of the group
        $sql = "SELECT user.full_name 
                FROM group_patient 
                JOIN patient ON group_patient.patient_id = patient.user_id
                JOIN user ON patient.user_id = user.user_id
                WHERE group_patient.group_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $group_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $members = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $members[] = ['name' => $row['full_name']];
            }
        }

        $stmt->close();
        $conn->close();

        // Return the members as JSON
        echo json_encode(['members' => $members]);
        exit;
    }
}

// Continue with the rest of your HTML and page rendering code below
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient List</title>
    <link rel="stylesheet" href="../style/global.css">
    <link rel="stylesheet" href="../style/patientList.css">
</head>
<body class="patientList-body">
    <!-- global navigation bar -->
    <header class="navbar">
        <a href="therapistDashboard.html"><img src="../image/logo.png" alt="Logo Icon" id="logo-icon"></a>
    </header>

    <div class="therapistContainer">
        <div class="leftbox">
            <a href="therapistDashboard.html">
                <button class="back-btn">Back</button>
            </a>
            <h3>Badge</h3>
            <div class="badge-section">
                <div class="badge-item" draggable="true" data-status="good">
                    <span class="status good"></span> Good Status
                </div>
                <div class="badge-item" draggable="true" data-status="bad">
                    <span class="status bad"></span> Bad Status
                </div>
                <div class="badge-item" draggable="true" data-status="danger">
                    <span class="status danger"></span> Danger Status
                </div>
            </div>
        </div>

        <div class="patient-list">
            <div class="nameAndButton">
                <h2>Patient List</h2>
                <form class="search-bar">
                    <input type="text" placeholder="Search..." name="search">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="tableContainer">
                <!-- PHP Code to Fetch and Display Patients Dynamically -->
                <?php
                // Query to get patient data with names and status
                $sql = "SELECT patient.age, patient.badge, user.full_name, patient.user_id
                        FROM patient
                        JOIN user ON patient.user_id = user.user_id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Output HTML for each patient
                        echo '<div class="patient-item" data-user-id="' . $row['user_id'] . '">';
                        echo '<div class="left-section">';
                        echo '<div class="patient-icon">☰</div>';
                        echo '<div>';
                        echo '<strong>' . htmlspecialchars($row['full_name']) . '</strong><br>';
                        echo 'Age: ' . htmlspecialchars($row['age']);
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="right-section">';
                        echo '<div class="status-container">';

                        // Add a span with the correct status color
                        if ($row['badge'] === 'good status') {
                            echo '<span class="status good"></span>';
                        } elseif ($row['badge'] === 'bad status') {
                            echo '<span class="status bad"></span>';
                        } elseif ($row['badge'] === 'danger status') {
                            echo '<span class="status danger"></span>';
                        }

                        echo '</div>';
                        echo '<a href="patientDetail.html"><button class="details">Details</button></a>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>No patients found.</p>";
                }
                ?>
            </div>
        </div>

        <div class="groups">
            <div class="nameAndButton">
                <h2>Groups</h2>
                <button class="create-new">Create New</button>
            </div>
            <div id="groupContainer" class="tableContainer">
                <!-- PHP to display group names dynamically -->
                <?php
                $sql = "SELECT group_id, group_name FROM `group`"; // Assuming your table is named `group`
                $group_result = $conn->query($sql);

                if ($group_result->num_rows > 0) {
                    while ($group_row = $group_result->fetch_assoc()) {
                        echo '<div class="group-item" data-group-id="' . $group_row['group_id'] . '">';
                        echo htmlspecialchars($group_row['group_name']);
                        echo '</div>';
                    }
                } else {
                    echo "<p>No groups found.</p>";
                }
                ?>
            </div>
            <h3>Members</h3>
            <div class="members">
                <p id="currentGroupName">Group Name</p>
                <div id="membersContainer" class="tableContainer">
                    <!-- Dynamic members list -->
                </div>
            </div>
        </div>
    </div>

    <script src="../scripts/createNewModal.js"></script>
    <script src="../scripts//groupSelection.js"></script>
    <script src="../scripts/memberDeletion.js"></script>
    <script src="../scripts/drag&drop.js"></script>

    <footer class="site-footer">
        <p>&copy; 2024 CaRe | All Rights Reserved</p>
    </footer>
</body>
</html>
