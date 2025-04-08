<?php
// Start the session
session_start();

// Check if the user is logged in as a company
if (!isset($_SESSION['company_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Database connection
include 'db_connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get company information
$company_id = $_SESSION['company_id'];
$company_name = $_SESSION['company_name'];

// Get all flights for this company
$flights_query = "
    SELECT f.flight_id,
           f.departure_date,
           f.seats_no, 
           f.price,
           a1.airport_code as from_code,
           a1.City as from_place,
           a1.State as from_state,
           a2.airport_code as to_code,
           a2.City as to_place,
           a2.State as to_state,
           COALESCE(b.booked_seats, 0) as booked_seats,
           COALESCE(b.booking_revenue, 0) as booking_revenue
    FROM Flights f
    JOIN Airports a1 ON f.from_airport_code = a1.airport_code
    JOIN Airports a2 ON f.to_airport_code = a2.airport_code
    LEFT JOIN (
        SELECT flight_id, COUNT(*) as booked_seats, SUM(price) as booking_revenue
        FROM Bookings 
        GROUP BY flight_id
    ) b ON f.flight_id = b.flight_id
    WHERE f.company_id = ?
    ORDER BY f.departure_date ASC";

$stmt = $conn->prepare($flights_query);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();

// Count total flights
$total_flights = $result->num_rows;

// Get upcoming flights count
$upcoming_flights_query = "
    SELECT COUNT(*) as count
    FROM Flights
    WHERE company_id = ? AND departure_date >= CURDATE()";

$stmt = $conn->prepare($upcoming_flights_query);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$upcoming_result = $stmt->get_result();
$upcoming_flights = $upcoming_result->fetch_assoc()['count'];

// Calculate total seats, bookings, and revenue
$total_seats = 0;
$total_bookings = 0;
$total_revenue = 0;
$booking_rate = 0;

// Create an array to store flights data for reuse
$flights = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $flights[] = $row;
        $total_seats += $row['seats_no'];
        $total_bookings += $row['booked_seats'];
        $total_revenue += $row['booking_revenue'];
    }
    
    // Calculate overall booking rate
    if ($total_seats > 0) {
        $booking_rate = round(($total_bookings / $total_seats) * 100, 2);
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #ABD2FA;
        }

        .header {
            background-color: #091540;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: #091540;
            color: #fff;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo h1 {
            margin: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info p {
            margin-right: 15px;
        }
        
        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .dashboard-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .summary-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
        }
        
        .summary-card h2 {
            margin-top: 0;
            font-size: 2rem;
            color: #333;
        }
        
        .summary-card p {
            color: #777;
            margin-bottom: 0;
        }
        
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            margin: 20px 0;
        }
        
        .add-flight-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }
        
        .add-flight-btn:hover {
            background-color: #45a049;
        }
        
        .flights-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .flights-table th, 
        .flights-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .flights-table th {
            background-color: #f8f8f8;
            color: #333;
            font-weight: bold;
        }
        
        .flights-table tr:last-child td {
            border-bottom: none;
        }
        
        .flights-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .booking-status {
            display: flex;
            align-items: center;
        }
        
        .booking-bar {
            flex-grow: 1;
            height: 15px;
            background-color: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            margin-right: 10px;
        }
        
        .booking-progress {
            height: 100%;
            background-color: #4CAF50;
        }
        
        .booking-percentage {
            width: 50px;
            text-align: right;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .empty-state p {
            color: #777;
            margin-bottom: 20px;
        }
        
        .action-cell {
            white-space: nowrap;
        }
        
        .edit-btn {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 5px;
        }
        
        .cancel-btn {
            background-color: #ff9800;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 5px;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .date-past {
            color: #dc3545;
        }
        
        .date-future {
            color: #28a745;
        }
        
        .home a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
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
            border-radius: 8px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-actions {
            text-align: right;
            margin-top: 20px;
        }
        
        .form-actions button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .form-actions .cancel {
            background-color: #ccc;
            margin-right: 10px;
        }
        
        .form-actions .save {
            background-color: #4CAF50;
            color: white;
        }
        
        .price-column {
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <h1>Flight Management System</h1>
        </div>
        <div class="home">
            <a href="index.php" class="nav-link">Home</a>
        </div>
        <div class="user-info">
            <p>Welcome, <strong><?php echo htmlspecialchars($company_name); ?></strong></p>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>
    
    <div class="container">
        <div class="dashboard-summary">
            <div class="summary-card">
                <h2><?php echo $total_flights; ?></h2>
                <p>Total Flights</p>
            </div>
            <div class="summary-card">
                <h2><?php echo $upcoming_flights; ?></h2>
                <p>Upcoming Flights</p>
            </div>
            <div class="summary-card">
                <h2><?php echo $total_bookings; ?></h2>
                <p>Total Bookings</p>
            </div>
            <div class="summary-card">
                <h2>₹<?php echo number_format($total_revenue, 2); ?></h2>
                <p>Total Revenue</p>
            </div>
            <div class="summary-card">
                <h2><?php echo $booking_rate; ?>%</h2>
                <p>Overall Booking Rate</p>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="add_flight.php" class="add-flight-btn">+ Add New Flight</a>
        </div>
        
        <?php if (count($flights) > 0): ?>
            <table class="flights-table">
                <thead>
                    <tr>
                        <th>Flight ID</th>
                        <th>Route</th>
                        <th>Departure Date</th>
                        <th>Price</th>
                        <th>Bookings</th>
                        <th>Revenue</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($flights as $flight): ?>
                        <?php 
                            $booking_percent = 0;
                            if ($flight['seats_no'] > 0) {
                                $booking_percent = round(($flight['booked_seats'] / $flight['seats_no']) * 100, 2);
                            }
                            
                            $date_class = strtotime($flight['departure_date']) < strtotime(date('Y-m-d')) ? 'date-past' : 'date-future';
                            $is_past = strtotime($flight['departure_date']) < strtotime(date('Y-m-d'));
                        ?>
                        <tr>
                            <td>FLT-<?php echo $flight['flight_id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($flight['from_code'] . ' (' . $flight['from_place'] . ', ' . $flight['from_state'] . ')'); ?> → 
                                <?php echo htmlspecialchars($flight['to_code'] . ' (' . $flight['to_place'] . ', ' . $flight['to_state'] . ')'); ?>
                            </td>
                            <td class="<?php echo $date_class; ?>">
                                <?php echo date('M d, Y', strtotime($flight['departure_date'])); ?>
                            </td>
                            <td class="price-column">
                            ₹<?php echo number_format($flight['price'], 2); ?>
                            </td>
                            <td>
                                <div class="booking-status">
                                    <div class="booking-bar">
                                        <div class="booking-progress" style="width: <?php echo $booking_percent; ?>%"></div>
                                    </div>
                                    <div class="booking-percentage">
                                        <?php echo $flight['booked_seats'] . '/' . $flight['seats_no']; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="price-column">
                            ₹<?php echo number_format($flight['booking_revenue'], 2); ?>
                            </td>
                            <td class="action-cell">
                                <?php if (!$is_past): ?>
                                    <button onclick="openEditModal(<?php echo $flight['flight_id']; ?>, '<?php echo addslashes($flight['departure_date']); ?>', <?php echo $flight['seats_no']; ?>, <?php echo $flight['price']; ?>)" class="edit-btn">Edit</button>
                                    <button onclick="openCancelModal(<?php echo $flight['flight_id']; ?>, <?php echo $flight['booked_seats']; ?>)" class="cancel-btn">Cancel</button>
                                <?php endif; ?>
                                <!-- <a href="delete_flight.php?id=<?php echo $flight['flight_id']; ?>" class="delete-btn" 
                                   onclick="return confirm('Are you sure you want to delete this flight?');">Delete</a> -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <h2>No Flights Found</h2>
                <p>You haven't added any flights yet. Click the button below to add your first flight.</p>
                <a href="add_flight.php" class="add-flight-btn">+ Add New Flight</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Edit Flight Modal -->
    <div id="editFlightModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Flight Details</h2>
            <form id="editFlightForm" action="update_flight.php" method="POST">
                <input type="hidden" id="edit_flight_id" name="flight_id">
                
                <div class="form-group">
                    <label for="departure_date">Departure Date:</label>
                    <input type="date" id="departure_date" name="departure_date" required>
                </div>
                
                <div class="form-group">
                    <label for="seats_no">Number of Seats:</label>
                    <input type="number" id="seats_no" name="seats_no" min="1" required>
                </div>
                
                <!-- <div class="form-group">
                    <label for="price">Price per Seat ($):</label>
                    <input type="number" id="price" name="price" min="1" step="0.01" required>
                </div> -->
                
                <div class="form-actions">
                    <button type="button" class="cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Cancel Flight Modal -->
    <div id="cancelFlightModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCancelModal()">&times;</span>
            <h2>Cancel Flight</h2>
            <p id="cancelMessage"></p>
            <form id="cancelFlightForm" action="cancel_flight.php" method="POST">
                <input type="hidden" id="cancel_flight_id" name="flight_id">
                
                <div class="form-actions">
                    <button type="button" class="cancel" onclick="closeCancelModal()">No, Keep Flight</button>
                    <button type="submit" class="save">Yes, Cancel Flight</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Edit Flight Modal Functions
        function openEditModal(flightId, departureDate, seatsNo, price) {
            document.getElementById('edit_flight_id').value = flightId;
            document.getElementById('departure_date').value = departureDate;
            document.getElementById('seats_no').value = seatsNo;
            document.getElementById('price').value = price;
            document.getElementById('editFlightModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editFlightModal').style.display = 'none';
        }
        
        // Cancel Flight Modal Functions
        function openCancelModal(flightId, bookedSeats) {
            document.getElementById('cancel_flight_id').value = flightId;
            let message = 'Are you sure you want to cancel this flight?';
            
            if (bookedSeats > 0) {
                message += ' This flight has ' + bookedSeats + ' booking(s). All customers will be notified and refunded.';
            }
            
            document.getElementById('cancelMessage').innerText = message;
            document.getElementById('cancelFlightModal').style.display = 'block';
        }
        
        function closeCancelModal() {
            document.getElementById('cancelFlightModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('editFlightModal')) {
                closeEditModal();
            }
            if (event.target == document.getElementById('cancelFlightModal')) {
                closeCancelModal();
            }
        }
    </script>
</body>
</html>
