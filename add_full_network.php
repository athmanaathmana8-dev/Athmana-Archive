<?php
// Adds at least two flights for every ordered city pair in the main network

$servername = "localhost";
$username = "root";
$password = "";
$database_name = "airport_management_system";

$conn = mysqli_connect($servername, $username, $password, $database_name);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2>Building full flight network (at least 2 flights per route)</h2>";

$cities = [
    'Bangalore', 'Delhi', 'Mumbai', 'Chennai', 'Hyderabad',
    'Kolkata', 'Pune', 'Ahmedabad', 'Kochi', 'Goa'
];

$airlines = [
    ['code' => '6E', 'name' => 'IndiGo',    'base' => 200],
    ['code' => 'AI', 'name' => 'Air India', 'base' => 300],
    ['code' => 'SG', 'name' => 'SpiceJet',  'base' => 400],
    ['code' => 'UK', 'name' => 'Vistara',   'base' => 500]
];

$added = 0;
$skipped = 0;

// Helper: does flight number already exist?
function flightExists($conn, $flightNumber) {
    $res = mysqli_query($conn, "SELECT 1 FROM flights WHERE flight_number='" . mysqli_real_escape_string($conn, $flightNumber) . "' LIMIT 1");
    return $res && mysqli_num_rows($res) > 0;
}

// Helper: generate a unique flight number
function generateFlightNumber($conn, $airlineCode, $base, $increment) {
    $num = $base + $increment;
    $candidate = $airlineCode . $num;
    $tries = 0;
    while (flightExists($conn, $candidate) && $tries < 1000) {
        $num++;
        $candidate = $airlineCode . $num;
        $tries++;
    }
    return $candidate;
}

// Helper: create seats for a flight
function createSeats($conn, $flightNumber, $totalSeats) {
    $seatsPerRow = 6;
    $rows = (int)ceil($totalSeats / $seatsPerRow);
    for ($row = 1; $row <= $rows; $row++) {
        for ($col = 1; $col <= $seatsPerRow; $col++) {
            if ((($row - 1) * $seatsPerRow + $col) > $totalSeats) break;
            $seatLetter = chr(64 + $col);
            $seatNumber = $row . $seatLetter;
            $seatClass = 'Economy';
            if ($row <= 2) $seatClass = 'First';
            elseif ($row <= 4) $seatClass = 'Business';
            $sql = "INSERT IGNORE INTO seats (flight_number, seat_number, seat_class, is_available, is_reserved) VALUES ('" .
                   mysqli_real_escape_string($conn, $flightNumber) . "', '" .
                   mysqli_real_escape_string($conn, $seatNumber) . "', '" .
                   mysqli_real_escape_string($conn, $seatClass) . "', 1, 0)";
            mysqli_query($conn, $sql);
        }
    }
}

// Ensure at least two flights for each ordered pair
foreach ($cities as $from) {
    foreach ($cities as $to) {
        if ($from === $to) continue;

        $countSql = sprintf(
            "SELECT COUNT(*) AS c FROM flights WHERE source='%s' AND destination='%s'",
            mysqli_real_escape_string($conn, $from),
            mysqli_real_escape_string($conn, $to)
        );
        $countRes = mysqli_query($conn, $countSql);
        $row = $countRes ? mysqli_fetch_assoc($countRes) : ['c' => 0];
        $existing = (int)$row['c'];

        for ($i = $existing; $i < 2; $i++) {
            // Pick airline round-robin
            $air = $airlines[($i) % count($airlines)];
            $flightCompany = $air['name'];
            $flightNumber = generateFlightNumber($conn, $air['code'], $air['base'], rand(1, 900));

            // Simple time slots
            $depHour = 6 + ($i * 6); // 6:00, 12:00
            $arrHour = $depHour + 2; // +2 hours
            $departing = sprintf("%02d:00:00", $depHour);
            $arriving = sprintf("%02d:00:00", $arrHour);

            $noOfSeats = 180;
            // Prices vary slightly
            $priceEconomy  = 4500 + (rand(0, 10) * 100);
            $priceBusiness = 9000 + (rand(0, 10) * 100);
            $priceFirst    = 15000 + (rand(0, 10) * 100);

            $insert = sprintf(
                "INSERT INTO flights (flight_number, flight_company, departing_time, arrival_time, no_of_seats, source, destination, price_economy, price_business, price_first) VALUES ('%s','%s','%s','%s',%d,'%s','%s',%.2f,%.2f,%.2f)",
                mysqli_real_escape_string($conn, $flightNumber),
                mysqli_real_escape_string($conn, $flightCompany),
                mysqli_real_escape_string($conn, $departing),
                mysqli_real_escape_string($conn, $arriving),
                $noOfSeats,
                mysqli_real_escape_string($conn, $from),
                mysqli_real_escape_string($conn, $to),
                $priceEconomy,
                $priceBusiness,
                $priceFirst
            );

            if (mysqli_query($conn, $insert)) {
                echo "<p class='success'>✓ Added: {$flightCompany} {$flightNumber} ({$from} → {$to})</p>";
                createSeats($conn, $flightNumber, $noOfSeats);
                $added++;
            } else {
                echo "<p class='error'>✗ Failed: {$from} → {$to} :: " . mysqli_error($conn) . "</p>";
                $skipped++;
            }
        }
    }
}

echo "<hr><h3>Summary</h3>";
echo "<p>Added: {$added}</p>";
echo "<p>Skipped/Errors: {$skipped}</p>";
echo "<p><a href='Frontpage.html'>Go to Home</a> | <a href='Ticketbooking.html'>Book Ticket</a></p>";

mysqli_close($conn);
?>




