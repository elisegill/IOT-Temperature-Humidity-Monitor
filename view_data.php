<?php
// File: view_data.php

// Database credentials
$host = '127.0.0.1'; // Use 127.0.0.1 for MAMP compatibility
$db = 'sensor_data'; // Your database name
$user = 'root';      // Your MAMP MySQL username
$pass = 'root';      // Your MAMP MySQL password
$port = 8889;        // Default MAMP MySQL port

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch all rows from the `readings` table
$sql = "SELECT id, temperature, humidity, timestamp FROM readings ORDER BY id DESC";
$result = $conn->query($sql);

// Store every 10th row into an array
$readings = [];
$index = 0;
while ($row = $result->fetch_assoc()) {
    if ($index % 10 === 0) { // Only include every 10th record
        $readings[] = $row;
    }
    $index++;
}

// Filter every 30 minutes for the additional line graph
$sql_30_minutes = "SELECT id, temperature, humidity, timestamp FROM readings WHERE MINUTE(timestamp) = 0 ORDER BY timestamp ASC";
$result_30_minutes = $conn->query($sql_30_minutes);
$readings_30_minutes = [];
while ($row = $result_30_minutes->fetch_assoc()) {
    $readings_30_minutes[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Sensor Dashboard</title>
    <!-- Add Bootstrap CSS for responsive design -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            margin-top: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .alert {
            font-size: 0.9rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>IoT Sensor Dashboard</h1>

        <!-- Filters Section -->
        <div class="mb-3">
            <h4>Filter Data</h4>
            <form method="GET" action="">
                <label for="startDate">Start Date:</label>
                <input type="date" name="startDate" id="startDate">
                <label for="endDate">End Date:</label>
                <input type="date" name="endDate" id="endDate">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </form>
        </div>

        <?php
        // Database credentials
        $host = '127.0.0.1'; 
        $db = 'sensor_data'; 
        $user = 'root'; 
        $pass = 'root'; 
        $port = 8889; 

        // Connect to the database
        $conn = new mysqli($host, $user, $pass, $db, $port);

        // Check connection
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }

        // Fetch filtered data if date range is provided
        $query = "SELECT id, temperature, humidity, timestamp FROM readings";
        if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
            $startDate = $_GET['startDate'];
            $endDate = $_GET['endDate'];
            $query .= " WHERE DATE(timestamp) BETWEEN '$startDate' AND '$endDate'";
        }
        $query .= " ORDER BY timestamp DESC";

        $result = $conn->query($query);
        $readings = [];
        while ($row = $result->fetch_assoc()) {
            $readings[] = $row;
        }
        ?>

        <?php if (count($readings) > 0): ?>
            <!-- Data Table -->
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Temperature (°C)</th>
                        <th>Humidity (%)</th>
                        <th>Timestamp</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($readings as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['temperature']); ?></td>
                            <td><?php echo htmlspecialchars($row['humidity']); ?></td>
                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                            <td>
                                <?php if ($row['temperature'] > 30 || $row['humidity'] > 70): ?>
                                    <span class="alert alert-danger">Alert</span>
                                <?php else: ?>
                                    <span class="alert alert-success">Normal</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Charts Section -->
            <h2>Visualized Data</h2>
            <div class="row">
                <div class="col-md-6">
                    <canvas id="temperatureChart"></canvas>
                </div>
                <div class="col-md-6">
                    <canvas id="humidityChart"></canvas>
                </div>
            </div>

            <script>
                // Pass PHP data to JavaScript
                const data = <?php echo json_encode($readings); ?>;

                // Prepare chart data
                const labels = data.map(reading => reading.timestamp);
                const temperatures = data.map(reading => parseFloat(reading.temperature));
                const humidities = data.map(reading => parseFloat(reading.humidity));

                // Temperature Chart
                new Chart(document.getElementById('temperatureChart'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Temperature (°C)',
                            data: temperatures,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        }]
                    }
                });

                // Humidity Chart
                new Chart(document.getElementById('humidityChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Humidity (%)',
                            data: humidities,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                        }]
                    }
                });
            </script>
        <?php else: ?>
            <p>No data available for the selected range or in the database.</p>
        <?php endif; ?>

        <?php $conn->close(); ?>
    </div>

    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

