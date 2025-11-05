<?php
/**
 * Payment Processing and Booking Confirmation
 * Handles payment completion and generates unique booking reference
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
        $payment_data = json_decode($input, true);
        
        if (!$payment_data) {
            throw new Exception('Invalid JSON input');
        }
        
        // Validate required fields
        $required_fields = ['passenger_name', 'flying_from', 'flying_to', 'departing_date', 'selected_flight', 'class', 'selected_seat', 'payment_method'];
        foreach ($required_fields as $field) {
            if (empty($payment_data[$field])) {
                throw new Exception("Required field '{$field}' is missing");
            }
        }
        
        // Clean and validate flight number
        $original_flight = trim($payment_data['selected_flight']);
        $flight_number = $original_flight;
        
        // Handle cases where flight might be passed as "Company FlightNumber" or "Company FlightNumber / Company FlightNumber"
        // Also handle malformed cases like "03 / IndiGo 6E202"
        if (strpos($flight_number, '/') !== false) {
            // Split by / and try each part to find a valid flight number
            $parts = array_map('trim', explode('/', $flight_number));
            $found_flight = false;
            
            // Try each part to extract a valid flight number
            foreach ($parts as $part) {
                // Remove airline names
                $airline_pattern = '/\b(IndiGo|Air India|SpiceJet|Vistara|GoAir|AirAsia|Jet Airways)\s*/i';
                $cleaned = preg_replace($airline_pattern, '', $part);
                $cleaned = trim($cleaned);
                
                // Pattern to match flight numbers: starts with digits/letters, contains letters and digits
                if (preg_match('/[A-Z0-9]{2,5}[A-Z]?\d{2,5}/i', $cleaned, $matches)) {
                    $flight_number = strtoupper(trim($matches[0]));
                    $found_flight = true;
                    break; // Found a valid flight number, use it
                }
            }
            
            // If no valid flight number found in parts, use original
            if (!$found_flight) {
                $flight_number = $original_flight;
            }
        }
        
        // Extract flight number from company name (e.g., "IndiGo 6E901" -> "6E901", "Air India AI101" -> "AI101")
        // First, try to remove airline names
        $airline_pattern = '/\b(IndiGo|Air India|SpiceJet|Vistara|GoAir|AirAsia|Jet Airways)\s*/i';
        $flight_number = preg_replace($airline_pattern, '', $flight_number);
        $flight_number = trim($flight_number);
        
        // Pattern to match flight numbers: starts with digits/letters, contains letters and digits
        // Matches: 6E901, AI101, SG202, UK303, etc.
        if (preg_match('/[A-Z0-9]{2,5}[A-Z]?\d{2,5}/i', $flight_number, $matches)) {
            $flight_number = strtoupper(trim($matches[0]));
        } else {
            // If still no match, try simpler pattern - just extract alphanumeric after airline name
            $flight_number = preg_replace('/[^A-Z0-9]/', '', strtoupper($flight_number));
        }
        
        // Validate that we have a reasonable flight number (at least 3 characters)
        if (strlen($flight_number) < 3) {
            throw new Exception("Invalid flight number format: '{$original_flight}'. Could not extract valid flight number. Please ensure the flight is properly selected.");
        }
        
        $payment_data['selected_flight'] = $flight_number;
        
        // Debug: Log the cleaned flight number
        error_log("Original flight: " . $original_flight . " -> Cleaned: " . $flight_number);
        
        // Set default values for optional fields
        $payment_data['email'] = $payment_data['email'] ?? 'guest@example.com';
        $payment_data['phone'] = $payment_data['phone'] ?? '0000000000';
        // City field removed - always set to empty
        $payment_data['city'] = '';
        $payment_data['date_of_birth'] = $payment_data['date_of_birth'] ?? null;
        
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database_name = "airport_management_system";
        
        $conn = new mysqli($servername, $username, $password, $database_name);
        
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Generate unique identifiers
            $reference_generator = new BookingReferenceGenerator($conn);
            $booking_reference = $reference_generator->generateUniqueReference();
            $ticket_number = $reference_generator->generateUniqueTicketNumber();
            $transaction_id = $reference_generator->generateTransactionId();
            
            // Check if passenger already exists (by email), otherwise generate unique p_id
            if (!empty($payment_data['p_id'])) {
                // Use provided p_id if valid
                $existing_passenger = $conn->prepare("SELECT p_id FROM passenger WHERE p_id = ?");
                $existing_passenger->bind_param('s', $payment_data['p_id']);
                $existing_passenger->execute();
                $passenger_result = $existing_passenger->get_result();
                
                if ($passenger_result->num_rows > 0) {
                    // Passenger exists, reuse p_id
                    $payment_data['p_id'] = $passenger_result->fetch_assoc()['p_id'];
                    $existing_passenger->close();
                } else {
                    // Provided p_id doesn't exist, generate new one
                    $payment_data['p_id'] = $reference_generator->generateUniquePassengerId();
                    $existing_passenger->close();
                }
            } else {
                // No p_id provided, check by email first
                if (!empty($payment_data['email']) && $payment_data['email'] !== 'guest@example.com') {
                    $existing_passenger = $conn->prepare("SELECT p_id FROM passenger WHERE email = ? LIMIT 1");
                    $existing_passenger->bind_param('s', $payment_data['email']);
                    $existing_passenger->execute();
                    $passenger_result = $existing_passenger->get_result();
                    
                    if ($passenger_result->num_rows > 0) {
                        // Passenger exists with this email, reuse p_id
                        $payment_data['p_id'] = $passenger_result->fetch_assoc()['p_id'];
                        $existing_passenger->close();
                    } else {
                        // New passenger, generate unique p_id
                        $payment_data['p_id'] = $reference_generator->generateUniquePassengerId();
                        $existing_passenger->close();
                    }
                } else {
                    // Generate unique p_id for guest users
                    $payment_data['p_id'] = $reference_generator->generateUniquePassengerId();
                }
            }
            
            // Get flight details and pricing
            $flight_stmt = $conn->prepare("SELECT * FROM flights WHERE flight_number = ?");
            $flight_stmt->bind_param('s', $payment_data['selected_flight']);
            $flight_stmt->execute();
            $flight_result = $flight_stmt->get_result();
            
            if ($flight_result->num_rows === 0) {
                // Try case-insensitive search
                $flight_lookup = "SELECT * FROM flights WHERE UPPER(flight_number) = UPPER(?)";
                $flight_stmt2 = $conn->prepare($flight_lookup);
                $flight_stmt2->bind_param('s', $payment_data['selected_flight']);
                $flight_stmt2->execute();
                $flight_result2 = $flight_stmt2->get_result();
                
                if ($flight_result2->num_rows > 0) {
                    $flight_details = $flight_result2->fetch_assoc();
                    $flight_stmt2->close();
                } else {
                    // Fallback: find any flight for the requested route
                    $fallback_stmt = $conn->prepare("SELECT * FROM flights WHERE source = ? AND destination = ? LIMIT 1");
                    $fallback_stmt->bind_param('ss', $payment_data['flying_from'], $payment_data['flying_to']);
                    $fallback_stmt->execute();
                    $fallback_result = $fallback_stmt->get_result();
                    if ($fallback_result->num_rows > 0) {
                        $flight_details = $fallback_result->fetch_assoc();
                        // Overwrite to the found flight number for consistency downstream
                        $payment_data['selected_flight'] = $flight_details['flight_number'];
                        $fallback_stmt->close();
                    } else {
                    // List available flights for debugging
                    $debug_query = "SELECT flight_number, flight_company FROM flights LIMIT 10";
                    $debug_result = $conn->query($debug_query);
                    $available_flights = [];
                    while ($row = $debug_result->fetch_assoc()) {
                        $available_flights[] = $row['flight_company'] . ' ' . $row['flight_number'];
                    }
                    $flight_stmt->close();
                        throw new Exception("Flight not found: " . $payment_data['selected_flight'] . ". Available flights: " . implode(', ', array_slice($available_flights, 0, 5)));
                    }
                }
            } else {
                $flight_details = $flight_result->fetch_assoc();
                $flight_stmt->close();
            }
            
            // Calculate price based on class
            $price = 0;
            switch ($payment_data['class']) {
                case 'Economy':
                    $price = $flight_details['price_economy'];
                    break;
                case 'Business':
                    $price = $flight_details['price_business'];
                    break;
                case 'First':
                    $price = $flight_details['price_first'];
                    break;
                default:
                    throw new Exception("Invalid travel class: " . $payment_data['class']);
            }
            
            // Lock and check seat availability (using SELECT FOR UPDATE to prevent race conditions)
            $seat_lock_stmt = $conn->prepare("SELECT is_available, seat_number FROM seats WHERE flight_number = ? AND seat_number = ? FOR UPDATE");
            $seat_lock_stmt->bind_param('ss', $payment_data['selected_flight'], $payment_data['selected_seat']);
            $seat_lock_stmt->execute();
            $seat_lock_result = $seat_lock_stmt->get_result();
            $seat_data = $seat_lock_result->fetch_assoc();
            $seat_lock_stmt->close();
            
            if ($seat_data === null) {
                // Seat doesn't exist, create it and mark as available
                $create_seat_stmt = $conn->prepare("INSERT INTO seats (flight_number, seat_number, is_available, is_reserved) VALUES (?, ?, 1, 0)");
                $create_seat_stmt->bind_param('ss', $payment_data['selected_flight'], $payment_data['selected_seat']);
                if (!$create_seat_stmt->execute()) {
                    throw new Exception("Failed to create seat: " . $create_seat_stmt->error);
                }
                $create_seat_stmt->close();
            } else {
                // Validate seat availability - check again with lock to prevent double booking
                if ($seat_data['is_available'] == 0) {
                    throw new Exception("Seat {$payment_data['selected_seat']} is already booked for flight {$payment_data['selected_flight']}. Please select another seat.");
                }
            }
            
            // Insert or update passenger record (handle duplicate p_id gracefully)
            // Try insert first, if duplicate exists, update instead
            $passenger_stmt = $conn->prepare("INSERT INTO passenger (p_id, passenger_name, email, phone, city, date_of_birth, flight_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $passenger_stmt->bind_param('sssssss', 
                $payment_data['p_id'],
                $payment_data['passenger_name'],
                $payment_data['email'],
                $payment_data['phone'],
                $payment_data['city'],
                $payment_data['date_of_birth'],
                $payment_data['selected_flight']
            );
            
            if (!$passenger_stmt->execute()) {
                $error_code = $passenger_stmt->errno;
                $error_msg = $passenger_stmt->error;
                $passenger_stmt->close();
                
                // Check if error is due to duplicate key (error code 1062)
                if ($error_code == 1062 || strpos($error_msg, 'Duplicate entry') !== false) {
                    // Passenger with this p_id already exists, update instead
                    $update_stmt = $conn->prepare("UPDATE passenger SET passenger_name = ?, email = ?, phone = ?, city = ?, date_of_birth = ?, flight_number = ? WHERE p_id = ?");
                    $update_stmt->bind_param('sssssss',
                        $payment_data['passenger_name'],
                        $payment_data['email'],
                        $payment_data['phone'],
                        $payment_data['city'],
                        $payment_data['date_of_birth'],
                        $payment_data['selected_flight'],
                        $payment_data['p_id']
                    );
                    
                    if (!$update_stmt->execute()) {
                        // Even update failed, generate new unique p_id and insert
                        $update_stmt->close();
                        $payment_data['p_id'] = $reference_generator->generateUniquePassengerId();
                        
                        $insert_retry = $conn->prepare("INSERT INTO passenger (p_id, passenger_name, email, phone, city, date_of_birth, flight_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $insert_retry->bind_param('sssssss', 
                            $payment_data['p_id'],
                            $payment_data['passenger_name'],
                            $payment_data['email'],
                            $payment_data['phone'],
                            $payment_data['city'],
                            $payment_data['date_of_birth'],
                            $payment_data['selected_flight']
                        );
                        
                        if (!$insert_retry->execute()) {
                            throw new Exception("Failed to insert passenger after all retries: " . $insert_retry->error);
                        }
                        $insert_retry->close();
                    } else {
                        $update_stmt->close();
                    }
                } else {
                    // Some other error occurred
                    throw new Exception("Failed to insert passenger: " . $error_msg);
                }
            } else {
                $passenger_stmt->close();
            }
            
            // Insert ticket record
            $ticket_stmt = $conn->prepare("INSERT INTO tickets (ticket_number, seat_number, passenger_name, flying_to, flying_from, departing_date, price, class, flight_number, p_id, payment_status, booking_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid', ?)");
            $ticket_stmt->bind_param('ssssssdssss',
                $ticket_number,
                $payment_data['selected_seat'],
                $payment_data['passenger_name'],
                $payment_data['flying_to'],
                $payment_data['flying_from'],
                $payment_data['departing_date'],
                $price,
                $payment_data['class'],
                $payment_data['selected_flight'],
                $payment_data['p_id'],
                $booking_reference
            );
            
            if (!$ticket_stmt->execute()) {
                throw new Exception("Failed to insert ticket: " . $ticket_stmt->error);
            }
            
            $ticket_id = $conn->insert_id;
            $ticket_stmt->close();
            
            // Insert payment record
            $payment_stmt = $conn->prepare("INSERT INTO payments (ticket_id, amount, payment_method, payment_status, transaction_id, payment_gateway) VALUES (?, ?, ?, 'Success', ?, 'Demo Gateway')");
            $payment_stmt->bind_param('idss', $ticket_id, $price, $payment_data['payment_method'], $transaction_id);
            
            if (!$payment_stmt->execute()) {
                throw new Exception("Failed to insert payment: " . $payment_stmt->error);
            }
            $payment_stmt->close();
            
            // Atomically update seat as occupied (only if it's still available)
            // This prevents double booking even if two requests come at the same time
            $seat_update_stmt = $conn->prepare("UPDATE seats SET is_available = 0, is_reserved = 0, reserved_until = NULL WHERE flight_number = ? AND seat_number = ? AND is_available = 1");
            $seat_update_stmt->bind_param('ss', $payment_data['selected_flight'], $payment_data['selected_seat']);
            
            if (!$seat_update_stmt->execute()) {
                throw new Exception("Failed to update seat status: " . $seat_update_stmt->error);
            }
            
            // Check if the update actually affected any rows (seat was still available)
            if ($seat_update_stmt->affected_rows === 0) {
                // Seat was taken between our check and update - this shouldn't happen with FOR UPDATE, but just in case
                throw new Exception("Seat {$payment_data['selected_seat']} was booked by another user. Please select a different seat.");
            }
            $seat_update_stmt->close();
            
            // Handle round trip booking (create return ticket)
            $return_ticket_id = null;
            $return_ticket_number = null;
            $return_price = 0;
            
            if (!empty($payment_data['is_round_trip']) && $payment_data['is_round_trip'] === true) {
                // Process return flight booking
                if (!empty($payment_data['return_flight']) && !empty($payment_data['return_seat']) && !empty($payment_data['return_class'])) {
                    // Extract return flight number
                    $return_flight = trim($payment_data['return_flight']);
                    $return_flight_orig = $return_flight;
                    
                    // Clean return flight number
                    $airline_pattern = '/\b(IndiGo|Air India|SpiceJet|Vistara|GoAir|AirAsia|Jet Airways)\s*/i';
                    $return_flight = preg_replace($airline_pattern, '', $return_flight);
                    $return_flight = trim($return_flight);
                    
                    if (preg_match('/[A-Z0-9]{2,5}[A-Z]?\d{2,5}/i', $return_flight, $matches)) {
                        $return_flight = strtoupper(trim($matches[0]));
                    }
                    
                    // Get return flight details
                    $return_flight_stmt = $conn->prepare("SELECT * FROM flights WHERE flight_number = ?");
                    $return_flight_stmt->bind_param('s', $return_flight);
                    $return_flight_stmt->execute();
                    $return_flight_result = $return_flight_stmt->get_result();
                    
                    if ($return_flight_result->num_rows > 0) {
                        $return_flight_details = $return_flight_result->fetch_assoc();
                        
                        // Calculate return price
                        switch ($payment_data['return_class']) {
                            case 'Economy':
                                $return_price = $return_flight_details['price_economy'];
                                break;
                            case 'Business':
                                $return_price = $return_flight_details['price_business'];
                                break;
                            case 'First':
                                $return_price = $return_flight_details['price_first'];
                                break;
                        }
                        
                        // Check and lock return seat
                        $return_seat_lock_stmt = $conn->prepare("SELECT is_available FROM seats WHERE flight_number = ? AND seat_number = ? FOR UPDATE");
                        $return_seat_lock_stmt->bind_param('ss', $return_flight, $payment_data['return_seat']);
                        $return_seat_lock_stmt->execute();
                        $return_seat_result = $return_seat_lock_stmt->get_result();
                        $return_seat_data = $return_seat_result->fetch_assoc();
                        $return_seat_lock_stmt->close();
                        
                        if ($return_seat_data === null) {
                            // Create return seat
                            $create_return_seat_stmt = $conn->prepare("INSERT INTO seats (flight_number, seat_number, is_available, is_reserved) VALUES (?, ?, 1, 0)");
                            $create_return_seat_stmt->bind_param('ss', $return_flight, $payment_data['return_seat']);
                            if (!$create_return_seat_stmt->execute()) {
                                throw new Exception("Failed to create return seat");
                            }
                            $create_return_seat_stmt->close();
                        } else if ($return_seat_data['is_available'] == 0) {
                            throw new Exception("Return seat {$payment_data['return_seat']} is already booked");
                        }
                        
                        // Generate return ticket number
                        $return_ticket_number = $reference_generator->generateUniqueTicketNumber();
                        
                        // Insert return ticket
                        $return_ticket_stmt = $conn->prepare("INSERT INTO tickets (ticket_number, seat_number, passenger_name, flying_to, flying_from, departing_date, price, class, flight_number, p_id, payment_status, booking_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid', ?)");
                        $return_ticket_stmt->bind_param('ssssssdssss',
                            $return_ticket_number,
                            $payment_data['return_seat'],
                            $payment_data['passenger_name'],
                            $payment_data['return_to'],
                            $payment_data['return_from'],
                            $payment_data['return_date'],
                            $return_price,
                            $payment_data['return_class'],
                            $return_flight,
                            $payment_data['p_id'],
                            $booking_reference
                        );
                        
                        if (!$return_ticket_stmt->execute()) {
                            throw new Exception("Failed to insert return ticket: " . $return_ticket_stmt->error);
                        }
                        
                        $return_ticket_id = $conn->insert_id;
                        $return_ticket_stmt->close();
                        
                        // Insert return payment record
                        $return_payment_stmt = $conn->prepare("INSERT INTO payments (ticket_id, amount, payment_method, payment_status, transaction_id, payment_gateway) VALUES (?, ?, ?, 'Success', ?, 'Demo Gateway')");
                        $return_payment_stmt->bind_param('idss', $return_ticket_id, $return_price, $payment_data['payment_method'], $transaction_id);
                        
                        if (!$return_payment_stmt->execute()) {
                            throw new Exception("Failed to insert return payment");
                        }
                        $return_payment_stmt->close();
                        
                        // Update return seat as occupied
                        $return_seat_update_stmt = $conn->prepare("UPDATE seats SET is_available = 0, is_reserved = 0, reserved_until = NULL WHERE flight_number = ? AND seat_number = ? AND is_available = 1");
                        $return_seat_update_stmt->bind_param('ss', $return_flight, $payment_data['return_seat']);
                        
                        if (!$return_seat_update_stmt->execute()) {
                            throw new Exception("Failed to update return seat status");
                        }
                        
                        if ($return_seat_update_stmt->affected_rows === 0) {
                            throw new Exception("Return seat {$payment_data['return_seat']} was booked by another user");
                        }
                        $return_seat_update_stmt->close();
                    } else {
                        // Fallback: find any return flight for the requested route
                        $rf_stmt = $conn->prepare("SELECT * FROM flights WHERE source = ? AND destination = ? LIMIT 1");
                        $rf_stmt->bind_param('ss', $payment_data['return_from'], $payment_data['return_to']);
                        $rf_stmt->execute();
                        $rf_res = $rf_stmt->get_result();
                        if ($rf_res->num_rows > 0) {
                            $return_flight_details = $rf_res->fetch_assoc();
                            $return_flight = $return_flight_details['flight_number'];
                            // Calculate return price
                            switch ($payment_data['return_class']) {
                                case 'Economy':
                                    $return_price = $return_flight_details['price_economy'];
                                    break;
                                case 'Business':
                                    $return_price = $return_flight_details['price_business'];
                                    break;
                                case 'First':
                                    $return_price = $return_flight_details['price_first'];
                                    break;
                            }
                            // proceed with the same flow below using $return_flight and $return_flight_details
                            // Check and lock return seat
                            $return_seat_lock_stmt = $conn->prepare("SELECT is_available FROM seats WHERE flight_number = ? AND seat_number = ? FOR UPDATE");
                            $return_seat_lock_stmt->bind_param('ss', $return_flight, $payment_data['return_seat']);
                            $return_seat_lock_stmt->execute();
                            $return_seat_result = $return_seat_lock_stmt->get_result();
                            $return_seat_data = $return_seat_result->fetch_assoc();
                            $return_seat_lock_stmt->close();
                            if ($return_seat_data === null) {
                                $create_return_seat_stmt = $conn->prepare("INSERT INTO seats (flight_number, seat_number, is_available, is_reserved) VALUES (?, ?, 1, 0)");
                                $create_return_seat_stmt->bind_param('ss', $return_flight, $payment_data['return_seat']);
                                if (!$create_return_seat_stmt->execute()) {
                                    throw new Exception("Failed to create return seat");
                                }
                                $create_return_seat_stmt->close();
                            } else if ($return_seat_data['is_available'] == 0) {
                                throw new Exception("Return seat {$payment_data['return_seat']} is already booked");
                            }

                            // Generate return ticket number
                            $return_ticket_number = $reference_generator->generateUniqueTicketNumber();
                            // Insert return ticket
                            $return_ticket_stmt = $conn->prepare("INSERT INTO tickets (ticket_number, seat_number, passenger_name, flying_to, flying_from, departing_date, price, class, flight_number, p_id, payment_status, booking_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid', ?)");
                            $return_ticket_stmt->bind_param('ssssssdssss',
                                $return_ticket_number,
                                $payment_data['return_seat'],
                                $payment_data['passenger_name'],
                                $payment_data['return_to'],
                                $payment_data['return_from'],
                                $payment_data['return_date'],
                                $return_price,
                                $payment_data['return_class'],
                                $return_flight,
                                $payment_data['p_id'],
                                $booking_reference
                            );
                            if (!$return_ticket_stmt->execute()) {
                                throw new Exception("Failed to insert return ticket: " . $return_ticket_stmt->error);
                            }
                            $return_ticket_id = $conn->insert_id;
                            $return_ticket_stmt->close();
                            // Insert payment
                            $return_payment_stmt = $conn->prepare("INSERT INTO payments (ticket_id, amount, payment_method, payment_status, transaction_id, payment_gateway) VALUES (?, ?, ?, 'Success', ?, 'Demo Gateway')");
                            $return_payment_stmt->bind_param('idss', $return_ticket_id, $return_price, $payment_data['payment_method'], $transaction_id);
                            if (!$return_payment_stmt->execute()) {
                                throw new Exception("Failed to insert return payment");
                            }
                            $return_payment_stmt->close();
                            // Update seat
                            $return_seat_update_stmt = $conn->prepare("UPDATE seats SET is_available = 0, is_reserved = 0, reserved_until = NULL WHERE flight_number = ? AND seat_number = ? AND is_available = 1");
                            $return_seat_update_stmt->bind_param('ss', $return_flight, $payment_data['return_seat']);
                            if (!$return_seat_update_stmt->execute()) {
                                throw new Exception("Failed to update return seat status");
                            }
                            if ($return_seat_update_stmt->affected_rows === 0) {
                                throw new Exception("Return seat {$payment_data['return_seat']} was booked by another user");
                            }
                            $return_seat_update_stmt->close();
                        } else {
                            throw new Exception("Return flight not found: " . $return_flight);
                        }
                    }
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            // Return success response
            $response_data = [
                'success' => true,
                'message' => 'Payment processed and booking confirmed successfully',
                'booking_reference' => $booking_reference,
                'ticket_number' => $ticket_number,
                'transaction_id' => $transaction_id,
                'passenger_id' => $payment_data['p_id'],
                'ticket_id' => $ticket_id,
                'price' => $price,
                'flight_details' => [
                    'flight_number' => $flight_details['flight_number'],
                    'flight_company' => $flight_details['flight_company'],
                    'departing_time' => $flight_details['departing_time'],
                    'arrival_time' => $flight_details['arrival_time']
                ]
            ];
            
            // Add return flight details if round trip
            if ($return_ticket_id) {
                $response_data['return_ticket_number'] = $return_ticket_number;
                $response_data['return_ticket_id'] = $return_ticket_id;
                $response_data['return_price'] = $return_price;
            }
            
            echo json_encode($response_data);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }
        
        $conn->close();
        
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
