<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost"; // Try "127.0.0.1" if this fails
$username = "bhavicreations";
$password = "d8Az75YlgmyBnVM";  
// $username = "root";
// $password = "";  
$dbname = "samhita";


 

echo "Attempting to connect to database '{$dbname}' on '{$servername}' with user '{$username}'...<br>";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection FAILED: " . $conn->connect_error . " (Error No: " . $conn->connect_errno . ")<br>");
}
echo "Connection SUCCESSFUL to database: " . $dbname . "<br>";

// Optional: Test a simple query
$result = $conn->query("SELECT 1+1 AS test");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Simple query 'SELECT 1+1' successful. Result: " . $row['test'] . "<br>";
    $result->free();
} else {
    echo "Simple query FAILED: " . $conn->error . " (Error No: " . $conn->errno . ")<br>";
}

// Close connection
$conn->close();
echo "Connection closed.<br>";
?>