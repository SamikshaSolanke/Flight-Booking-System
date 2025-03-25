<?php
$servername = "localhost";
$username = "root";  // Replace with your MySQL username
$password = "Samruddhi@09";  // Replace with your MySQL password
$dbname = "DBMS_PROJECT";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>