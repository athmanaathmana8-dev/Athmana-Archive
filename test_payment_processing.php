<?php
// Test the payment processing with sample data
header('Content-Type: application/json');

$test_data = [
    'passenger_name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '9876543210',
    'flying_from' => 'Bangalore',
    'flying_to' => 'Delhi',
    'departing_date' => '2024-12-25',
    'selected_flight' => 'AI101',
    'class' => 'Economy',
    'selected_seat' => '12A',
    'payment_method' => 'Credit Card'
];

echo "<h2>Testing Payment Processing</h2>";
echo "<pre>";
echo "Test data:\n";
print_r($test_data);

// Test flight number cleaning
$flight_number = $test_data['selected_flight'];

echo "\nOriginal flight number: " . $flight_number;

// Handle cases where flight might be passed as "Company FlightNumber" or "Company FlightNumber / Company FlightNumber"
if (strpos($flight_number, '/') !== false) {
    $flight_number = trim(explode('/', $flight_number)[0]);
    echo "\nAfter splitting by /: " . $flight_number;
}

// Extract flight number from company name if needed (e.g., "Air India AI101" -> "AI101")
if (preg_match('/[A-Z]{2,3}\d{2,4}/', $flight_number, $matches)) {
    $flight_number = $matches[0];
    echo "\nAfter regex extraction: " . $flight_number;
}

echo "\nFinal flight number: " . $flight_number;

// Test database connection and flight lookup
$servername = "localhost";
$username = "root";
$password = "";
$database_name = "airport_management_system";

$conn = mysqli_connect($servername, $username, $password, $database_name);

if (!$conn) {
    echo "\nDatabase connection failed: " . mysqli_connect_error();
} else {
    echo "\nDatabase connected successfully";
    
    $stmt = $conn->prepare("SELECT * FROM flights WHERE flight_number = ?");
    $stmt->bind_param('s', $flight_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $flight = $result->fetch_assoc();
        echo "\nFlight found: " . $flight['flight_company'] . " " . $flight['flight_number'];
        echo "\nRoute: " . $flight['source'] . " → " . $flight['destination'];
        echo "\nEconomy Price: ₹" . $flight['price_economy'];
    } else {
        echo "\nFlight NOT found: " . $flight_number;
        
        // Show available flights
        echo "\nAvailable flights:";
        $all_flights = mysqli_query($conn, "SELECT flight_number, flight_company FROM flights LIMIT 10");
        while ($row = mysqli_fetch_assoc($all_flights)) {
            echo "\n- " . $row['flight_number'] . " (" . $row['flight_company'] . ")";
        }
    }
    
    $stmt->close();
    mysqli_close($conn);
}

echo "</pre>";
?>

