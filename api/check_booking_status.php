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
    
    // Clean and prepare booking reference (remove spaces, handle BR- prefix)
    $booking_ref_clean = strtoupper(trim(str_replace(' ', '', $booking_reference)));
    if (!empty($booking_ref_clean) && substr($booking_ref_clean, 0, 3) !== 'BR-') {
        // If it doesn't start with BR-, try to add it if it looks like a reference code
        if (preg_match('/^[A-Z0-9]{8,}$/', $booking_ref_clean)) {
            $booking_ref_clean = 'BR-' . $booking_ref_clean;
        }
    }
    
    // Build WHERE clause - search by booking reference (case-insensitive, flexible)
    $where_conditions = [];
    $booking_ref_escaped = mysqli_real_escape_string($conn, $booking_ref_clean);
    $where_conditions[] = "(UPPER(TRIM(REPLACE(t.booking_reference, ' ', ''))) = UPPER('$booking_ref_escaped') 
                           OR UPPER(TRIM(t.booking_reference)) = UPPER('$booking_ref_escaped'))";
    
    // Add name filter if provided (case-insensitive, flexible matching)
    if (!empty($last_name) && strtoupper(trim($last_name)) !== 'DETAILS') {
        $name_clean = mysqli_real_escape_string($conn, trim($last_name));
        $where_conditions[] = "UPPER(t.passenger_name) LIKE UPPER('%$name_clean%')";
    }
    
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    
    // Query to get booking details - fetch confirmed flights (Paid status) and other bookings
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
        t.created_at,
        f.departing_time,
        f.arrival_time,
        f.flight_company,
        f.price_economy,
        f.price_business,
        f.price_first,
        p.email,
        p.phone,
        p.city,
        p.date_of_birth
    FROM tickets t
    LEFT JOIN flights f ON t.flight_number = f.flight_number
    LEFT JOIN passenger p ON t.p_id = p.p_id
    $where_clause
    ORDER BY 
        CASE WHEN t.payment_status = 'Paid' THEN 0 ELSE 1 END,
        t.created_at DESC, 
        t.ticket_id DESC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode([
            'success' => false,
            'error' => 'Query failed: ' . mysqli_error($conn),
            'debug_query' => $query
        ]);
        mysqli_close($conn);
        exit;
    }
    
    if (mysqli_num_rows($result) > 0) {
        $bookings = [];
        $confirmed_count = 0;
        
        while ($booking = mysqli_fetch_assoc($result)) {
            // Determine status - show 'Confirmed' for Paid bookings
            $payment_status = $booking['payment_status'] ?? 'Unknown';
            $display_status = ($payment_status === 'Paid') ? 'Confirmed' : $payment_status;
            
            // Format price with proper currency formatting
            $price = isset($booking['price']) ? (float)$booking['price'] : 0.00;
            $price_formatted = number_format($price, 2, '.', ',');
            
            // Format date properly
            $departure_date = $booking['departing_date'] ?? '';
            if ($departure_date && $departure_date !== '0000-00-00') {
                $date_obj = DateTime::createFromFormat('Y-m-d', $departure_date);
                if ($date_obj) {
                    $departure_date_formatted = $date_obj->format('Y-m-d');
                } else {
                    $departure_date_formatted = $departure_date;
                }
            } else {
                $departure_date_formatted = '';
            }
            
            // Format time (remove seconds if present)
            $departure_time = $booking['departing_time'] ?? '';
            $arrival_time = $booking['arrival_time'] ?? '';
            if ($departure_time && strlen($departure_time) > 5) {
                $departure_time = substr($departure_time, 0, 5);
            }
            if ($arrival_time && strlen($arrival_time) > 5) {
                $arrival_time = substr($arrival_time, 0, 5);
            }
            
            // Count confirmed bookings
            if ($payment_status === 'Paid') {
                $confirmed_count++;
            }
            
            // Format the data for each booking with all confirmed flight details
            $bookings[] = [
                'ticket_id' => $booking['ticket_id'] ?? 0,
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
                'departure_date' => $departure_date_formatted,
                'departure_time' => $departure_time,
                'arrival_time' => $arrival_time,
                'seat_number' => $booking['seat_number'] ?? '',
                'class' => $booking['class'] ?? '',
                'price' => $price,
                'price_formatted' => $price_formatted,
                'status' => $display_status,
                'payment_status' => $payment_status,
                'is_confirmed' => ($payment_status === 'Paid'),
                'created_at' => $booking['created_at'] ?? ''
            ];
        }
        
        // For backward compatibility, include the first booking as 'booking'
        $response = [
            'success' => true,
            'booking' => $bookings[0], // First booking for backward compatibility
            'bookings' => $bookings,     // All bookings array
            'confirmed_count' => $confirmed_count,
            'total_count' => count($bookings),
            'message' => $confirmed_count > 0 ? 
                "Found {$confirmed_count} confirmed flight(s)" : 
                "Found " . count($bookings) . " booking(s)"
        ];
    } else {
        // Try to find booking by reference only (without name filter) if name was provided
        if (!empty($last_name) && strtoupper(trim($last_name)) !== 'DETAILS') {
            $ref_only_query = "SELECT 
                t.ticket_id,
                t.booking_reference,
                t.passenger_name
            FROM tickets t
            WHERE (UPPER(TRIM(REPLACE(t.booking_reference, ' ', ''))) = UPPER('$booking_ref_escaped') 
                   OR UPPER(TRIM(t.booking_reference)) = UPPER('$booking_ref_escaped'))
            LIMIT 1";
            
            $ref_only_result = mysqli_query($conn, $ref_only_query);
            if ($ref_only_result && mysqli_num_rows($ref_only_result) > 0) {
                $ref_booking = mysqli_fetch_assoc($ref_only_result);
                $response = [
                    'success' => false,
                    'error' => 'Booking reference found, but the name does not match. Please verify the name you entered matches the booking.',
                    'hint' => 'Found booking for: ' . ($ref_booking['passenger_name'] ?? 'Unknown')
                ];
            } else {
                $response = [
                    'success' => false,
                    'error' => 'No booking found with the provided reference. Please check your booking reference and try again.'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'error' => 'No booking found with the provided reference. Please check your booking reference and try again.'
            ];
        }
    }
    
    echo json_encode($response);
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>

