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
echo "<html><head><title>Adding Goa-Pune Flights</title>";
echo "<style>
body{font-family:Arial;padding:30px;background:linear-gradient(135deg, #28a745 0%, #20c997 100%);min-height:100vh;}
.container{max-width:800px;margin:0 auto;background:white;padding:40px;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.3);}
.success{color:green;padding:10px;background:#d4edda;margin:10px 0;border-radius:8px;border-left:5px solid #28a745;}
.warning{color:orange;padding:10px;background:#fff3cd;margin:10px 0;border-radius:8px;border-left:5px solid #ffc107;}
h1{color:#28a745;text-align:center;}
.btn{display:inline-block;padding:15px 30px;margin:10px;background:#28a745;color:white;text-decoration:none;border-radius:10px;font-weight:bold;font-size:18px;}
.btn:hover{transform:translateY(-3px);box-shadow:0 10px 20px rgba(0,0,0,0.2);}
.summary{background:#d4edda;padding:20px;border-radius:10px;margin:20px 0;border-left:5px solid #28a745;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🛫 Adding Goa ↔ Pune Flights</h1>";

// Goa to Pune flights (YOUR CURRENT SEARCH!)
$flights = [
    // Goa to Pune
    ['6E171G', 'IndiGo', '07:00:00', '08:30:00', 180, 'Goa', 'Pune', 4500.00, 8500.00, 14000.00],
    ['AI271G', 'Air India', '11:00:00', '12:30:00', 150, 'Goa', 'Pune', 5000.00, 9000.00, 14500.00],
    ['SG371G', 'SpiceJet', '15:00:00', '16:30:00', 120, 'Goa', 'Pune', 4200.00, 8200.00, 13700.00],
    ['6E172G', 'IndiGo', '19:00:00', '20:30:00', 180, 'Goa', 'Pune', 4700.00, 8700.00, 14200.00],
    
    // Pune to Goa (return)
    ['6E173P', 'IndiGo', '08:00:00', '09:30:00', 180, 'Pune', 'Goa', 4600.00, 8600.00, 14100.00],
    ['AI272P', 'Air India', '12:00:00', '13:30:00', 150, 'Pune', 'Goa', 5100.00, 9100.00, 14600.00],
    ['SG372P', 'SpiceJet', '16:00:00', '17:30:00', 120, 'Pune', 'Goa', 4300.00, 8300.00, 13800.00],
    ['6E174P', 'IndiGo', '20:00:00', '21:30:00', 180, 'Pune', 'Goa', 4800.00, 8800.00, 14300.00],
];

$added = 0;
$exists = 0;

foreach ($flights as $flight) {
    list($flight_number, $flight_company, $departing_time, $arrival_time, $no_of_seats, $source, $destination, $price_economy, $price_business, $price_first) = $flight;
    
    // Check if flight already exists
    $check = "SELECT * FROM flights WHERE flight_number = '$flight_number'";
    $result = mysqli_query($conn, $check);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<p class='warning'>⚠ Flight $flight_number already exists - $flight_company ($source → $destination)</p>";
        $exists++;
    } else {
        $sql = "INSERT INTO flights (flight_number, flight_company, departing_time, arrival_time, no_of_seats, source, destination, price_economy, price_business, price_first) 
                VALUES ('$flight_number', '$flight_company', '$departing_time', '$arrival_time', $no_of_seats, '$source', '$destination', $price_economy, $price_business, $price_first)";
        
        if (mysqli_query($conn, $sql)) {
            echo "<p class='success'>✓ Added: $flight_company $flight_number - $source → $destination (₹$price_economy)</p>";
            $added++;
            
            // Create seats
            createSeatsForFlight($conn, $flight_number, $no_of_seats);
        } else {
            echo "<p class='error'>✗ Error: " . mysqli_error($conn) . "</p>";
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
            if ($row <= 2) $seat_class = 'First';
            elseif ($row <= 4) $seat_class = 'Business';
            
            $insert_sql = "INSERT IGNORE INTO seats (flight_number, seat_number, seat_class, is_available, is_reserved) 
                          VALUES ('$flight_number', '$seat_number', '$seat_class', 1, 0)";
            mysqli_query($conn, $insert_sql);
        }
    }
}

echo "<div class='summary'>";
echo "<h2 style='color:#28a745;'>✅ Success!</h2>";
echo "<h3>Summary:</h3>";
echo "<p><strong>✓ Added:</strong> $added new flights</p>";
echo "<p><strong>⚠ Already existed:</strong> $exists flights</p>";
echo "<h3>✈️ Available Routes:</h3>";
echo "<ul>";
echo "<li><strong>Goa → Pune:</strong> 4 flights (IndiGo, Air India, SpiceJet)</li>";
echo "<li><strong>Pune → Goa:</strong> 4 flights (IndiGo, Air India, SpiceJet)</li>";
echo "</ul>";
echo "</div>";

echo "<hr style='margin:30px 0;'>";
echo "<div style='text-align:center;'>";
echo "<h3>🚀 What to Do Next:</h3>";
echo "<ol style='text-align:left;font-size:16px;line-height:2;'>";
echo "<li>Go to the home page</li>";
echo "<li>Click the <strong>'Available Flights'</strong> button (green button)</li>";
echo "<li>You'll see ALL flights grouped by route</li>";
echo "<li>Or search for <strong>Goa → Pune</strong> specifically</li>";
echo "</ol>";
echo "<a href='Frontpage.html' class='btn'>🏠 Go to Home Page</a>";
echo "</div>";

echo "</div></body></html>";

mysqli_close($conn);
?>


