<?php
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get flight ID and price from previous page
$flight_id = $_POST['flight_id'] ?? '';
$price = $_POST['price'] ?? 0; // Get the price from the form

if (empty($flight_id)) {
    die("No flight selected.");
}

// Variables to store flight details for display
$flight_details = null;
$booking_status = "";

// Begin transaction to ensure atomic booking
$conn->begin_transaction();

try {
    // Check flight availability and get flight details
    $flight_check = $conn->prepare("SELECT seats_no, price, departure_date, from_airport_code, to_airport_code FROM Flights WHERE flight_id = ? FOR UPDATE");
    $flight_check->bind_param("i", $flight_id);
    $flight_check->execute();
    $result = $flight_check->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception("Flight not found.");
    }
    
    $flight = $result->fetch_assoc();
    $flight_details = $flight; // Store flight details for display
    
    if ($flight['seats_no'] <= 0) {
        throw new Exception("No seats available on this flight.");
    }

    // Reduce seat count
    $update_seats = $conn->prepare("UPDATE Flights SET seats_no = seats_no - 1 WHERE flight_id = ?");
    $update_seats->bind_param("i", $flight_id);
    $update_seats->execute();

    // Create booking - now including the price in the INSERT statement
    $booking_query = $conn->prepare("INSERT INTO Bookings (user_id, flight_id, Price) VALUES (?, ?, ?)");
    $booking_query->bind_param("iii", $_SESSION['user_id'], $flight_id, $price);
    $booking_query->execute();
    $booking_id = $conn->insert_id;

    // Commit transaction
    $conn->commit();
    
    $booking_status = "Flight booked successfully! Your booking ID is #" . $booking_id;
} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();
    $booking_status = "Booking failed: " . $e->getMessage();
    $flight_details = null;
}

// Get additional information for better display if booking successful
if ($flight_details) {
    // Get airport names
    $airports_query = $conn->prepare("SELECT airport_code, City, State FROM Airports WHERE airport_code IN (?, ?)");
    $airports_query->bind_param("ss", $flight_details['from_airport_code'], $flight_details['to_airport_code']);
    $airports_query->execute();
    $airports_result = $airports_query->get_result();
    
    $airports = [];
    while ($airport = $airports_result->fetch_assoc()) {
        $airports[$airport['airport_code']] = $airport;
    }
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

        .header {
            background-color: #4a90e2;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .confirmation {
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .ticket {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: left;
        }
        
        .ticket-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
        }
        
        .ticket-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .label {
            font-weight: bold;
            color: #666;
            font-size: 0.9em;
        }
        
        .value {
            font-size: 1.1em;
        }
        
        .price {
            font-size: 1.2em;
            font-weight: bold;
            color: #4a90e2;
            text-align: right;
            margin-top: 15px;
        }
        
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        
        .error {
            color: #F44336;
            font-weight: bold;
        }
        
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #45a049;
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
    <div class="confirmation">
        <h2>Booking Status</h2>
        <p class="<?php echo strpos($booking_status, 'successfully') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($booking_status); ?>
        </p>
        
        <?php if ($flight_details): ?>
        <div class="ticket">
            <div class="ticket-header">
                <h3>Flight Ticket</h3>
                <div>
                    <span class="label">Booking Date:</span>
                    <span class="value"><?php echo date('M d, Y'); ?></span>
                </div>
            </div>
            <div class="ticket-details">
                <div class="detail-item">
                    <div class="label">From:</div>
                    <div class="value">
                        <?php 
                            if (isset($airports[$flight_details['from_airport_code']])) {
                                echo htmlspecialchars($airports[$flight_details['from_airport_code']]['City'] . ', ' . 
                                     $airports[$flight_details['from_airport_code']]['State'] . ' (' . 
                                     $flight_details['from_airport_code'] . ')'); 
                            } else {
                                echo htmlspecialchars($flight_details['from_airport_code']);
                            }
                        ?>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="label">To:</div>
                    <div class="value">
                        <?php 
                            if (isset($airports[$flight_details['to_airport_code']])) {
                                echo htmlspecialchars($airports[$flight_details['to_airport_code']]['City'] . ', ' . 
                                     $airports[$flight_details['to_airport_code']]['State'] . ' (' . 
                                     $flight_details['to_airport_code'] . ')'); 
                            } else {
                                echo htmlspecialchars($flight_details['to_airport_code']);
                            }
                        ?>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="label">Departure Date:</div>
                    <div class="value"><?php echo date('M d, Y', strtotime($flight_details['departure_date'])); ?></div>
                </div>
                <div class="detail-item">
                    <div class="label">Flight ID:</div>
                    <div class="value"><?php echo htmlspecialchars($flight_id); ?></div>
                </div>
            </div>
            <div class="price">
                Price: ₹<?php echo number_format($flight_details['price'], 2); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <a href="flight_search.php" class="btn">Back to Search</a>
        <?php if ($flight_details): ?>
            <a href="my_bookings.php" class="btn">View My Bookings</a>
        <?php endif; ?>
    </div>
</body>
</html>