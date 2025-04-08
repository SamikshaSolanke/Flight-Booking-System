<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if booking_id is provided
if (!isset($_POST['booking_id']) || empty($_POST['booking_id'])) {
    header("Location: my_bookings.php?cancel_error=1");
    exit();
}

$booking_id = $_POST['booking_id'];

// Database connection
include 'db_connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// First verify that the booking belongs to the logged-in user
$verify_query = "SELECT user_id, flight_id FROM Bookings WHERE booking_id = ?";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("i", $booking_id);
$verify_stmt->execute();
$result = $verify_stmt->get_result();

if ($result->num_rows === 0) {
    // Booking doesn't exist
    $verify_stmt->close();
    $conn->close();
    header("Location: my_bookings.php?cancel_error=1");
    exit();
}

$booking = $result->fetch_assoc();

// Check if the booking belongs to the logged-in user
if ($booking['user_id'] != $_SESSION['user_id']) {
    // Not authorized to cancel this booking
    $verify_stmt->close();
    $conn->close();
    header("Location: my_bookings.php?cancel_error=1");
    exit();
}

// Retrieve flight_id for updating seats_no after cancellation
$flight_id = $booking['flight_id'];

// Delete the booking
$delete_query = "DELETE FROM Bookings WHERE booking_id = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("i", $booking_id);

// Start transaction to ensure data consistency
$conn->begin_transaction();

try {
    // Execute the delete query
    $delete_result = $delete_stmt->execute();
    
    if ($delete_result) {
        // Increment available seats in Flights table by 1
        $update_seats_query = "UPDATE Flights SET seats_no = seats_no + 1 WHERE flight_id = ?";
        $update_stmt = $conn->prepare($update_seats_query);
        $update_stmt->bind_param("i", $flight_id);
        $update_result = $update_stmt->execute();
        
        if (!$update_result) {
            // If updating seats fails, rollback
            throw new Exception("Failed to update seats");
        }
        
        $update_stmt->close();
        
        // If everything is successful, commit transaction
        $conn->commit();
        
        // Redirect to booking page with success message
        header("Location: my_bookings.php?cancel_success=1");
        exit();
    } else {
        // If deletion fails, rollback
        throw new Exception("Failed to delete booking");
    }
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    header("Location: my_bookings.php?cancel_error=1");
    exit();
}

// Close statements and connection
$delete_stmt->close();
$verify_stmt->close();
$conn->close();
?>