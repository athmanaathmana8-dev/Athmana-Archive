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
    
    if (!isset($data['ticket_id'])) {
        echo json_encode(['success' => false, 'error' => 'Ticket ID is required']);
        mysqli_close($conn);
        exit;
    }
    
    $ticket_id = mysqli_real_escape_string($conn, $data['ticket_id']);
    
    mysqli_begin_transaction($conn);
    
    try {
        $ticket_query = "SELECT * FROM tickets WHERE ticket_id = $ticket_id";
        $ticket_result = mysqli_query($conn, $ticket_query);
        
        if (!$ticket_result || mysqli_num_rows($ticket_result) == 0) {
            throw new Exception("Ticket not found");
        }
        
        $ticket = mysqli_fetch_assoc($ticket_result);
        
        $update_query = "UPDATE tickets 
                        SET payment_status = 'Cancelled' 
                        WHERE ticket_id = $ticket_id";
        
        if (!mysqli_query($conn, $update_query)) {
            throw new Exception("Failed to cancel ticket: " . mysqli_error($conn));
        }
        
        $flight_number = $ticket['flight_number'];
        $seat_number = $ticket['seat_number'];
        
        $free_seat_query = "UPDATE seats 
                           SET is_available = 1, is_reserved = 0, reserved_until = NULL 
                           WHERE flight_number = '$flight_number' 
                           AND seat_number = '$seat_number'";
        
        if (!mysqli_query($conn, $free_seat_query)) {
            throw new Exception("Failed to free seat: " . mysqli_error($conn));
        }
        
        $update_payment_query = "UPDATE payments 
                                 SET payment_status = 'Refunded' 
                                 WHERE ticket_id = $ticket_id";
        
        mysqli_query($conn, $update_payment_query);
        
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'ticket_id' => $ticket_id
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>



























