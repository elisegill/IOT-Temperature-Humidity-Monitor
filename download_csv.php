<?php
// Database credentials
$host = '127.0.0.1';
$db = 'sensor_data';
$user = 'root';
$pass = 'root';
$port = 8889;

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch all data
$sql = "SELECT id, temperature, humidity, timestamp FROM readings";
$result = $conn->query($sql);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sensor_data.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Temperature (°C)', 'Humidity (%)', 'Timestamp']);
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
$conn->close();
?>
