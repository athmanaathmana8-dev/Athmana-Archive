<?php
/**
 * Unique Booking Reference ID Generator
 * Generates cryptographically secure, unique booking reference IDs
 */

class BookingReferenceGenerator {
    
    private $db_connection;
    
    public function __construct($connection) {
        $this->db_connection = $connection;
    }
    
    /**
     * Generate a unique booking reference ID
     * Format: BR + 8-character alphanumeric code
     * Example: BR-A7B9C2D1
     */
    public function generateUniqueReference() {
        $max_attempts = 10;
        $attempt = 0;
        
        do {
            $attempt++;
            
            // Generate a random 8-character alphanumeric code
            $random_part = $this->generateRandomCode(8);
            $booking_reference = 'BR-' . $random_part;
            
            // Check if this reference already exists
            if (!$this->referenceExists($booking_reference)) {
                return $booking_reference;
            }
            
        } while ($attempt < $max_attempts);
        
        // If we couldn't generate a unique reference after max attempts
        throw new Exception("Unable to generate unique booking reference after {$max_attempts} attempts");
    }
    
    /**
     * Generate a random alphanumeric code
     */
    private function generateRandomCode($length) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        
        // Use cryptographically secure random number generator
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $code;
    }
    
    /**
     * Check if a booking reference already exists in the database
     */
    private function referenceExists($booking_reference) {
        $stmt = $this->db_connection->prepare("SELECT COUNT(*) FROM tickets WHERE booking_reference = ?");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $this->db_connection->error);
        }
        
        $stmt->bind_param('s', $booking_reference);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_row()[0];
        $stmt->close();
        
        return $count > 0;
    }
    
    /**
     * Generate a unique ticket number
     * Format: TKT + YYYYMMDD + 4-digit random number
     * Example: TKT202412251234
     */
    public function generateUniqueTicketNumber() {
        $max_attempts = 10;
        $attempt = 0;
        
        do {
            $attempt++;
            
            $date_part = date('Ymd');
            $random_part = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $ticket_number = 'TKT' . $date_part . $random_part;
            
            // Check if this ticket number already exists
            if (!$this->ticketNumberExists($ticket_number)) {
                return $ticket_number;
            }
            
        } while ($attempt < $max_attempts);
        
        throw new Exception("Unable to generate unique ticket number after {$max_attempts} attempts");
    }
    
    /**
     * Check if a ticket number already exists in the database
     */
    private function ticketNumberExists($ticket_number) {
        $stmt = $this->db_connection->prepare("SELECT COUNT(*) FROM tickets WHERE ticket_number = ?");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $this->db_connection->error);
        }
        
        $stmt->bind_param('s', $ticket_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_row()[0];
        $stmt->close();
        
        return $count > 0;
    }
    
    /**
     * Generate a unique transaction ID for payment
     */
    public function generateTransactionId() {
        $timestamp = time();
        $random_part = $this->generateRandomCode(6);
        return 'TXN' . $timestamp . $random_part;
    }
    
    /**
     * Generate a unique passenger ID
     * Format: 8-character alphanumeric lowercase
     * Example: a7b9c2d1
     */
    public function generateUniquePassengerId() {
        $max_attempts = 10;
        $attempt = 0;
        
        do {
            $attempt++;
            
            // Generate a random 8-character lowercase alphanumeric code
            $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $p_id = '';
            for ($i = 0; $i < 8; $i++) {
                $p_id .= $characters[random_int(0, strlen($characters) - 1)];
            }
            
            // Check if this passenger ID already exists
            if (!$this->passengerIdExists($p_id)) {
                return $p_id;
            }
            
        } while ($attempt < $max_attempts);
        
        throw new Exception("Unable to generate unique passenger ID after {$max_attempts} attempts");
    }
    
    /**
     * Check if a passenger ID already exists in the database
     */
    private function passengerIdExists($p_id) {
        $stmt = $this->db_connection->prepare("SELECT COUNT(*) FROM passenger WHERE p_id = ?");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $this->db_connection->error);
        }
        
        $stmt->bind_param('s', $p_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_row()[0];
        $stmt->close();
        
        return $count > 0;
    }
}

/**
 * Booking Confirmation Handler
 * Handles the complete booking confirmation process
 */
class BookingConfirmationHandler {
    
    private $db_connection;
    private $reference_generator;
    
    public function __construct($connection) {
        $this->db_connection = $connection;
        $this->reference_generator = new BookingReferenceGenerator($connection);
    }
    
    /**
     * Process a complete booking confirmation
     */
    public function processBooking($booking_data) {
        // Validate required fields
        $required_fields = ['passenger_name', 'email', 'phone', 'p_id', 'flying_from', 'flying_to', 'departing_date', 'selected_flight', 'class', 'selected_seat', 'payment_method'];
        
        foreach ($required_fields as $field) {
            if (empty($booking_data[$field])) {
                throw new Exception("Required field '{$field}' is missing");
            }
        }
        
        // Start database transaction
        $this->db_connection->begin_transaction();
        
        try {
            // Generate unique identifiers
            $booking_reference = $this->reference_generator->generateUniqueReference();
            $ticket_number = $this->reference_generator->generateUniqueTicketNumber();
            $transaction_id = $this->reference_generator->generateTransactionId();
            
            // Get flight details and pricing
            $flight_details = $this->getFlightDetails($booking_data['selected_flight']);
            $price = $this->calculatePrice($flight_details, $booking_data['class']);
            
            // Validate seat availability
            $this->validateSeatAvailability($booking_data['selected_flight'], $booking_data['selected_seat']);
            
            // Insert passenger record
            $passenger_id = $this->insertPassenger($booking_data);
            
            // Insert ticket record
            $ticket_id = $this->insertTicket($booking_data, $passenger_id, $ticket_number, $booking_reference, $price);
            
            // Insert payment record
            $this->insertPayment($ticket_id, $price, $booking_data['payment_method'], $transaction_id);
            
            // Update seat as occupied
            $this->updateSeatStatus($booking_data['selected_flight'], $booking_data['selected_seat'], false);
            
            // Commit transaction
            $this->db_connection->commit();
            
            // Return booking confirmation data
            return [
                'success' => true,
                'booking_reference' => $booking_reference,
                'ticket_number' => $ticket_number,
                'transaction_id' => $transaction_id,
                'passenger_id' => $passenger_id,
                'ticket_id' => $ticket_id,
                'price' => $price,
                'flight_details' => $flight_details
            ];
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db_connection->rollback();
            throw $e;
        }
    }
    
