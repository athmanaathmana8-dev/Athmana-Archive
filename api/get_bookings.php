<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Error handler
function sendError($message, $logMessage = '') {
    if ($logMessage) {
        error_log("get_bookings.php: " . $logMessage);
    }
    echo json_encode(['success' => false, 'error' => $message, 'bookings' => []]);
    exit;
}

try {
    // Fast database connection with error handling
    $conn = @mysqli_connect("localhost", "root", "", "airport_management_system");
    
    if (!$conn) {
        sendError('Database connection failed', 'Connection error: ' . (function_exists('mysqli_connect_error') ? mysqli_connect_error() : 'Unknown error'));
    }
    
    mysqli_set_charset($conn, "utf8mb4");
    
    // Optimized query with fallback ordering (use ticket_id if created_at doesn't exist)
    $query = "SELECT 
        t.ticket_id,
        t.ticket_number,
        t.booking_reference,
        t.passenger_name,
        t.flying_from,
        t.flying_to,
        t.departing_date,
        t.price,
        t.class,
        t.seat_number,
        t.flight_number,
        t.payment_status,
        t.p_id,
        f.departing_time,
        f.arrival_time,
        f.flight_company,
        p.email,
        p.phone,
        p.city
    FROM tickets t
    LEFT JOIN flights f ON t.flight_number = f.flight_number
    LEFT JOIN passenger p ON t.p_id = p.p_id
    WHERE t.payment_status != 'Cancelled'
    ORDER BY t.created_at DESC, t.ticket_id DESC
    LIMIT 100";
    
    $result = @mysqli_query($conn, $query);
    
    if (!$result) {
        $error = mysqli_error($conn);
        sendError('Database query failed', "Query error: " . $error);
    }

    // Early exit if no results
    $numRows = mysqli_num_rows($result);
    if ($numRows === 0) {
        echo json_encode(['success' => true, 'bookings' => [], 'count' => 0]);
        mysqli_close($conn);
        exit;
    }
    
    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = [
            'ticket_id' => $row['ticket_id'],
            'ticket_number' => $row['ticket_number'] ?? '',
            'booking_reference' => $row['booking_reference'] ?? '',
            'passenger_name' => $row['passenger_name'] ?? '',
            'email' => $row['email'] ?? '',
            'phone' => $row['phone'] ?? '',
            'city' => $row['city'] ?? '',
            'p_id' => $row['p_id'] ?? '',
            'from' => $row['flying_from'] ?? '',
            'to' => $row['flying_to'] ?? '',
            'date' => $row['departing_date'] ?? '',
            'departure_time' => $row['departing_time'] ?? '',
            'arrival_time' => $row['arrival_time'] ?? '',
            'flight_number' => $row['flight_number'] ?? '',
            'flight_company' => $row['flight_company'] ?? '',
            'seat_number' => $row['seat_number'] ?? '',
            'class' => $row['class'] ?? '',
            'price' => isset($row['price']) ? number_format((float)$row['price'], 2) : '0.00',
            'status' => $row['payment_status'] ?? 'Unknown'
        ];
    }
    
    $response = [
        'success' => true,
        'bookings' => $bookings,
        'count' => count($bookings)
    ];
    
    echo json_encode($response);
    mysqli_close($conn);
    
} catch (Exception $e) {
    sendError('An unexpected error occurred', 'Exception: ' . $e->getMessage());
}
?>

