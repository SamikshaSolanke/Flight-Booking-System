<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
// $conn = new mysqli("localhost", "root", "Samruddhi@09", "DBMS_PROJECT");
include 'db_connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user's bookings
$bookings_query = "
    SELECT 
        b.booking_id, 
        b.booking_date, 
        f.departure_date, 
        c.company_name, 
        a1.airport_code as from_code, 
        a1.City as from_place,
        a2.airport_code as to_code,
        a2.City as to_place
    FROM 
        Bookings b
    JOIN 
        Flights f ON b.flight_id = f.flight_id
    JOIN 
        Companies c ON f.company_id = c.company_id
    JOIN 
        Airports a1 ON f.from_airport_code = a1.airport_code
    JOIN 
        Airports a2 ON f.to_airport_code = a2.airport_code
    WHERE 
        b.user_id = ?
    ORDER BY 
        b.booking_date DESC
";

$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Check for cancel action
if (isset($_GET['cancel_success'])) {
    $message = "Your booking has been successfully cancelled.";
    $alert_class = "success";
} elseif (isset($_GET['cancel_error'])) {
    $message = "Error cancelling your booking. Please try again.";
    $alert_class = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ABD2FA;
            margin: 0;
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

        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }
        .booking-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }
        .booking-details {
            display: flex;
            justify-content: space-between;
        }
        .no-bookings {
            text-align: center;
            color: #666;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #4a90e2;
            text-decoration: none;
        }
        .cancel-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 14px;
        }
        .cancel-button:hover {
            background-color: #c0392b;
        }
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 5px;
        }
        .modal-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .confirm-cancel {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .cancel-modal {
            background-color: #95a5a6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
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
    <br>
    <br>
    <div class="container">
        <h1>My Flight Bookings</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert <?php echo $alert_class; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while ($booking = $result->fetch_assoc()): ?>
                <div class="booking-card">
                    <div class="booking-details">
                        <div>
                            <strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['booking_id']); ?>
                        </div>
                        <div>
                            <strong>Booking Date:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?>
                        </div>
                    </div>
                    <div class="booking-details">
                        <div>
                            <strong>Airline:</strong> <?php echo htmlspecialchars($booking['company_name']); ?>
                        </div>
                        <div>
                            <strong>Flight Date:</strong> <?php echo htmlspecialchars($booking['departure_date']); ?>
                        </div>
                    </div>
                    <div class="booking-details">
                        <div>
                            <strong>From:</strong> <?php echo htmlspecialchars($booking['from_place']); ?> (<?php echo htmlspecialchars($booking['from_code']); ?>)
                        </div>
                        <div>
                            <strong>To:</strong> <?php echo htmlspecialchars($booking['to_place']); ?> (<?php echo htmlspecialchars($booking['to_code']); ?>)
                        </div>
                    </div>
                    <div class="actions">
                        <button class="cancel-button" onclick="showCancelModal(<?php echo htmlspecialchars($booking['booking_id']); ?>)">Cancel Booking</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-bookings">
                <p>You have no flight bookings yet.</p>
            </div>
        <?php endif; ?>
        
        <a href="flight_search.php" class="back-link">Book a New Flight</a>
        <a href="login.php" class="back-link">Back to Dashboard</a>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <h2>Cancel Booking</h2>
            <p>Are you sure you want to cancel this booking? This action cannot be undone.</p>
            <div class="modal-buttons">
                <form id="cancelForm" action="cancel_booking.php" method="POST">
                    <input type="hidden" id="booking_id" name="booking_id" value="">
                    <button type="submit" class="confirm-cancel">Yes, Cancel Booking</button>
                </form>
                <button class="cancel-modal" onclick="hideCancelModal()">No, Keep Booking</button>
            </div>
        </div>
    </div>

    <script>
        function showCancelModal(bookingId) {
            document.getElementById('booking_id').value = bookingId;
            document.getElementById('cancelModal').style.display = 'block';
        }

        function hideCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }

        // Close modal if user clicks outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById('cancelModal')) {
                hideCancelModal();
            }
        }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>