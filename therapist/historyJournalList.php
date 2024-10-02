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

$patient_id = $_GET['patient_id'];

$sql = "SELECT journal_id, journal_date, journal_content, highlight 
        FROM journal 
        WHERE patient_id = ? 
        ORDER BY journal_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Wenqiang Jin">
    <meta name="description" content="therapist of COMP9030_CaRe_Groups6">
    <title>Therapist - History Journal List</title>
    <script src="../scripts/datepicker.js"></script>
    <script src="../scripts/historyJournalList.js"></script>
    <link rel="stylesheet" href="../style/global.css">
    <link rel="stylesheet" href="../style/historyJournal.css">
</head>

<body class="therapistBody">
    <!-- global navigation bar TBD -->
    <header class="navbar">
        <a href="therapistDashboard.html"><img src="../image/logo.png" alt="Logo Icon" id="logo-icon"></a>
    </header>
    <div class="therapistContainer">
        <div class="leftbox">
            <a href="patientDetail.html">
                <button class="back-btn">Back</button>
            </a>
        </div>
        <div class="centre">
            <h1>
                <!-- John Smith should be user's Full name -->
                History Journals of John Smith
            </h1>
            <div id="historyJournal">
                <div class="searchPannel">
                    <form class="search-bar">
                        <input type="text" placeholder="Search..." name="search" />
                        <button type="submit">Search</button>
                    </form>
                    <div class="date-picker-container">
                        <label for="date-range-input" id="history-note"></label>
                        <span id="date-icon">
                            <img src="../image/calendar.png" alt="Calendar Icon">
                        </span>
                        <div class="calendar-popup">
                            <label for="year">Year:</label>
                            <select id="year"></select>
        
                            <label for="month">Month:</label>
                            <select id="month"></select>
        
                            <label for="day">Day:</label>
                            <select id="day"></select>
        
                            <button id="confirm-btn">Confirm</button>
                        </div>
                    </div>
                </div>

                <div class="tableContainer">
                    <!-- click journal title will open that journal in new page -->
                    <table class="historyJournal-table thearpistHJT">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Content</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            // Fetch and display journal data
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $journal_id = $row['journal_id'];
                                    $journal_content = $row['journal_content'];
                                    $journal_date = $row['journal_date'];
                                    $highlight = $row['highlight'];
                                    
                                    // Convert journal_date to a more readable format
                                    $formattedDate = date("d/m/Y", strtotime($journal_date));
                                    
                                    echo "<tr>";
                                    echo "<td class='star'>" . ($highlight ? "★" : "") . "</td>";
                                    echo "<td><a href='journalDetail.php?journal_id=$journal_id'>$journal_content</a></td>";
                                    echo "<td>$formattedDate</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No journals found.</td></tr>";
                            }

                            // Close the statement and connection
                            $stmt->close();
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="rightbox">

        </div>
    </div>
    <footer class="site-footer">
        <p>&copy; 2024 CaRe | All Rights Reserved</p>
    </footer>
</body>

</html>