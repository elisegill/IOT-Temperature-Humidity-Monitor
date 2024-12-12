<?php
// File: insert_data.php

header('Content-Type: application/json');

// Database credentials
$host = '127.0.0.1'; // Use 127.0.0.1 instead of localhost for PHP-MAMP compatibility
$db = 'sensor_data';
$user = 'root';
$pass = 'root'; // Default MAMP password
$port = 8889;   // Default MySQL port in MAMP

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

// Validate POST data
if (isset($_POST['temperature']) && isset($_POST['humidity'])) {
    $temperature = $_POST['temperature'];
    $humidity = $_POST['humidity'];

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO readings (temperature, humidity) VALUES (?, ?)");
    $stmt->bind_param("dd", $temperature, $humidity);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Data inserted successfully',
            'data' => [
                'temperature' => $temperature,
                'humidity' => $humidity
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to insert data: ' . $stmt->error
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing parameters'
    ]);
}

$conn->close();
?>
