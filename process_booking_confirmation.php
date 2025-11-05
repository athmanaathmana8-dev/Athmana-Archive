<?php
/**
 * Process Booking Confirmation
 * Handles the complete booking confirmation process with unique reference generation
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include the booking reference generator
require_once 'booking_reference_generator.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get JSON input
        $input = file_get_contents('php://input');
        $booking_data = json_decode($input, true);
        
        if (!$booking_data) {
            throw new Exception('Invalid JSON input');
        }
        
        // Validate required fields
        $required_fields = ['passenger_name', 'flying_from', 'flying_to', 'departing_date', 'selected_flight', 'class', 'selected_seat'];
        foreach ($required_fields as $field) {
            if (empty($booking_data[$field])) {
                throw new Exception("Required field '{$field}' is missing");
            }
        }
        
        // Set default values for optional fields
        $booking_data['email'] = $booking_data['email'] ?? '';
        $booking_data['phone'] = $booking_data['phone'] ?? '';
        $booking_data['p_id'] = $booking_data['p_id'] ?? 'GUEST' . rand(1000, 9999);
        $booking_data['city'] = $booking_data['city'] ?? '';
        $booking_data['date_of_birth'] = $booking_data['date_of_birth'] ?? null;
        $booking_data['payment_method'] = $booking_data['payment_method'] ?? 'Credit Card';
        
        // Process the booking confirmation
        $result = processBookingConfirmation($booking_data);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Booking confirmed successfully',
            'booking_reference' => $result['booking_reference'],
            'ticket_number' => $result['ticket_number'],
            'transaction_id' => $result['transaction_id'],
            'passenger_id' => $result['passenger_id'],
            'ticket_id' => $result['ticket_id'],
            'price' => $result['price']
        ]);
        
    } catch (Exception $e) {
        // Return error response
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
}
?>

