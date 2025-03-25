<?php
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get unique airport locations using City
$airport_query = "SELECT DISTINCT City FROM Airports";
$airport_result = $conn->query($airport_query);

// Check if query was successful
if (!$airport_result) {
    die("Error in query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight Search</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ABD2FA;
        }
        /* .header {
            background-color: #4a90e2;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        } */
        .search-form {
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 8px;
        }
        .search-form select, 
        .search-form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }
        .flight-results {
            margin-top: 20px;
        }
        .flight-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- <header class="header">
        <div class="logo">SkyConnect</div>
        <nav class="nav">
            <a href="index.php" class="nav-link">Home</a>
            <a href="login.php" class="nav-link">User Login</a>
            <a href="company_login.php" class="nav-link">Company Login</a>
            <a href="#about" class="nav-link">About</a>
        </nav>
    </header> -->
    <div class="search-form">
        <h2>Search Flights</h2>
        <form method="POST" action="flight_results.php">
            <label for="from_location">From:</label>
            <select name="from_location" required>
                <option value="">Select Departure Location</option>
                <?php
                // Reset pointer
                mysqli_data_seek($airport_result, 0);
                $unique_locations = [];
                while ($row = $airport_result->fetch_assoc()) {
                    $location = $row['City'];
                    if (!in_array($location, $unique_locations)) {
                        $unique_locations[] = $location;
                        echo "<option value='" . htmlspecialchars($location) . "'>" 
                            . htmlspecialchars($location) . "</option>";
                    }
                }
                ?>
            </select>
            <br>
            <br>

            <label for="to_location">To:</label>
            <select name="to_location" required>
                <option value="">Select Destination</option>
                <?php
                // Reset pointer
                mysqli_data_seek($airport_result, 0);
                $unique_locations = [];
                while ($row = $airport_result->fetch_assoc()) {
                    $location = $row['City'];
                    if (!in_array($location, $unique_locations)) {
                        $unique_locations[] = $location;
                        echo "<option value='" . htmlspecialchars($location) . "'>" 
                            . htmlspecialchars($location) . "</option>";
                    }
                }
                ?>
            </select>
            <br>
            <br>

            <label for="flight_date">Date:</label>
            <input type="date" name="flight_date" required>
            <br>
            <br>

            <input type="submit" value="Search Flights">
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>