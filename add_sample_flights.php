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

echo "<h2>Adding Sample Flights to Database</h2>";

// Sample flights - Bangalore to Delhi route
$flights = [
    ['AI101', 'Air India', '08:00:00', '10:30:00', 150, 'Bangalore', 'Delhi', 15000.00, 25000.00, 35000.00],
    ['AI102', 'Air India', '14:00:00', '16:30:00', 150, 'Bangalore', 'Delhi', 16000.00, 26000.00, 36000.00],
    ['6E301', 'IndiGo', '09:30:00', '12:00:00', 180, 'Bangalore', 'Delhi', 14000.00, 24000.00, 34000.00],
    ['6E302', 'IndiGo', '15:00:00', '17:30:00', 180, 'Bangalore', 'Delhi', 14500.00, 24500.00, 34500.00],
    ['SG401', 'SpiceJet', '11:00:00', '13:30:00', 120, 'Bangalore', 'Delhi', 13500.00, 23000.00, 33000.00],
    
    // Delhi to Bangalore (return)
    ['AI201', 'Air India', '07:00:00', '09:30:00', 150, 'Delhi', 'Bangalore', 15500.00, 25500.00, 35500.00],
    ['AI202', 'Air India', '13:00:00', '15:30:00', 150, 'Delhi', 'Bangalore', 16500.00, 26500.00, 36500.00],
    ['6E401', 'IndiGo', '10:00:00', '12:30:00', 180, 'Delhi', 'Bangalore', 14500.00, 24500.00, 34500.00],
    ['6E402', 'IndiGo', '16:00:00', '18:30:00', 180, 'Delhi', 'Bangalore', 15000.00, 25000.00, 35000.00],
    
    // Chennai to Delhi
    ['AI501', 'Air India', '08:30:00', '11:00:00', 150, 'Chennai', 'Delhi', 18000.00, 28000.00, 38000.00],
    ['6E601', 'IndiGo', '10:00:00', '12:30:00', 180, 'Chennai', 'Delhi', 17000.00, 27000.00, 37000.00],
    
    // Delhi to Chennai (return)
    ['AI601', 'Air India', '09:00:00', '11:30:00', 150, 'Delhi', 'Chennai', 18500.00, 28500.00, 38500.00],
    ['6E701', 'IndiGo', '14:00:00', '16:30:00', 180, 'Delhi', 'Chennai', 17500.00, 27500.00, 37500.00],
    
    // Bangalore to Chennai
    ['6E801', 'IndiGo', '07:00:00', '08:30:00', 180, 'Bangalore', 'Chennai', 6000.00, 12000.00, 20000.00],
    ['SG901', 'SpiceJet', '15:00:00', '16:30:00', 120, 'Bangalore', 'Chennai', 5500.00, 11500.00, 19500.00],
    
    // Chennai to Bangalore (return)
    ['6E901', 'IndiGo', '09:00:00', '10:30:00', 180, 'Chennai', 'Bangalore', 6100.00, 12100.00, 20100.00],
    ['SG001', 'SpiceJet', '17:00:00', '18:30:00', 120, 'Chennai', 'Bangalore', 5600.00, 11600.00, 19600.00],
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
echo "<h3 style='color:green;'>Summary:</h3>";
echo "<p>✓ Added: $added flights</p>";
echo "<p>⚠ Skipped (already existed): $exists flights</p>";
echo "<p><strong>Total flights in database now!</strong></p>";
echo "<p><a href='Ticketbooking.html'>Go to Booking Page</a></p>";

mysqli_close($conn);
?>



























