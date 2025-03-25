<?php
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get flight ID from previous page
$flight_id = $_POST['flight_id'] ?? '';

if (empty($flight_id)) {
    die("No flight selected.");
}

// Begin transaction to ensure atomic booking
$conn->begin_transaction();

try {
    // Check flight availability
    $flight_check = $conn->prepare("SELECT seats_no FROM Flights WHERE flight_id = ? FOR UPDATE");
    $flight_check->bind_param("i", $flight_id);
    $flight_check->execute();
    $result = $flight_check->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception("Flight not found.");
    }
    
    $flight = $result->fetch_assoc();
    if ($flight['seats_no'] <= 0) {
        throw new Exception("No seats available on this flight.");
    }

    // Reduce seat count
    $update_seats = $conn->prepare("UPDATE Flights SET seats_no = seats_no - 1 WHERE flight_id = ?");
    $update_seats->bind_param("i", $flight_id);
    $update_seats->execute();

    // Create booking
    $booking_query = $conn->prepare("INSERT INTO Bookings (user_id, flight_id) VALUES (?, ?)");
    $booking_query->bind_param("ii", $_SESSION['user_id'], $flight_id);
    $booking_query->execute();

    // Commit transaction
    $conn->commit();
    
    echo "Flight booked successfully!";
} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();
    echo "Booking failed: " . $e->getMessage();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight Booking Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        .confirmation {
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 8px;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="confirmation">
        <h2>Booking Status</h2>
        <p>Please check the status message above.</p>
        <a href="flight_search.php" class="btn">Back to Search</a>
    </div>
</body>
</html>