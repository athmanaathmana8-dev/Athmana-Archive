<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database_name = "airport_management_system";

$conn = mysqli_connect($servername, $username, $password, $database_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2>Adding More Sample Flights to Database</h2>";

// Extended sample flights covering more routes
$flights = [
    // Kochi to Pune
    ['6E101', 'IndiGo', '08:00:00', '10:30:00', 180, 'Kochi', 'Pune', 8500.00, 15000.00, 25000.00],
    ['AI201', 'Air India', '12:00:00', '14:30:00', 150, 'Kochi', 'Pune', 9000.00, 16000.00, 26000.00],
    ['SG301', 'SpiceJet', '16:00:00', '18:30:00', 120, 'Kochi', 'Pune', 8000.00, 14500.00, 24500.00],
    
    // Pune to Kochi (return)
    ['6E102', 'IndiGo', '09:00:00', '11:30:00', 180, 'Pune', 'Kochi', 8700.00, 15200.00, 25200.00],
    ['AI202', 'Air India', '13:00:00', '15:30:00', 150, 'Pune', 'Kochi', 9200.00, 16200.00, 26200.00],
    ['SG302', 'SpiceJet', '17:00:00', '19:30:00', 120, 'Pune', 'Kochi', 8200.00, 14700.00, 24700.00],
    
    // Delhi to Jaipur
    ['6E201', 'IndiGo', '07:00:00', '08:00:00', 180, 'Delhi', 'Jaipur', 3500.00, 7000.00, 12000.00],
    ['AI301', 'Air India', '11:00:00', '12:00:00', 150, 'Delhi', 'Jaipur', 4000.00, 7500.00, 12500.00],
    ['SG401', 'SpiceJet', '15:00:00', '16:00:00', 120, 'Delhi', 'Jaipur', 3200.00, 6800.00, 11800.00],
    ['6E202', 'IndiGo', '19:00:00', '20:00:00', 180, 'Delhi', 'Jaipur', 3600.00, 7100.00, 12100.00],
    
    // Jaipur to Delhi (return)
    ['6E203', 'IndiGo', '08:30:00', '09:30:00', 180, 'Jaipur', 'Delhi', 3550.00, 7050.00, 12050.00],
    ['AI302', 'Air India', '12:30:00', '13:30:00', 150, 'Jaipur', 'Delhi', 4050.00, 7550.00, 12550.00],
    ['SG402', 'SpiceJet', '16:30:00', '17:30:00', 120, 'Jaipur', 'Delhi', 3250.00, 6850.00, 11850.00],
    ['6E204', 'IndiGo', '20:30:00', '21:30:00', 180, 'Jaipur', 'Delhi', 3650.00, 7150.00, 12150.00],
    
    // Mumbai to Goa
    ['6E301', 'IndiGo', '08:00:00', '09:00:00', 180, 'Mumbai', 'Goa', 4500.00, 8500.00, 14000.00],
    ['AI401', 'Air India', '13:00:00', '14:00:00', 150, 'Mumbai', 'Goa', 5000.00, 9000.00, 14500.00],
    ['SG501', 'SpiceJet', '17:00:00', '18:00:00', 120, 'Mumbai', 'Goa', 4200.00, 8200.00, 13700.00],
    
    // Goa to Mumbai (return)
    ['6E302', 'IndiGo', '10:00:00', '11:00:00', 180, 'Goa', 'Mumbai', 4600.00, 8600.00, 14100.00],
    ['AI402', 'Air India', '15:00:00', '16:00:00', 150, 'Goa', 'Mumbai', 5100.00, 9100.00, 14600.00],
    ['SG502', 'SpiceJet', '19:00:00', '20:00:00', 120, 'Goa', 'Mumbai', 4300.00, 8300.00, 13800.00],
    
    // Hyderabad to Bangalore
    ['6E401', 'IndiGo', '07:30:00', '08:30:00', 180, 'Hyderabad', 'Bangalore', 3500.00, 6500.00, 11000.00],
    ['AI501', 'Air India', '12:30:00', '13:30:00', 150, 'Hyderabad', 'Bangalore', 4000.00, 7000.00, 11500.00],
    ['SG601', 'SpiceJet', '18:30:00', '19:30:00', 120, 'Hyderabad', 'Bangalore', 3200.00, 6200.00, 10700.00],
    
    // Bangalore to Hyderabad (return)
    ['6E402', 'IndiGo', '09:00:00', '10:00:00', 180, 'Bangalore', 'Hyderabad', 3600.00, 6600.00, 11100.00],
    ['AI502', 'Air India', '14:00:00', '15:00:00', 150, 'Bangalore', 'Hyderabad', 4100.00, 7100.00, 11600.00],
    ['SG602', 'SpiceJet', '20:00:00', '21:00:00', 120, 'Bangalore', 'Hyderabad', 3300.00, 6300.00, 10800.00],
    
    // Kolkata to Chennai
    ['6E501', 'IndiGo', '08:00:00', '10:30:00', 180, 'Kolkata', 'Chennai', 10000.00, 18000.00, 28000.00],
    ['AI601', 'Air India', '14:00:00', '16:30:00', 150, 'Kolkata', 'Chennai', 10500.00, 18500.00, 28500.00],
    
    // Chennai to Kolkata (return)
    ['6E502', 'IndiGo', '11:00:00', '13:30:00', 180, 'Chennai', 'Kolkata', 10200.00, 18200.00, 28200.00],
    ['AI602', 'Air India', '17:00:00', '19:30:00', 150, 'Chennai', 'Kolkata', 10700.00, 18700.00, 28700.00],
];

$added = 0;
$exists = 0;

foreach ($flights as $flight) {
    $flight_number = $flight[0];
    $flight_company = $flight[1];
    $departing_time = $flight[2];
    $arrival_time = $flight[3];
    $no_of_seats = $flight[4];
    $source = $flight[5];
    $destination = $flight[6];
    $price_economy = $flight[7];
    $price_business = $flight[8];
    $price_first = $flight[9];
    
    // Check if flight already exists
    $check = "SELECT * FROM flights WHERE flight_number = '$flight_number'";
    $result = mysqli_query($conn, $check);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color:orange;'>⚠ Flight $flight_number already exists (skipping)</p>";
        $exists++;
    } else {
        $sql = "INSERT INTO flights (flight_number, flight_company, departing_time, arrival_time, no_of_seats, source, destination, price_economy, price_business, price_first) 
                VALUES ('$flight_number', '$flight_company', '$departing_time', '$arrival_time', $no_of_seats, '$source', '$destination', $price_economy, $price_business, $price_first)";
        
        if (mysqli_query($conn, $sql)) {
            echo "<p style='color:green;'>✓ Added flight: $flight_company $flight_number ($source → $destination)</p>";
            $added++;
            
            // Create seats for this flight
            createSeatsForFlight($conn, $flight_number, $no_of_seats);
        } else {
            echo "<p style='color:red;'>✗ Error adding $flight_number: " . mysqli_error($conn) . "</p>";
        }
    }
}

