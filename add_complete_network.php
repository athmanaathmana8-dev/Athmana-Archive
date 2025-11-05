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
echo "<html><head><title>Complete Flight Network Setup</title>";
echo "<style>
    body{font-family:Arial;padding:20px;background:#f5f5f5;} 
    .success{color:green;padding:5px;} 
    .warning{color:orange;padding:5px;} 
    .error{color:red;padding:5px;} 
    .summary{background:white;padding:30px;border-radius:15px;margin:20px 0;box-shadow:0 5px 15px rgba(0,0,0,0.1);}
    h1{color:#667eea;text-align:center;}
    .btn{display:inline-block;padding:15px 30px;margin:10px;background:#28a745;color:white;text-decoration:none;border-radius:8px;font-weight:bold;transition:all 0.3s;}
    .btn:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(0,0,0,0.2);}
    .progress{background:#f0f0f0;border-radius:5px;height:30px;margin:20px 0;}
    .progress-bar{background:#28a745;height:100%;border-radius:5px;text-align:center;line-height:30px;color:white;font-weight:bold;}
</style>";
echo "</head><body>";
echo "<h1>🛫 Complete Flight Network Setup</h1>";

// ALL POSSIBLE ROUTES - Comprehensive coverage
$flights = [
    // KOLKATA to BANGALORE (YOUR CURRENT SEARCH!) ✈️
    ['6E181', 'IndiGo', '06:00:00', '08:30:00', 180, 'Kolkata', 'Bangalore', 9000.00, 16000.00, 25000.00],
    ['AI281', 'Air India', '10:00:00', '12:30:00', 150, 'Kolkata', 'Bangalore', 9500.00, 16500.00, 25500.00],
    ['SG381', 'SpiceJet', '14:00:00', '16:30:00', 120, 'Kolkata', 'Bangalore', 8500.00, 15500.00, 24500.00],
    ['6E182', 'IndiGo', '18:00:00', '20:30:00', 180, 'Kolkata', 'Bangalore', 9200.00, 16200.00, 25200.00],
    
    // BANGALORE to KOLKATA (return)
    ['6E183', 'IndiGo', '07:00:00', '09:30:00', 180, 'Bangalore', 'Kolkata', 9100.00, 16100.00, 25100.00],
    ['AI282', 'Air India', '11:00:00', '13:30:00', 150, 'Bangalore', 'Kolkata', 9600.00, 16600.00, 25600.00],
    ['SG382', 'SpiceJet', '15:00:00', '17:30:00', 120, 'Bangalore', 'Kolkata', 8600.00, 15600.00, 24600.00],
    ['6E184', 'IndiGo', '19:00:00', '21:30:00', 180, 'Bangalore', 'Kolkata', 9300.00, 16300.00, 25300.00],
    
    // Chennai to Jaipur
    ['6E111', 'IndiGo', '06:00:00', '08:30:00', 180, 'Chennai', 'Jaipur', 12000.00, 20000.00, 30000.00],
    ['AI211', 'Air India', '10:00:00', '12:30:00', 150, 'Chennai', 'Jaipur', 13000.00, 21000.00, 31000.00],
    ['SG311', 'SpiceJet', '14:00:00', '16:30:00', 120, 'Chennai', 'Jaipur', 11500.00, 19500.00, 29500.00],
    ['6E112', 'IndiGo', '18:00:00', '20:30:00', 180, 'Chennai', 'Jaipur', 12500.00, 20500.00, 30500.00],
    
    // Jaipur to Chennai
    ['6E113', 'IndiGo', '07:00:00', '09:30:00', 180, 'Jaipur', 'Chennai', 12200.00, 20200.00, 30200.00],
    ['AI212', 'Air India', '11:00:00', '13:30:00', 150, 'Jaipur', 'Chennai', 13200.00, 21200.00, 31200.00],
    ['SG312', 'SpiceJet', '15:00:00', '17:30:00', 120, 'Jaipur', 'Chennai', 11700.00, 19700.00, 29700.00],
    
    // Bangalore to Delhi
    ['AI101', 'Air India', '08:00:00', '10:30:00', 150, 'Bangalore', 'Delhi', 15000.00, 25000.00, 35000.00],
    ['AI102', 'Air India', '14:00:00', '16:30:00', 150, 'Bangalore', 'Delhi', 16000.00, 26000.00, 36000.00],
    ['6E301', 'IndiGo', '09:30:00', '12:00:00', 180, 'Bangalore', 'Delhi', 14000.00, 24000.00, 34000.00],
    ['SG401', 'SpiceJet', '11:00:00', '13:30:00', 120, 'Bangalore', 'Delhi', 13500.00, 23000.00, 33000.00],
    
    // Delhi to Bangalore
    ['AI201', 'Air India', '07:00:00', '09:30:00', 150, 'Delhi', 'Bangalore', 15500.00, 25500.00, 35500.00],
    ['AI202', 'Air India', '13:00:00', '15:30:00', 150, 'Delhi', 'Bangalore', 16500.00, 26500.00, 36500.00],
    ['6E401', 'IndiGo', '10:00:00', '12:30:00', 180, 'Delhi', 'Bangalore', 14500.00, 24500.00, 34500.00],
    
    // Bangalore to Mumbai
    ['6E121', 'IndiGo', '08:00:00', '10:00:00', 180, 'Bangalore', 'Mumbai', 8000.00, 14000.00, 22000.00],
    ['AI221', 'Air India', '13:00:00', '15:00:00', 150, 'Bangalore', 'Mumbai', 8500.00, 14500.00, 22500.00],
    ['SG321', 'SpiceJet', '17:00:00', '19:00:00', 120, 'Bangalore', 'Mumbai', 7500.00, 13500.00, 21500.00],
    
    // Mumbai to Bangalore
    ['6E122', 'IndiGo', '09:00:00', '11:00:00', 180, 'Mumbai', 'Bangalore', 8200.00, 14200.00, 22200.00],
    ['AI222', 'Air India', '14:00:00', '16:00:00', 150, 'Mumbai', 'Bangalore', 8700.00, 14700.00, 22700.00],
    ['SG322', 'SpiceJet', '18:00:00', '20:00:00', 120, 'Mumbai', 'Bangalore', 7700.00, 13700.00, 21700.00],
    
    // Bangalore to Chennai
    ['6E801', 'IndiGo', '07:00:00', '08:30:00', 180, 'Bangalore', 'Chennai', 6000.00, 12000.00, 20000.00],
    ['SG901', 'SpiceJet', '15:00:00', '16:30:00', 120, 'Bangalore', 'Chennai', 5500.00, 11500.00, 19500.00],
    
    // Chennai to Bangalore
    ['6E901', 'IndiGo', '09:00:00', '10:30:00', 180, 'Chennai', 'Bangalore', 6100.00, 12100.00, 20100.00],
    ['SG001', 'SpiceJet', '17:00:00', '18:30:00', 120, 'Chennai', 'Bangalore', 5600.00, 11600.00, 19600.00],
    
    // Delhi to Mumbai
    ['6E131', 'IndiGo', '07:00:00', '09:30:00', 180, 'Delhi', 'Mumbai', 9000.00, 16000.00, 25000.00],
    ['AI231', 'Air India', '12:00:00', '14:30:00', 150, 'Delhi', 'Mumbai', 9500.00, 16500.00, 25500.00],
    ['SG331', 'SpiceJet', '16:00:00', '18:30:00', 120, 'Delhi', 'Mumbai', 8500.00, 15500.00, 24500.00],
    
    // Mumbai to Delhi
    ['6E133', 'IndiGo', '08:00:00', '10:30:00', 180, 'Mumbai', 'Delhi', 9100.00, 16100.00, 25100.00],
    ['AI232', 'Air India', '13:00:00', '15:30:00', 150, 'Mumbai', 'Delhi', 9600.00, 16600.00, 25600.00],
    ['SG332', 'SpiceJet', '17:00:00', '19:30:00', 120, 'Mumbai', 'Delhi', 8600.00, 15600.00, 24600.00],
    
    // Delhi to Jaipur
    ['6E201', 'IndiGo', '07:00:00', '08:00:00', 180, 'Delhi', 'Jaipur', 3500.00, 7000.00, 12000.00],
    ['AI301', 'Air India', '11:00:00', '12:00:00', 150, 'Delhi', 'Jaipur', 4000.00, 7500.00, 12500.00],
    ['SG401J', 'SpiceJet', '15:00:00', '16:00:00', 120, 'Delhi', 'Jaipur', 3200.00, 6800.00, 11800.00],
    
    // Jaipur to Delhi
    ['6E203', 'IndiGo', '08:30:00', '09:30:00', 180, 'Jaipur', 'Delhi', 3550.00, 7050.00, 12050.00],
    ['AI302', 'Air India', '12:30:00', '13:30:00', 150, 'Jaipur', 'Delhi', 4050.00, 7550.00, 12550.00],
    ['SG402J', 'SpiceJet', '16:30:00', '17:30:00', 120, 'Jaipur', 'Delhi', 3250.00, 6850.00, 11850.00],
    
    // Kochi to Pune
    ['6E101K', 'IndiGo', '08:00:00', '10:30:00', 180, 'Kochi', 'Pune', 8500.00, 15000.00, 25000.00],
    ['AI201K', 'Air India', '12:00:00', '14:30:00', 150, 'Kochi', 'Pune', 9000.00, 16000.00, 26000.00],
    ['SG301K', 'SpiceJet', '16:00:00', '18:30:00', 120, 'Kochi', 'Pune', 8000.00, 14500.00, 24500.00],
    
    // Pune to Kochi
    ['6E102K', 'IndiGo', '09:00:00', '11:30:00', 180, 'Pune', 'Kochi', 8700.00, 15200.00, 25200.00],
    ['AI202K', 'Air India', '13:00:00', '15:30:00', 150, 'Pune', 'Kochi', 9200.00, 16200.00, 26200.00],
    ['SG302K', 'SpiceJet', '17:00:00', '19:30:00', 120, 'Pune', 'Kochi', 8200.00, 14700.00, 24700.00],
];

$added = 0;
$exists = 0;
$total = count($flights);

echo "<div class='summary'>";
echo "<h3>Processing $total flights...</h3>";
echo "<div class='progress'><div class='progress-bar' id='progressBar' style='width:0%'>0%</div></div>";
echo "<div id='results'>";

foreach ($flights as $index => $flight) {
    list($flight_number, $flight_company, $departing_time, $arrival_time, $no_of_seats, $source, $destination, $price_economy, $price_business, $price_first) = $flight;
    
    // Check if flight already exists
    $check = "SELECT * FROM flights WHERE flight_number = '$flight_number'";
    $result = mysqli_query($conn, $check);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<p class='warning'>⚠ Flight $flight_number already exists (skipped)</p>";
        $exists++;
    } else {
        $sql = "INSERT INTO flights (flight_number, flight_company, departing_time, arrival_time, no_of_seats, source, destination, price_economy, price_business, price_first) 
                VALUES ('$flight_number', '$flight_company', '$departing_time', '$arrival_time', $no_of_seats, '$source', '$destination', $price_economy, $price_business, $price_first)";
        
        if (mysqli_query($conn, $sql)) {
            echo "<p class='success'>✓ Added: $flight_company $flight_number ($source → $destination) - ₹$price_economy</p>";
            $added++;
            createSeatsForFlight($conn, $flight_number, $no_of_seats);
        } else {
            echo "<p class='error'>✗ Error adding $flight_number: " . mysqli_error($conn) . "</p>";
        }
    }
    
    // Update progress
    $progress = round((($index + 1) / $total) * 100);
    echo "<script>document.getElementById('progressBar').style.width='$progress%';document.getElementById('progressBar').innerHTML='$progress%';</script>";
    flush();
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

echo "</div></div>";

echo "<div class='summary' style='margin-top:30px;'>";
echo "<h2 style='color:#28a745;text-align:center;'>✅ Setup Complete!</h2>";
echo "<h3>Summary:</h3>";
echo "<p><strong style='color:green;'>✓ Added:</strong> $added new flights</p>";
echo "<p><strong style='color:orange;'>⚠ Skipped:</strong> $exists flights (already existed)</p>";
echo "<p><strong>📊 Total Routes Available:</strong> " . ($added + $exists) . " flights</p>";

echo "<h3 style='margin-top:30px;'>✈️ Routes Now Available Include:</h3>";
echo "<ul style='line-height:2;'>";
echo "<li><strong style='color:#007bff;'>Kolkata ↔ Bangalore</strong> (4 flights each way) 🎯 YOUR SEARCH!</li>";
echo "<li>Chennai ↔ Jaipur (3-4 flights each way)</li>";
echo "<li>Bangalore ↔ Delhi (3-4 flights each way)</li>";
echo "<li>Bangalore ↔ Mumbai (3 flights each way)</li>";
echo "<li>Bangalore ↔ Chennai (2 flights each way)</li>";
echo "<li>Delhi ↔ Mumbai (3 flights each way)</li>";
echo "<li>Delhi ↔ Jaipur (3 flights each way)</li>";
echo "<li>Kochi ↔ Pune (3 flights each way)</li>";
echo "<li>And many more...</li>";
echo "</ul>";

echo "<hr style='margin:30px 0;'>";
echo "<h3 style='text-align:center;'>🚀 Ready to Test!</h3>";
echo "<div style='text-align:center;'>";
echo "<a href='Frontpage.html' class='btn' style='background:#007bff;'>🏠 Go to Home Page & Search</a>";
echo "<a href='test_search.html' class='btn' style='background:#17a2b8;'>🧪 Test API</a>";
echo "</div>";

echo "<div style='background:#fff3cd;padding:20px;border-radius:10px;margin-top:30px;border-left:5px solid #ffc107;'>";
echo "<h4 style='color:#856404;'>📝 Next Steps:</h4>";
echo "<ol style='color:#856404;line-height:2;'>";
echo "<li>Click <strong>'Go to Home Page'</strong> above</li>";
echo "<li>Search for: <strong>Kolkata → Bangalore</strong></li>";
echo "<li>Results will appear <strong>BELOW the search form</strong> on the same page!</li>";
echo "<li>Press F12 to see console logs if needed</li>";
echo "</ol>";
echo "</div>";

echo "</div>";

echo "</body></html>";

mysqli_close($conn);
?>


















