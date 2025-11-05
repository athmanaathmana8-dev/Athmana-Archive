<?php
// Check what flights are in the database
$servername = "localhost";
$username = "root";
$password = "";
$database_name = "airport_management_system";

$conn = mysqli_connect($servername, $username, $password, $database_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2>Flights in Database</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Flight Number</th><th>Company</th><th>Source</th><th>Destination</th><th>Departure Time</th><th>Arrival Time</th><th>Economy Price</th><th>Business Price</th><th>First Price</th></tr>";

$result = mysqli_query($conn, "SELECT * FROM flights ORDER BY flight_number");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['flight_number'] . "</td>";
        echo "<td>" . $row['flight_company'] . "</td>";
        echo "<td>" . $row['source'] . "</td>";
        echo "<td>" . $row['destination'] . "</td>";
        echo "<td>" . $row['departing_time'] . "</td>";
        echo "<td>" . $row['arrival_time'] . "</td>";
        echo "<td>₹" . number_format($row['price_economy']) . "</td>";
        echo "<td>₹" . number_format($row['price_business']) . "</td>";
        echo "<td>₹" . number_format($row['price_first']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>No flights found in database</td></tr>";
}

echo "</table>";

echo "<h2>Sample Booking Data</h2>";
echo "<p>Based on the error, the system is looking for flight: 'Air India AI101 / IndiGo 6E202'</p>";
echo "<p>This suggests the flight parameter might be malformed.</p>";

mysqli_close($conn);
?>