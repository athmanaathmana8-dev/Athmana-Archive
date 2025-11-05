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
echo "<html><head><title>Adding Main Routes</title>";
echo "<style>
body{font-family:Arial;padding:30px;background:linear-gradient(135deg, #28a745 0%, #20c997 100%);min-height:100vh;}
.container{max-width:900px;margin:0 auto;background:white;padding:40px;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.3);}
.success{color:green;padding:10px;background:#d4edda;margin:10px 0;border-radius:8px;border-left:5px solid #28a745;}
.warning{color:orange;padding:10px;background:#fff3cd;margin:10px 0;border-radius:8px;border-left:5px solid #ffc107;}
h1{color:#28a745;text-align:center;}
.btn{display:inline-block;padding:15px 30px;margin:10px;background:#28a745;color:white;text-decoration:none;border-radius:10px;font-weight:bold;font-size:18px;}
.summary{background:#d4edda;padding:20px;border-radius:10px;margin:20px 0;border-left:5px solid #28a745;}
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🛫 Adding Flights for Main Routes</h1>";

// Comprehensive flights for all 10 cities
$flights = [
    // Hyderabad to Delhi (Your search!)
    ['6E141', 'IndiGo', '07:30:00', '10:00:00', 180, 'Hyderabad', 'Delhi', 10000.00, 17000.00, 26000.00],
    ['AI241', 'Air India', '13:30:00', '16:00:00', 150, 'Hyderabad', 'Delhi', 10500.00, 17500.00, 26500.00],
    ['SG341', 'SpiceJet', '18:30:00', '21:00:00', 120, 'Hyderabad', 'Delhi', 9500.00, 16500.00, 25500.00],
    
    // Delhi to Hyderabad
    ['6E142', 'IndiGo', '08:30:00', '11:00:00', 180, 'Delhi', 'Hyderabad', 10200.00, 17200.00, 26200.00],
    ['AI242', 'Air India', '14:30:00', '17:00:00', 150, 'Delhi', 'Hyderabad', 10700.00, 17700.00, 26700.00],
    ['SG342', 'SpiceJet', '19:30:00', '22:00:00', 120, 'Delhi', 'Hyderabad', 9700.00, 16700.00, 25700.00],
    
    // Bangalore to Delhi
    ['AI101', 'Air India', '08:00:00', '10:30:00', 150, 'Bangalore', 'Delhi', 15000.00, 25000.00, 35000.00],
    ['6E301', 'IndiGo', '14:00:00', '16:30:00', 180, 'Bangalore', 'Delhi', 14000.00, 24000.00, 34000.00],
    
    // Delhi to Bangalore
    ['AI201', 'Air India', '07:00:00', '09:30:00', 150, 'Delhi', 'Bangalore', 15500.00, 25500.00, 35500.00],
    ['6E401', 'IndiGo', '13:00:00', '15:30:00', 180, 'Delhi', 'Bangalore', 14500.00, 24500.00, 34500.00],
    
    // Mumbai to Bangalore
    ['6E121', 'IndiGo', '08:00:00', '10:00:00', 180, 'Mumbai', 'Bangalore', 8000.00, 14000.00, 22000.00],
    ['AI221', 'Air India', '13:00:00', '15:00:00', 150, 'Mumbai', 'Bangalore', 8500.00, 14500.00, 22500.00],
    
    // Bangalore to Mumbai
    ['6E122', 'IndiGo', '09:00:00', '11:00:00', 180, 'Bangalore', 'Mumbai', 8200.00, 14200.00, 22200.00],
    ['AI222', 'Air India', '14:00:00', '16:00:00', 150, 'Bangalore', 'Mumbai', 8700.00, 14700.00, 22700.00],
    
    // Chennai to Bangalore
    ['6E901', 'IndiGo', '09:00:00', '10:30:00', 180, 'Chennai', 'Bangalore', 6100.00, 12100.00, 20100.00],
    ['SG001', 'SpiceJet', '17:00:00', '18:30:00', 120, 'Chennai', 'Bangalore', 5600.00, 11600.00, 19600.00],
    
    // Bangalore to Chennai
    ['6E801', 'IndiGo', '07:00:00', '08:30:00', 180, 'Bangalore', 'Chennai', 6000.00, 12000.00, 20000.00],
    ['SG901', 'SpiceJet', '15:00:00', '16:30:00', 120, 'Bangalore', 'Chennai', 5500.00, 11500.00, 19500.00],
    
    // Delhi to Mumbai
    ['6E131', 'IndiGo', '07:00:00', '09:30:00', 180, 'Delhi', 'Mumbai', 9000.00, 16000.00, 25000.00],
    ['AI231', 'Air India', '12:00:00', '14:30:00', 150, 'Delhi', 'Mumbai', 9500.00, 16500.00, 25500.00],
    
    // Mumbai to Delhi
    ['6E133', 'IndiGo', '08:00:00', '10:30:00', 180, 'Mumbai', 'Delhi', 9100.00, 16100.00, 25100.00],
    ['AI232', 'Air India', '13:00:00', '15:30:00', 150, 'Mumbai', 'Delhi', 9600.00, 16600.00, 25600.00],
    
    // Kolkata to Mumbai
    ['6E151M', 'IndiGo', '08:00:00', '10:30:00', 180, 'Kolkata', 'Mumbai', 11000.00, 18000.00, 27000.00],
    ['AI251M', 'Air India', '14:00:00', '16:30:00', 150, 'Kolkata', 'Mumbai', 11500.00, 18500.00, 27500.00],
    
    // Mumbai to Kolkata
    ['6E152M', 'IndiGo', '11:00:00', '13:30:00', 180, 'Mumbai', 'Kolkata', 11200.00, 18200.00, 27200.00],
    ['AI252M', 'Air India', '17:00:00', '19:30:00', 150, 'Mumbai', 'Kolkata', 11700.00, 18700.00, 27700.00],
    
    // Pune to Mumbai
    ['6E161P', 'IndiGo', '07:00:00', '07:45:00', 180, 'Pune', 'Mumbai', 3000.00, 6000.00, 10000.00],
    ['AI261P', 'Air India', '13:00:00', '13:45:00', 150, 'Pune', 'Mumbai', 3500.00, 6500.00, 10500.00],
    
    // Mumbai to Pune
    ['6E162P', 'IndiGo', '10:00:00', '10:45:00', 180, 'Mumbai', 'Pune', 3200.00, 6200.00, 10200.00],
    ['AI262P', 'Air India', '16:00:00', '16:45:00', 150, 'Mumbai', 'Pune', 3700.00, 6700.00, 10700.00],
    
    // Goa to Mumbai
    ['6E171G', 'IndiGo', '08:00:00', '09:00:00', 180, 'Goa', 'Mumbai', 4500.00, 8500.00, 14000.00],
    ['AI271G', 'Air India', '13:00:00', '14:00:00', 150, 'Goa', 'Mumbai', 5000.00, 9000.00, 14500.00],
    
    // Mumbai to Goa
    ['6E172G', 'IndiGo', '10:00:00', '11:00:00', 180, 'Mumbai', 'Goa', 4600.00, 8600.00, 14100.00],
    ['AI272G', 'Air India', '15:00:00', '16:00:00', 150, 'Mumbai', 'Goa', 5100.00, 9100.00, 14600.00],
    
    // Kochi to Bangalore
    ['6E181K', 'IndiGo', '08:00:00', '09:30:00', 180, 'Kochi', 'Bangalore', 5500.00, 10500.00, 17500.00],
    ['AI281K', 'Air India', '14:00:00', '15:30:00', 150, 'Kochi', 'Bangalore', 6000.00, 11000.00, 18000.00],
    
    // Bangalore to Kochi
    ['6E182K', 'IndiGo', '10:00:00', '11:30:00', 180, 'Bangalore', 'Kochi', 5700.00, 10700.00, 17700.00],
    ['AI282K', 'Air India', '16:00:00', '17:30:00', 150, 'Bangalore', 'Kochi', 6200.00, 11200.00, 18200.00],
    
    // Ahmedabad to Mumbai
    ['6E191A', 'IndiGo', '07:00:00', '08:00:00', 180, 'Ahmedabad', 'Mumbai', 4000.00, 8000.00, 13000.00],
    ['AI291A', 'Air India', '12:00:00', '13:00:00', 150, 'Ahmedabad', 'Mumbai', 4500.00, 8500.00, 13500.00],
    
    // Mumbai to Ahmedabad
    ['6E192A', 'IndiGo', '09:00:00', '10:00:00', 180, 'Mumbai', 'Ahmedabad', 4200.00, 8200.00, 13200.00],
    ['AI292A', 'Air India', '14:00:00', '15:00:00', 150, 'Mumbai', 'Ahmedabad', 4700.00, 8700.00, 13700.00],
];

$added = 0;
$exists = 0;

foreach ($flights as $flight) {
    list($flight_number, $flight_company, $departing_time, $arrival_time, $no_of_seats, $source, $destination, $price_economy, $price_business, $price_first) = $flight;
    
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
echo "<h2 style='color:#28a745;'>✅ Complete!</h2>";
echo "<p><strong>✓ Added:</strong> $added new flights</p>";
echo "<p><strong>⚠ Already existed:</strong> $exists flights</p>";
echo "<h3>✈️ Routes Available:</h3>";
echo "<ul style='line-height:2;'>";
echo "<li><strong>Hyderabad ↔ Delhi</strong> (3 flights each way)</li>";
echo "<li>Bangalore ↔ Delhi (2 flights each way)</li>";
echo "<li>Mumbai ↔ Bangalore (2 flights each way)</li>";
echo "<li>Chennai ↔ Bangalore (2 flights each way)</li>";
echo "<li>Delhi ↔ Mumbai (2 flights each way)</li>";
echo "<li>Kolkata ↔ Mumbai (2 flights each way)</li>";
echo "<li>Pune ↔ Mumbai (2 flights each way)</li>";
echo "<li>Goa ↔ Mumbai (2 flights each way)</li>";
echo "<li>Kochi ↔ Bangalore (2 flights each way)</li>";
echo "<li>Ahmedabad ↔ Mumbai (2 flights each way)</li>";
echo "</ul>";
echo "</div>";

echo "<hr style='margin:30px 0;'>";
echo "<div style='text-align:center;'>";
echo "<h3>🚀 Ready to Search!</h3>";
echo "<p style='font-size:18px;margin:20px 0;'>Now go to the home page and search for flights!</p>";
echo "<a href='Frontpage.html' class='btn'>🏠 Go to Home Page</a>";
echo "</div>";

echo "</div></body></html>";

mysqli_close($conn);
?>

