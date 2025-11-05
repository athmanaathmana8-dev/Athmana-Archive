<?php
/**
 * Add Flights to Database
 * Adds missing flights including the one causing the error
 */

$servername = "localhost";
$username = "root";
$password = "";
$database_name = "airport_management_system";

echo "<h1>Adding Flights to Database</h1>";

$conn = mysqli_connect($servername, $username, $password, $database_name);

if (!$conn) {
    die("<p style='color: red;'>Connection failed: " . mysqli_connect_error() . "</p>");
}

echo "<p style='color: green;'>✓ Connected to database</p>";

// Additional flights to add (including the missing one from error)
$additional_flights = [
    // Missing flight from error
    ['6E901', 'IndiGo', '07:25:00', '10:00:00', 150, 'Bangalore', 'Delhi', 4500.00, 9500.00, 15000.00],
    
    // More IndiGo flights
    ['6E202', 'IndiGo', '09:00:00', '11:30:00', 150, 'Bangalore', 'Delhi', 4000.00, 9000.00, 14500.00],
    ['6E404', 'IndiGo', '11:00:00', '13:30:00', 150, 'Delhi', 'Bangalore', 4200.00, 9200.00, 14800.00],
    ['6E505', 'IndiGo', '13:00:00', '15:30:00', 150, 'Bangalore', 'Mumbai', 3800.00, 8800.00, 14200.00],
    ['6E707', 'IndiGo', '15:00:00', '17:30:00', 150, 'Mumbai', 'Bangalore', 3900.00, 8900.00, 14300.00],
    
    // Air India flights
    ['AI201', 'Air India', '06:00:00', '08:30:00', 180, 'Bangalore', 'Delhi', 5000.00, 10000.00, 16000.00],
    ['AI301', 'Air India', '10:00:00', '12:30:00', 180, 'Delhi', 'Bangalore', 5100.00, 10100.00, 16100.00],
    ['AI501', 'Air India', '14:00:00', '16:30:00', 180, 'Bangalore', 'Mumbai', 4800.00, 9800.00, 15800.00],
    
    // SpiceJet flights
    ['SG301', 'SpiceJet', '08:00:00', '10:30:00', 160, 'Bangalore', 'Delhi', 4700.00, 9700.00, 15700.00],
    ['SG401', 'SpiceJet', '12:00:00', '14:30:00', 160, 'Delhi', 'Bangalore', 4900.00, 9900.00, 15900.00],
    ['SG601', 'SpiceJet', '16:00:00', '18:30:00', 160, 'Bangalore', 'Mumbai', 4600.00, 9600.00, 15600.00],
    
    // Vistara flights
    ['UK303', 'Vistara', '07:00:00', '09:30:00', 170, 'Bangalore', 'Delhi', 5000.00, 10000.00, 15500.00],
    ['UK403', 'Vistara', '11:00:00', '13:30:00', 170, 'Delhi', 'Bangalore', 5200.00, 10200.00, 15700.00],
    ['UK503', 'Vistara', '15:00:00', '17:30:00', 170, 'Bangalore', 'Mumbai', 4900.00, 9900.00, 15400.00],
    
    // More routes
    ['6E612', 'IndiGo', '18:00:00', '20:30:00', 150, 'Mumbai', 'Delhi', 4100.00, 9100.00, 14600.00],
    ['AI712', 'Air India', '19:00:00', '21:30:00', 180, 'Delhi', 'Mumbai', 5300.00, 10300.00, 16300.00],
    ['SG812', 'SpiceJet', '20:00:00', '22:30:00', 160, 'Mumbai', 'Delhi', 5000.00, 10000.00, 16000.00],
];

$added = 0;
$skipped = 0;

foreach ($additional_flights as $flight) {
    // Check if flight already exists
    $check_sql = "SELECT COUNT(*) as count FROM flights WHERE flight_number = '{$flight[0]}'";
    $check_result = mysqli_query($conn, $check_sql);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['count'] > 0) {
        echo "<p style='color: orange;'>⚠ Flight {$flight[1]} {$flight[0]} already exists, skipping...</p>";
        $skipped++;
        continue;
    }
    
    $sql = "INSERT INTO flights (flight_number, flight_company, departing_time, arrival_time, no_of_seats, source, destination, price_economy, price_business, price_first) 
            VALUES ('{$flight[0]}', '{$flight[1]}', '{$flight[2]}', '{$flight[3]}', {$flight[4]}, '{$flight[5]}', '{$flight[6]}', {$flight[7]}, {$flight[8]}, {$flight[9]})";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✓ Added flight: {$flight[1]} {$flight[0]} ({$flight[5]} → {$flight[6]})</p>";
        $added++;
        
        // Create seats for this flight
        createSeatsForFlight($conn, $flight[0], $flight[4]);
        echo "<p style='color: blue;'>  → Created {$flight[4]} seats for {$flight[0]}</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding flight {$flight[0]}: " . mysqli_error($conn) . "</p>";
    }
}

function createSeatsForFlight($conn, $flight_number, $total_seats) {
    $seats_per_row = 6;
    $rows = ceil($total_seats / $seats_per_row);
    
    for ($row = 1; $row <= $rows; $row++) {
        for ($col = 1; $col <= $seats_per_row; $col++) {
            if (($row - 1) * $seats_per_row + $col > $total_seats) break;
            
            $seat_letter = chr(64 + $col); // A, B, C, D, E, F
            $seat_number = $row . $seat_letter;
            
            $seat_sql = "INSERT IGNORE INTO seats (flight_number, seat_number, is_available, is_reserved) 
                        VALUES ('$flight_number', '$seat_number', 1, 0)";
            mysqli_query($conn, $seat_sql);
        }
    }
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p style='color: green; font-weight: bold;'>✓ Added: {$added} flights</p>";
if ($skipped > 0) {
    echo "<p style='color: orange;'>⚠ Skipped: {$skipped} flights (already exist)</p>";
}

echo "<p><a href='Ticketbooking.html'>Go to Ticket Booking</a></p>";

mysqli_close($conn);
?>


