<?php
// Add sample bookings for testing the confirmed flights feature

$servername = "localhost";
$username = "root";
$password = "";
$database_name = "airport_management_system";

$conn = mysqli_connect($servername, $username, $password, $database_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// First, add a sample passenger
$passenger_id = 'P001';
$passenger_name = 'John Doe';
$sql = "INSERT IGNORE INTO passenger (p_id, passenger_name, city, email, phone) 
        VALUES ('$passenger_id', '$passenger_name', 'Bangalore', 'john@example.com', '9876543210')";
mysqli_query($conn, $sql);

// Check if we have flights in the database
$flight_check = mysqli_query($conn, "SELECT flight_number FROM flights LIMIT 1");
if (mysqli_num_rows($flight_check) == 0) {
    echo "<h2>No flights found. Please run setup.php first to create flights.</h2>";
    mysqli_close($conn);
    exit;
}

$flight = mysqli_fetch_assoc($flight_check);
$flight_number = $flight['flight_number'];

// Create sample bookings
$bookings = [
    [
        'ticket_number' => 'TKT001',
        'booking_reference' => 'BR001',
        'passenger_name' => 'John Doe',
        'flying_from' => 'Bangalore',
        'flying_to' => 'Delhi',
        'departing_date' => date('Y-m-d', strtotime('+7 days')),
        'price' => 15000,
        'class' => 'Economy',
        'p_id' => $passenger_id
    ],
    [
        'ticket_number' => 'TKT002',
        'booking_reference' => 'BR002',
        'passenger_name' => 'Jane Smith',
        'flying_from' => 'Bangalore',
        'flying_to' => 'Mumbai',
        'departing_date' => date('Y-m-d', strtotime('+10 days')),
        'price' => 12000,
        'class' => 'Business',
        'p_id' => 'P002'
    ],
    [
        'ticket_number' => 'TKT003',
        'booking_reference' => 'BR003',
        'passenger_name' => 'Bob Johnson',
        'flying_from' => 'Delhi',
        'flying_to' => 'Bangalore',
        'departing_date' => date('Y-m-d', strtotime('+14 days')),
        'price' => 18000,
        'class' => 'First',
        'p_id' => 'P003'
    ]
];

// Add additional passengers
mysqli_query($conn, "INSERT IGNORE INTO passenger (p_id, passenger_name, city, email, phone) 
                    VALUES ('P002', 'Jane Smith', 'Bangalore', 'jane@example.com', '9876543211')");
mysqli_query($conn, "INSERT IGNORE INTO passenger (p_id, passenger_name, city, email, phone) 
                    VALUES ('P003', 'Bob Johnson', 'Delhi', 'bob@example.com', '9876543212')");

echo "<h2>Adding Sample Bookings</h2>";

foreach ($bookings as $booking) {
    $sql = "INSERT IGNORE INTO tickets (
        ticket_number, 
        seat_number, 
        passenger_name, 
        flying_to, 
        flying_from, 
        departing_date, 
        price, 
        class, 
        flight_number, 
        p_id, 
        payment_status,
        booking_reference
    ) VALUES (
        '{$booking['ticket_number']}',
        '12A',
        '{$booking['passenger_name']}',
        '{$booking['flying_to']}',
        '{$booking['flying_from']}',
        '{$booking['departing_date']}',
        {$booking['price']},
        '{$booking['class']}',
        '$flight_number',
        '{$booking['p_id']}',
        'Paid',
        '{$booking['booking_reference']}'
    )";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p>✓ Added booking: {$booking['booking_reference']} - {$booking['passenger_name']}</p>";
    } else {
        echo "<p>⚠ Failed to add booking: " . mysqli_error($conn) . "</p>";
    }
    
    // Create corresponding payment record
    $ticket_id_query = "SELECT ticket_id FROM tickets WHERE booking_reference = '{$booking['booking_reference']}'";
    $result = mysqli_query($conn, $ticket_id_query);
    if ($result && $ticket = mysqli_fetch_assoc($result)) {
        $ticket_id = $ticket['ticket_id'];
        $payment_sql = "INSERT IGNORE INTO payments (ticket_id, amount, payment_method, payment_status, transaction_id)
                        VALUES ($ticket_id, {$booking['price']}, 'Credit Card', 'Success', 'TXN{$ticket_id}')";
        mysqli_query($conn, $payment_sql);
    }
}

mysqli_close($conn);

echo "<hr>";
echo "<h3>Sample Bookings Added!</h3>";
echo "<p><a href='Frontpage.html'>View Home Page</a> | <a href='setup.php'>Run Full Setup</a></p>";
?>


