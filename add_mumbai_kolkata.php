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
echo "<html><head><title>Adding Mumbai-Kolkata Flights</title>";
echo "<style>
body{font-family:Arial;padding:30px;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);min-height:100vh;}
.container{max-width:800px;margin:0 auto;background:white;padding:40px;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.3);}
.success{color:green;padding:10px;background:#d4edda;margin:10px 0;border-radius:8px;border-left:5px solid #28a745;}
.warning{color:orange;padding:10px;background:#fff3cd;margin:10px 0;border-radius:8px;border-left:5px solid #ffc107;}
h1{color:#667eea;text-align:center;}
.btn{display:inline-block;padding:15px 30px;margin:10px;background:#28a745;color:white;text-decoration:none;border-radius:10px;font-weight:bold;font-size:18px;}
.btn:hover{transform:translateY(-3px);box-shadow:0 10px 20px rgba(0,0,0,0.2);}
.summary{background:#e7f3ff;padding:20px;border-radius:10px;margin:20px 0;border-left:5px solid #007bff;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🛫 Adding Mumbai ↔ Kolkata Flights</h1>";

// Mumbai to Kolkata flights (YOUR CURRENT SEARCH!)
$flights = [
    // Mumbai to Kolkata
    ['6E151M', 'IndiGo', '08:00:00', '10:30:00', 180, 'Mumbai', 'Kolkata', 11000.00, 18000.00, 27000.00],
    ['AI251M', 'Air India', '12:00:00', '14:30:00', 150, 'Mumbai', 'Kolkata', 11500.00, 18500.00, 27500.00],
    ['SG351M', 'SpiceJet', '16:00:00', '18:30:00', 120, 'Mumbai', 'Kolkata', 10500.00, 17500.00, 26500.00],
    ['6E152M', 'IndiGo', '20:00:00', '22:30:00', 180, 'Mumbai', 'Kolkata', 11200.00, 18200.00, 27200.00],
    
    // Kolkata to Mumbai (return)
    ['6E152', 'IndiGo', '07:00:00', '09:30:00', 180, 'Kolkata', 'Mumbai', 11200.00, 18200.00, 27200.00],
    ['AI252', 'Air India', '11:00:00', '13:30:00', 150, 'Kolkata', 'Mumbai', 11700.00, 18700.00, 27700.00],
    ['SG352', 'SpiceJet', '15:00:00', '17:30:00', 120, 'Kolkata', 'Mumbai', 10700.00, 17700.00, 26700.00],
    ['6E153', 'IndiGo', '19:00:00', '21:30:00', 180, 'Kolkata', 'Mumbai', 11400.00, 18400.00, 27400.00],
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
echo "<h2 style='color:#007bff;'>✅ Success!</h2>";
echo "<h3>Summary:</h3>";
echo "<p><strong>✓ Added:</strong> $added new flights</p>";
echo "<p><strong>⚠ Already existed:</strong> $exists flights</p>";
echo "<h3>✈️ Available Routes:</h3>";
echo "<ul>";
echo "<li><strong>Mumbai → Kolkata:</strong> 4 flights (IndiGo, Air India, SpiceJet)</li>";
echo "<li><strong>Kolkata → Mumbai:</strong> 4 flights (IndiGo, Air India, SpiceJet)</li>";
echo "</ul>";
echo "</div>";

echo "<hr style='margin:30px 0;'>";
echo "<div style='text-align:center;'>";
echo "<h3>🚀 Ready to Test!</h3>";
echo "<p style='font-size:18px;margin:20px 0;'>Now go to the home page and search for <strong>Mumbai → Kolkata</strong></p>";
echo "<a href='Frontpage.html' class='btn'>🏠 Go to Home Page</a>";
echo "<a href='simple_search_test.html' class='btn' style='background:#007bff;'>🧪 Try Simple Search</a>";
echo "</div>";

echo "<div style='background:#fff3cd;padding:20px;border-radius:10px;margin-top:30px;border-left:5px solid #ffc107;'>";
echo "<h4 style='color:#856404;'>📝 What Happens Next:</h4>";
echo "<ol style='color:#856404;font-size:16px;line-height:2;'>";
echo "<li>Go to home page (Frontpage.html)</li>";
echo "<li>Select: <strong>From: Mumbai → To: Kolkata</strong></li>";
echo "<li>Click <strong>Search Flights</strong></li>";
echo "<li><strong>Results will appear BELOW</strong> on the same page</li>";
echo "<li>You'll see 4 flight options with prices and details</li>";
echo "<li>Click <strong>Book Now</strong> on any flight to proceed</li>";
echo "</ol>";
echo "</div>";

echo "</div></body></html>";

mysqli_close($conn);
?>


















