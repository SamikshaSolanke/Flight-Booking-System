<?php
// Start the session
session_start();

// Check if the user is logged in as a company
if (!isset($_SESSION['company_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    // $conn = new mysqli("localhost", "root", "Samruddhi@09", "DBMS_PROJECT");
    include 'db_connection.php';

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Get form data
    $flight_id = $_POST['flight_id'];
    $departure_date = $_POST['departure_date'];
    $seats_no = $_POST['seats_no'];
    $price = $_POST['price'];
    $company_id = $_SESSION['company_id'];
    
    // First check if the flight belongs to this company
    $check_query = "SELECT * FROM Flights WHERE flight_id = ? AND company_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $flight_id, $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Flight doesn't exist or doesn't belong to this company
        $_SESSION['error'] = "Flight not found or you don't have permission to edit it.";
        header("Location: company_dashboard.php");
        exit();
    }
    
    // Get the current flight details
    $flight = $result->fetch_assoc();
    
    // Check if there are already more bookings than the new seats number
    $bookings_query = "SELECT COUNT(*) as booked_seats FROM Bookings WHERE flight_id = ?";
    $stmt = $conn->prepare($bookings_query);
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $bookings_result = $stmt->get_result();
    $booked_seats = $bookings_result->fetch_assoc()['booked_seats'];
    
    if ($booked_seats > $seats_no) {
        $_SESSION['error'] = "Cannot reduce seats to $seats_no. The flight already has $booked_seats bookings.";
        header("Location: company_dashboard.php");
        exit();
    }
    
    // Update the flight
    $update_query = "UPDATE Flights SET departure_date = ?, seats_no = ?, price = ? WHERE flight_id = ? AND company_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("siiii", $departure_date, $seats_no, $price, $flight_id, $company_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Flight updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating flight: " . $conn->error;
    }
    
    // Close connection
    $conn->close();
    
    // Redirect back to dashboard
    header("Location: company_dashboard.php");
    exit();
} else {
    // If not a POST request, redirect to dashboard
    header("Location: company_dashboard.php");
    exit();
}
?>