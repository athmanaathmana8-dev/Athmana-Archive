<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'get_seats':
            getSeats($conn, $input);
            break;
        case 'reserve_seat':
            reserveSeat($conn, $input);
            break;
        case 'cancel_reservation':
            cancelReservation($conn, $input);
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}

function getSeats($conn, $input) {
    $flight_number = mysqli_real_escape_string($conn, $input['flight_number'] ?? '');
    
    if (empty($flight_number)) {
        echo json_encode(['error' => 'Flight number is required']);
        return;
    }
    
    // Get flight details
    $flight_sql = "SELECT * FROM flights WHERE flight_number = '$flight_number'";
    $flight_result = mysqli_query($conn, $flight_sql);
    
    if (mysqli_num_rows($flight_result) == 0) {
        echo json_encode(['error' => 'Flight not found']);
        return;
    }
    
    $flight = mysqli_fetch_assoc($flight_result);
    
    // Get seats for this flight
    $seats_sql = "SELECT * FROM seats WHERE flight_number = '$flight_number' ORDER BY seat_number";
    $seats_result = mysqli_query($conn, $seats_sql);
    
    $seats = [];
    while ($row = mysqli_fetch_assoc($seats_result)) {
        $seats[] = [
            'seat_id' => $row['seat_id'],
            'seat_number' => $row['seat_number'],
            'seat_class' => $row['seat_class'],
            'is_available' => (bool)$row['is_available'],
            'is_reserved' => (bool)$row['is_reserved'],
            'reserved_until' => $row['reserved_until']
        ];
    }
    
    // If no seats exist, create them
    if (empty($seats)) {
        createSeatsForFlight($conn, $flight_number, $flight['no_of_seats']);
        getSeats($conn, $input); // Recursive call to get the created seats
        return;
    }
    
    echo json_encode([
        'flight' => $flight,
        'seats' => $seats
    ]);
}

function createSeatsForFlight($conn, $flight_number, $total_seats) {
    $seats_per_row = 6;
    $rows = ceil($total_seats / $seats_per_row);
    
    for ($row = 1; $row <= $rows; $row++) {
        for ($col = 1; $col <= $seats_per_row; $col++) {
            if (($row - 1) * $seats_per_row + $col > $total_seats) break;
            
            $seat_letter = chr(64 + $col); // A, B, C, D, E, F
            $seat_number = $row . $seat_letter;
            
            // Determine seat class based on row
            $seat_class = 'Economy';
            if ($row <= 2) {
                $seat_class = 'First';
            } elseif ($row <= 4) {
                $seat_class = 'Business';
            }
            
            $insert_sql = "INSERT INTO seats (flight_number, seat_number, seat_class, is_available, is_reserved) 
                          VALUES ('$flight_number', '$seat_number', '$seat_class', 1, 0)";
            mysqli_query($conn, $insert_sql);
        }
    }
}

function reserveSeat($conn, $input) {
    $seat_id = mysqli_real_escape_string($conn, $input['seat_id'] ?? '');
    $p_id = mysqli_real_escape_string($conn, $input['p_id'] ?? '');
    $flight_number = mysqli_real_escape_string($conn, $input['flight_number'] ?? '');
    $reserve_duration = 15; // 15 minutes reservation
    
    if (empty($seat_id) || empty($p_id) || empty($flight_number)) {
        echo json_encode(['error' => 'Missing required parameters']);
        return;
    }
    
    // Check if seat is available
    $check_sql = "SELECT * FROM seats WHERE seat_id = '$seat_id' AND is_available = 1 AND is_reserved = 0";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) == 0) {
        echo json_encode(['error' => 'Seat is not available']);
        return;
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update seat as reserved
        $reserved_until = date('Y-m-d H:i:s', time() + ($reserve_duration * 60));
        $update_seat_sql = "UPDATE seats SET is_reserved = 1, reserved_until = '$reserved_until' WHERE seat_id = '$seat_id'";
        
        if (!mysqli_query($conn, $update_seat_sql)) {
            throw new Exception("Error updating seat: " . mysqli_error($conn));
        }
        
        // Create reservation record
        $reservation_sql = "INSERT INTO reservations (p_id, flight_number, seat_id, reserved_until) 
                           VALUES ('$p_id', '$flight_number', '$seat_id', '$reserved_until')";
        
        if (!mysqli_query($conn, $reservation_sql)) {
            throw new Exception("Error creating reservation: " . mysqli_error($conn));
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Seat reserved successfully',
            'reserved_until' => $reserved_until
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function cancelReservation($conn, $input) {
    $reservation_id = mysqli_real_escape_string($conn, $input['reservation_id'] ?? '');
    
    if (empty($reservation_id)) {
        echo json_encode(['error' => 'Reservation ID is required']);
        return;
    }
    
    // Get reservation details
    $get_reservation_sql = "SELECT * FROM reservations WHERE reservation_id = '$reservation_id' AND status = 'Active'";
    $get_reservation_result = mysqli_query($conn, $get_reservation_sql);
    
    if (mysqli_num_rows($get_reservation_result) == 0) {
        echo json_encode(['error' => 'Reservation not found or already expired']);
        return;
    }
    
    $reservation = mysqli_fetch_assoc($get_reservation_result);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update seat as available
        $update_seat_sql = "UPDATE seats SET is_reserved = 0, reserved_until = NULL WHERE seat_id = '" . $reservation['seat_id'] . "'";
        
        if (!mysqli_query($conn, $update_seat_sql)) {
            throw new Exception("Error updating seat: " . mysqli_error($conn));
        }
        
        // Update reservation as expired
        $update_reservation_sql = "UPDATE reservations SET status = 'Expired' WHERE reservation_id = '$reservation_id'";
        
        if (!mysqli_query($conn, $update_reservation_sql)) {
            throw new Exception("Error updating reservation: " . mysqli_error($conn));
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode(['success' => true, 'message' => 'Reservation cancelled successfully']);
        
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

mysqli_close($conn);
?>






