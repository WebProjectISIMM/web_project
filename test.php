<?php
$servername = "localhost";
$username = "root";      // MariaDB root username
$password = "";          // MariaDB password (empty if default)
$dbname = "smartqueue";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully"; // optional test
?>