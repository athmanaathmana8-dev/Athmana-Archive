<?php
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

$conn = mysqli_connect($servername, $username, $password, $database_name);

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $booking_reference = isset($data['booking_reference']) ? trim(mysqli_real_escape_string($conn, $data['booking_reference'])) : '';
    $last_name = isset($data['last_name']) ? mysqli_real_escape_string($conn, $data['last_name']) : '';
    
    if (empty($booking_reference)) {
        echo json_encode([
            'success' => false,
            'error' => 'Booking reference is required'
        ]);
        mysqli_close($conn);
        exit;
    }
    
    // Get booking details by reference (case-insensitive search)
    // Only apply last_name filter if it's a meaningful search (not 'DETAILS' placeholder)
    $where_clause = "WHERE UPPER(TRIM(t.booking_reference)) = UPPER('$booking_reference')";
    if (!empty($last_name) && strtoupper(trim($last_name)) !== 'DETAILS') {
        $where_clause .= " AND t.passenger_name LIKE '%$last_name%'";
    }
    
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
        p.city,
        p.date_of_birth
    FROM tickets t
    LEFT JOIN flights f ON t.flight_number = f.flight_number
    LEFT JOIN passenger p ON t.p_id = p.p_id
    $where_clause
    AND (t.payment_status = 'Paid' OR t.payment_status = 'Pending')
    ORDER BY t.ticket_id ASC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode([
            'success' => false,
            'error' => 'Query failed: ' . mysqli_error($conn)
        ]);
        mysqli_close($conn);
        exit;
    }
    
    if (mysqli_num_rows($result) > 0) {
        $bookings = [];
        while ($booking = mysqli_fetch_assoc($result)) {
            // Format the data for each booking
            $bookings[] = [
                'ticket_id' => $booking['ticket_id'],
                'ticket_number' => $booking['ticket_number'] ?? '',
                'booking_reference' => $booking['booking_reference'] ?? '',
                'passenger_name' => $booking['passenger_name'] ?? '',
                'email' => $booking['email'] ?? '',
                'phone' => $booking['phone'] ?? '',
                'city' => $booking['city'] ?? '',
                'p_id' => $booking['p_id'] ?? '',
                'date_of_birth' => $booking['date_of_birth'] ?? '',
                'flight_number' => $booking['flight_number'] ?? '',
                'flight_company' => $booking['flight_company'] ?? '',
                'from' => $booking['flying_from'] ?? '',
                'to' => $booking['flying_to'] ?? '',
                'departure_date' => $booking['departing_date'] ?? '',
                'departure_time' => $booking['departing_time'] ?? '',
                'arrival_time' => $booking['arrival_time'] ?? '',
                'seat_number' => $booking['seat_number'] ?? '',
                'class' => $booking['class'] ?? '',
                'price' => isset($booking['price']) ? (float)$booking['price'] : 0.00,
                'status' => $booking['payment_status'] ?? 'Unknown',
                'payment_status' => $booking['payment_status'] ?? 'Unknown'
            ];
        }
        
        // For backward compatibility, include the first booking as 'booking'
        $response = [
            'success' => true,
            'booking' => $bookings[0], // First booking for backward compatibility
            'bookings' => $bookings     // All bookings array
        ];
    } else {
        // Debug: Check if booking reference exists with different case or formatting
        $debug_query = "SELECT booking_reference FROM tickets WHERE booking_reference LIKE '%" . mysqli_real_escape_string($conn, substr($booking_reference, -5)) . "%' LIMIT 5";
        $debug_result = mysqli_query($conn, $debug_query);
        
        $response = [
            'success' => false,
            'error' => 'No booking found with the provided reference. Please check your booking reference and try again.',
            'debug_info' => [
                'searched_reference' => $booking_reference,
                'similar_references' => mysqli_num_rows($debug_result) > 0 ? mysqli_fetch_all($debug_result, MYSQLI_ASSOC) : []
            ]
        ];
    }
    
    echo json_encode($response);
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>