function createSeatsForFlight($conn, $flight_number, $total_seats) {
    $seats_per_row = 6;
    $rows = ceil($total_seats / $seats_per_row);
    
    for ($row = 1; $row <= $rows; $row++) {
        for ($col = 1; $col <= $seats_per_row; $col++) {
            if (($row - 1) * $seats_per_row + $col > $total_seats) break;
            
            $seat_letter = chr(64 + $col);
            $seat_number = $row . $seat_letter;
            
            $seat_class = 'Economy';
            if ($row <= 2) {
                $seat_class = 'First';
            } elseif ($row <= 4) {
                $seat_class = 'Business';
            }
            
            $insert_sql = "INSERT IGNORE INTO seats (flight_number, seat_number, seat_class, is_available, is_reserved) 
                          VALUES ('$flight_number', '$seat_number', '$seat_class', 1, 0)";
            mysqli_query($conn, $insert_sql);
        }
    }
}

echo "<hr>";
echo "<h3 style='color:green;'>✅ Summary:</h3>";
echo "<p><strong>Added:</strong> $added new flights</p>";
echo "<p><strong>Skipped (already existed):</strong> $exists flights</p>";
echo "<h3 style='color:blue;'>📋 Routes Now Available:</h3>";
echo "<ul>";
echo "<li>Kochi ↔ Pune (3 flights each way)</li>";
echo "<li>Delhi ↔ Jaipur (4 flights each way)</li>";
echo "<li>Mumbai ↔ Goa (3 flights each way)</li>";
echo "<li>Hyderabad ↔ Bangalore (3 flights each way)</li>";
echo "<li>Kolkata ↔ Chennai (2 flights each way)</li>";
echo "</ul>";
echo "<hr>";
echo "<p><a href='Frontpage.html' style='padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>🏠 Go to Home Page & Search Flights</a></p>";

mysqli_close($conn);
?>


















