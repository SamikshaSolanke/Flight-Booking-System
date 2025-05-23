<?php
session_start();
include 'db_connection.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Get form data
$from_location = $_POST['from_location'] ?? '';
$to_location = $_POST['to_location'] ?? '';
$flight_date = $_POST['flight_date'] ?? '';
// Validate inputs
if (empty($from_location) || empty($to_location) || empty($flight_date)) {
    die("Please fill all search criteria.");
}
// Query to find matching flights
$flight_query = "SELECT f.flight_id, c.company_name, f.departure_date, f.seats_no, f.Price,
                a1.airport_code as from_code, a2.airport_code as to_code,
                a1.City as from_city, a2.City as to_city
                FROM Flights f
                JOIN Companies c ON f.company_id = c.company_id
                JOIN Airports a1 ON f.from_airport_code = a1.airport_code
                JOIN Airports a2 ON f.to_airport_code = a2.airport_code
                WHERE a1.City = ? AND a2.City = ? AND DATE(f.departure_date) = ?
                AND f.seats_no > 0";
$stmt = $conn->prepare($flight_query);
$stmt->bind_param("sss", $from_location, $to_location, $flight_date);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4a90e2;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .flight-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .book-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            text-decoration: none;
            cursor: pointer;
        }
        .price {
            font-weight: bold;
            font-size: 1.2em;
            color: #e67e22;
            margin-top: 8px;
        }
        .flight-details {
            flex: 1;
        }
        .booking-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">SkyConnect</div>
        <nav class="nav">
            <a href="index.php" class="nav-link">Home</a>
            <a href="login.php" class="nav-link">User Login</a>
            <a href="company_login.php" class="nav-link">Company Login</a>
            <a href="#about" class="nav-link">About</a>
        </nav>
    </header>
    <h2>Available Flights</h2>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($flight = $result->fetch_assoc()): ?>
            <div class="flight-card">
                <div class="flight-details">
                    <h3><?php echo htmlspecialchars($flight['company_name']); ?></h3>
                    <p>From: <?php echo htmlspecialchars($flight['from_city'] . ' (' . $flight['from_code'] . ')'); ?></p>
                    <p>To: <?php echo htmlspecialchars($flight['to_city'] . ' (' . $flight['to_code'] . ')'); ?></p>
                    <p>Date: <?php echo htmlspecialchars($flight['departure_date']); ?></p>
                    <p>Available Seats: <?php echo htmlspecialchars($flight['seats_no']); ?></p>
                </div>
                <div class="booking-section">
                    <div class="price">₹<?php echo htmlspecialchars($flight['Price']); ?></div>
                    <form method="POST" action="book_flight.php">
                        <input type="hidden" name="flight_id" value="<?php echo $flight['flight_id']; ?>">
                        <input type="hidden" name="price" value="<?php echo $flight['Price']; ?>">
                        <input type="submit" value="Book" class="book-btn">
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No flights found for the selected criteria.</p>
    <?php endif; ?>
    <a href="flight_search.php">Back to Search</a>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>