<?php
/**
 * Get Seat Availability for a Flight
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$servername = "localhost";
$username = "root";
$password = "";
$database_name = "airport_management_system";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $flight_number = $data['flight_number'] ?? '';
    
    if (empty($flight_number)) {
        echo json_encode(['error' => 'Flight number is required']);
        exit;
    }
    
    $conn = new mysqli($servername, $username, $password, $database_name);
    
    if ($conn->connect_error) {
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
    
    // Check which seats are booked by looking at tickets table
    $tickets_stmt = $conn->prepare("
        SELECT seat_number 
        FROM tickets 
        WHERE flight_number = ? AND payment_status != 'Cancelled'
    ");
    $tickets_stmt->bind_param('s', $flight_number);
    $tickets_stmt->execute();
    $tickets_result = $tickets_stmt->get_result();
    
    $booked_seats = [];
    while ($row = $tickets_result->fetch_assoc()) {
        $booked_seats[$row['seat_number']] = true;
    }
    $tickets_stmt->close();
    
    // Get all seats for this flight from seats table
    $stmt = $conn->prepare("SELECT seat_number, is_available FROM seats WHERE flight_number = ?");
    $stmt->bind_param('s', $flight_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $seats = [];
    while ($row = $result->fetch_assoc()) {
        // Seat is unavailable if it's in booked_seats OR if is_available = 0 in seats table
        $is_booked = isset($booked_seats[$row['seat_number']]);
        $seats[$row['seat_number']] = [
            'available' => !$is_booked && (bool)$row['is_available']
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'flight_number' => $flight_number,
        'seats' => $seats
    ]);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>

