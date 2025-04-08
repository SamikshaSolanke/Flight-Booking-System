<?php
$servername = "XX"; // Replace the XX with the required for example "localhost" in most of the case
$username = "XX";  // Replace with your MySQL username for example "root" in most of the case
$password = "XX";  // Replace the XX with the required MySQL password of yours
$dbname = "DBMS_PROJECT"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