    /**
     * Get flight details from database
     */
    private function getFlightDetails($flight_number) {
        $stmt = $this->db_connection->prepare("SELECT * FROM flights WHERE flight_number = ?");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $this->db_connection->error);
        }
        
        $stmt->bind_param('s', $flight_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            throw new Exception("Flight not found: {$flight_number}");
        }
        
        $flight_details = $result->fetch_assoc();
        $stmt->close();
        
        return $flight_details;
    }
    
    /**
     * Calculate price based on flight details and class
     */
    private function calculatePrice($flight_details, $class) {
        switch ($class) {
            case 'Economy':
                return $flight_details['price_economy'];
            case 'Business':
                return $flight_details['price_business'];
            case 'First':
                return $flight_details['price_first'];
            default:
                throw new Exception("Invalid travel class: {$class}");
        }
    }
    
    /**
     * Validate seat availability
     */
    private function validateSeatAvailability($flight_number, $seat_number) {
        $stmt = $this->db_connection->prepare("SELECT COUNT(*) FROM seats WHERE flight_number = ? AND seat_number = ? AND is_available = 1");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $this->db_connection->error);
        }
        
        $stmt->bind_param('ss', $flight_number, $seat_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_row()[0];
        $stmt->close();
        
        if ($count === 0) {
            throw new Exception("Seat {$seat_number} is not available for flight {$flight_number}");
        }
    }
    
    /**
     * Insert passenger record
     */
    private function insertPassenger($booking_data) {
        $stmt = $this->db_connection->prepare("INSERT INTO passenger (p_id, passenger_name, email, phone, city, date_of_birth, flight_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $this->db_connection->error);
        }
        
        $city = $booking_data['city'] ?? '';
        $date_of_birth = $booking_data['date_of_birth'] ?? null;
        
        $stmt->bind_param('sssssss', 
            $booking_data['p_id'],
            $booking_data['passenger_name'],
            $booking_data['email'],
            $booking_data['phone'],
            $city,
            $date_of_birth,
            $booking_data['selected_flight']
        );
        
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Failed to insert passenger: " . $stmt->error);
        }
        
        $passenger_id = $booking_data['p_id'];
        $stmt->close();
        
        return $passenger_id;
    }
    
    /**
     * Insert ticket record
     */
    private function insertTicket($booking_data, $passenger_id, $ticket_number, $booking_reference, $price) {
        $stmt = $this->db_connection->prepare("INSERT INTO tickets (ticket_number, seat_number, passenger_name, flying_to, flying_from, departing_date, price, class, flight_number, p_id, payment_status, booking_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid', ?)");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $this->db_connection->error);
        }
        
        $stmt->bind_param('ssssssdssss',
            $ticket_number,
            $booking_data['selected_seat'],
            $booking_data['passenger_name'],
            $booking_data['flying_to'],
            $booking_data['flying_from'],
            $booking_data['departing_date'],
            $price,
            $booking_data['class'],
            $booking_data['selected_flight'],
            $passenger_id,
            $booking_reference
        );
        
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Failed to insert ticket: " . $stmt->error);
        }
        
        $ticket_id = $this->db_connection->insert_id;
        $stmt->close();
        
        return $ticket_id;
    }
    
    /**
     * Insert payment record
     */
    private function insertPayment($ticket_id, $amount, $payment_method, $transaction_id) {
        $stmt = $this->db_connection->prepare("INSERT INTO payments (ticket_id, amount, payment_method, payment_status, transaction_id, payment_gateway) VALUES (?, ?, ?, 'Success', ?, 'Demo Gateway')");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $this->db_connection->error);
        }
        
        $stmt->bind_param('idss', $ticket_id, $amount, $payment_method, $transaction_id);
        
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Failed to insert payment: " . $stmt->error);
        }
        
        $stmt->close();
    }
    
    /**
     * Update seat status
     */
    private function updateSeatStatus($flight_number, $seat_number, $is_available) {
        $stmt = $this->db_connection->prepare("UPDATE seats SET is_available = ?, is_reserved = 0, reserved_until = NULL WHERE flight_number = ? AND seat_number = ?");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $this->db_connection->error);
        }
        
        $stmt->bind_param('iss', $is_available, $flight_number, $seat_number);
        
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Failed to update seat status: " . $stmt->error);
        }
        
        $stmt->close();
    }
}

// Example usage function
function processBookingConfirmation($booking_data) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database_name = "airport_management_system";
    
    $conn = new mysqli($servername, $username, $password, $database_name);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    $handler = new BookingConfirmationHandler($conn);
    $result = $handler->processBooking($booking_data);
    
    $conn->close();
    
    return $result;
}
?>
