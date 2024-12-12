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

// Reverse the order to display oldest first in the table and charts
$readings = array_reverse($readings);

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

    <title>Sensor Readings</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f9;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: darkblue;
            color: white;
        }
        tr:nth-child(even) {
            background-color: lightblue;
        }
        h1 {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Sensor Data Readings</h1>
    <?php if (count($readings) > 0): ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Temperature (°C)</th>
                    <th>Humidity (%)</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($readings as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['temperature']); ?></td>
                        <td><?php echo htmlspecialchars($row['humidity']); ?></td>
                        <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Visualized Data</h2>
        <canvas id="temperatureChart" width="400" height="200"></canvas>
        <canvas id="humidityChart" width="400" height="200"></canvas>
        <h2>Temperature and Humidity Trends (Every 30 Minutes)</h2>
        <canvas id="trendChart" width="400" height="200"></canvas>

        <script>
            // Pass the PHP data to JavaScript
            const data = <?php echo json_encode($readings); ?>;
            const data30Minutes = <?php echo json_encode($readings_30_minutes); ?>;

            // Extract data for main graphs
            const labels = data.map(reading => reading.timestamp);
            const temperatures = data.map(reading => parseFloat(reading.temperature));
            const humidities = data.map(reading => parseFloat(reading.humidity));

            // Extract data for the 30-minute trend graph
            const trendLabels = data30Minutes.map(reading => reading.timestamp);
            const trendTemperatures = data30Minutes.map(reading => parseFloat(reading.temperature));
            const trendHumidities = data30Minutes.map(reading => parseFloat(reading.humidity));

            // Main temperature graph
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

            // Main humidity graph
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

            // Combined temperature and humidity trend graph
            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [
                        {
                            label: 'Temperature (°C)',
                            data: trendTemperatures,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        },
                        {
                            label: 'Humidity (%)',
                            data: trendHumidities,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        }
                    ]
                }
            });
        </script>

        <a href="download_csv.php" class="btn">Download CSV</a>


    <?php else: ?>
        <p>No data available in the database.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>
