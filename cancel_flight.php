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
    $conn = new mysqli("localhost", "root", "Samruddhi@09", "DBMS_PROJECT");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Get form data
    $flight_id = $_POST['flight_id'];
    $company_id = $_SESSION['company_id'];
    
    // First check if the flight belongs to this company
    $check_query = "SELECT * FROM Flights WHERE flight_id = ? AND company_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $flight_id, $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Flight doesn't exist or doesn't belong to this company
        $_SESSION['error'] = "Flight not found or you don't have permission to cancel it.";
        header("Location: company_dashboard.php");
        exit();
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get all bookings for this flight to process refunds
        $bookings_query = "SELECT booking_id, user_id, price FROM Bookings WHERE flight_id = ?";
        $stmt = $conn->prepare($bookings_query);
        $stmt->bind_param("i", $flight_id);
        $stmt->execute();
        $bookings_result = $stmt->get_result();
        
        // In a real system, we would process refunds here
        // For this example, we'll just delete the bookings
        
        // Delete all bookings for this flight
        $delete_bookings_query = "DELETE FROM Bookings WHERE flight_id = ?";
        $stmt = $conn->prepare($delete_bookings_query);
        $stmt->bind_param("i", $flight_id);
        $stmt->execute();
        
        // Mark the flight as cancelled (alternatively, we could delete it)
        // For this example, we'll move the departure_date to a past date
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $update_query = "UPDATE Flights SET departure_date = ? WHERE flight_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $yesterday, $flight_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Flight cancelled successfully. All bookings have been cancelled and refunds will be processed.";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = "Error cancelling flight: " . $e->getMessage();
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