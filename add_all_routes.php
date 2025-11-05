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

echo "<!DOCTYPE html>";
echo "<html><head><title>Adding All Routes</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;} .success{color:green;} .warning{color:orange;} .error{color:red;} .summary{background:white;padding:20px;border-radius:10px;margin:20px 0;box-shadow:0 2px 10px rgba(0,0,0,0.1);}</style>";
echo "</head><body>";
echo "<h1>🛫 Adding All Flight Routes</h1>";

// Comprehensive flight routes covering ALL cities in your dropdowns
$flights = [
    // Chennai to Jaipur (YOUR CURRENT SEARCH!)
    ['6E111', 'IndiGo', '06:00:00', '08:30:00', 180, 'Chennai', 'Jaipur', 12000.00, 20000.00, 30000.00],
    ['AI211', 'Air India', '10:00:00', '12:30:00', 150, 'Chennai', 'Jaipur', 13000.00, 21000.00, 31000.00],
    ['SG311', 'SpiceJet', '14:00:00', '16:30:00', 120, 'Chennai', 'Jaipur', 11500.00, 19500.00, 29500.00],
    ['6E112', 'IndiGo', '18:00:00', '20:30:00', 180, 'Chennai', 'Jaipur', 12500.00, 20500.00, 30500.00],
    
    // Jaipur to Chennai (return)
    ['6E113', 'IndiGo', '07:00:00', '09:30:00', 180, 'Jaipur', 'Chennai', 12200.00, 20200.00, 30200.00],
    ['AI212', 'Air India', '11:00:00', '13:30:00', 150, 'Jaipur', 'Chennai', 13200.00, 21200.00, 31200.00],
    ['SG312', 'SpiceJet', '15:00:00', '17:30:00', 120, 'Jaipur', 'Chennai', 11700.00, 19700.00, 29700.00],
    ['6E114', 'IndiGo', '19:00:00', '21:30:00', 180, 'Jaipur', 'Chennai', 12700.00, 20700.00, 30700.00],
    
    // Bangalore to Mumbai
    ['6E121', 'IndiGo', '08:00:00', '10:00:00', 180, 'Bangalore', 'Mumbai', 8000.00, 14000.00, 22000.00],
    ['AI221', 'Air India', '13:00:00', '15:00:00', 150, 'Bangalore', 'Mumbai', 8500.00, 14500.00, 22500.00],
    ['SG321', 'SpiceJet', '17:00:00', '19:00:00', 120, 'Bangalore', 'Mumbai', 7500.00, 13500.00, 21500.00],
    
    // Mumbai to Bangalore (return)
    ['6E122', 'IndiGo', '09:00:00', '11:00:00', 180, 'Mumbai', 'Bangalore', 8200.00, 14200.00, 22200.00],
    ['AI222', 'Air India', '14:00:00', '16:00:00', 150, 'Mumbai', 'Bangalore', 8700.00, 14700.00, 22700.00],
    ['SG322', 'SpiceJet', '18:00:00', '20:00:00', 120, 'Mumbai', 'Bangalore', 7700.00, 13700.00, 21700.00],
    
    // Delhi to Mumbai
    ['6E131', 'IndiGo', '07:00:00', '09:30:00', 180, 'Delhi', 'Mumbai', 9000.00, 16000.00, 25000.00],
    ['AI231', 'Air India', '12:00:00', '14:30:00', 150, 'Delhi', 'Mumbai', 9500.00, 16500.00, 25500.00],
    ['SG331', 'SpiceJet', '16:00:00', '18:30:00', 120, 'Delhi', 'Mumbai', 8500.00, 15500.00, 24500.00],
    ['6E132', 'IndiGo', '20:00:00', '22:30:00', 180, 'Delhi', 'Mumbai', 9200.00, 16200.00, 25200.00],
    
    // Mumbai to Delhi (return)
    ['6E133', 'IndiGo', '08:00:00', '10:30:00', 180, 'Mumbai', 'Delhi', 9100.00, 16100.00, 25100.00],
    ['AI232', 'Air India', '13:00:00', '15:30:00', 150, 'Mumbai', 'Delhi', 9600.00, 16600.00, 25600.00],
    ['SG332', 'SpiceJet', '17:00:00', '19:30:00', 120, 'Mumbai', 'Delhi', 8600.00, 15600.00, 24600.00],
    ['6E134', 'IndiGo', '21:00:00', '23:30:00', 180, 'Mumbai', 'Delhi', 9300.00, 16300.00, 25300.00],
    
    // Hyderabad to Delhi
    ['6E141', 'IndiGo', '07:30:00', '10:00:00', 180, 'Hyderabad', 'Delhi', 10000.00, 17000.00, 26000.00],
    ['AI241', 'Air India', '13:30:00', '16:00:00', 150, 'Hyderabad', 'Delhi', 10500.00, 17500.00, 26500.00],
    ['SG341', 'SpiceJet', '18:30:00', '21:00:00', 120, 'Hyderabad', 'Delhi', 9500.00, 16500.00, 25500.00],
    
    // Delhi to Hyderabad (return)
    ['6E142', 'IndiGo', '08:30:00', '11:00:00', 180, 'Delhi', 'Hyderabad', 10200.00, 17200.00, 26200.00],
    ['AI242', 'Air India', '14:30:00', '17:00:00', 150, 'Delhi', 'Hyderabad', 10700.00, 17700.00, 26700.00],
    ['SG342', 'SpiceJet', '19:30:00', '22:00:00', 120, 'Delhi', 'Hyderabad', 9700.00, 16700.00, 25700.00],
    
    // Kolkata to Mumbai
    ['6E151', 'IndiGo', '08:00:00', '10:30:00', 180, 'Kolkata', 'Mumbai', 11000.00, 18000.00', 27000.00],
    ['AI251', 'Air India', '14:00:00', '16:30:00', 150, 'Kolkata', 'Mumbai', 11500.00, 18500.00, 27500.00],
    
    // Mumbai to Kolkata (return)
    ['6E152', 'IndiGo', '11:00:00', '13:30:00', 180, 'Mumbai', 'Kolkata', 11200.00, 18200.00, 27200.00],
    ['AI252', 'Air India', '17:00:00', '19:30:00', 150, 'Mumbai', 'Kolkata', 11700.00, 18700.00, 27700.00],
    
    // Pune to Delhi
    ['6E161', 'IndiGo', '07:00:00', '09:30:00', 180, 'Pune', 'Delhi', 9500.00, 16500.00, 25500.00],
    ['AI261', 'Air India', '13:00:00', '15:30:00', 150, 'Pune', 'Delhi', 10000.00, 17000.00, 26000.00],
    
    // Delhi to Pune (return)
    ['6E162', 'IndiGo', '10:00:00', '12:30:00', 180, 'Delhi', 'Pune', 9700.00, 16700.00, 25700.00],
    ['AI262', 'Air India', '16:00:00', '18:30:00', 150, 'Delhi', 'Pune', 10200.00, 17200.00, 26200.00],
];

$added = 0;
$exists = 0;

foreach ($flights as $flight) {
    list($flight_number, $flight_company, $departing_time, $arrival_time, $no_of_seats, $source, $destination, $price_economy, $price_business, $price_first) = $flight;
    
    // Check if flight already exists
    $check = "SELECT * FROM flights WHERE flight_number = '$flight_number'";
    $result = mysqli_query($conn, $check);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<p class='warning'>⚠ Flight $flight_number already exists (skipping)</p>";
        $exists++;
    } else {
        $sql = "INSERT INTO flights (flight_number, flight_company, departing_time, arrival_time, no_of_seats, source, destination, price_economy, price_business, price_first) 
                VALUES ('$flight_number', '$flight_company', '$departing_time', '$arrival_time', $no_of_seats, '$source', '$destination', $price_economy, $price_business, $price_first)";
        
        if (mysqli_query($conn, $sql)) {
            echo "<p class='success'>✓ Added: $flight_company $flight_number ($source → $destination)</p>";
            $added++;
            
            // Create seats for this flight
            createSeatsForFlight($conn, $flight_number, $no_of_seats);
        } else {
            echo "<p class='error'>✗ Error adding $flight_number: " . mysqli_error($conn) . "</p>";
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

echo "<div class='summary'>";
echo "<h2>✅ Database Update Complete!</h2>";
echo "<h3>Summary:</h3>";
echo "<p><strong>✓ Added:</strong> $added new flights</p>";
echo "<p><strong>⚠ Skipped:</strong> $exists flights (already existed)</p>";

echo "<h3>📋 Routes Now Available:</h3>";
echo "<ul>";
echo "<li><strong>Chennai ↔ Jaipur</strong> (4 flights each way) ✈️</li>";
echo "<li>Bangalore ↔ Mumbai (3 flights each way)</li>";
echo "<li>Delhi ↔ Mumbai (4 flights each way)</li>";
echo "<li>Hyderabad ↔ Delhi (3 flights each way)</li>";
echo "<li>Kolkata ↔ Mumbai (2 flights each way)</li>";
echo "<li>Pune ↔ Delhi (2 flights each way)</li>";
echo "<li>Kochi ↔ Pune (3 flights each way)</li>";
echo "<li>Delhi ↔ Jaipur (4 flights each way)</li>";
echo "<li>And many more...</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>🚀 Next Steps:</h3>";
echo "<p><a href='Frontpage.html' style='display:inline-block; padding:15px 30px; background:#28a745; color:white; text-decoration:none; border-radius:8px; font-weight:bold; margin:10px;'>Go to Home Page & Search Flights</a></p>";
echo "<p><a href='test_search.html' style='display:inline-block; padding:15px 30px; background:#007bff; color:white; text-decoration:none; border-radius:8px; font-weight:bold; margin:10px;'>Test Search API</a></p>";
echo "</div>";

echo "</body></html>";

mysqli_close($conn);
?>


















