<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
    <link rel="stylesheet" href="css/Ticketbooking.css">
  </head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h3><i class="fas fa-check-circle text-success"></i> Booking Confirmation</h3>
    </div>
                    <div class="card-body">
                        <?php
                        $servername = "localhost";
                        $username = "root";
                        $password = "";
                        $database_name = "airport_management_system";

                        $conn = mysqli_connect($servername, $username, $password, $database_name);

                        if (!$conn) {
                            die("Connection failed: " . mysqli_connect_error());
                        }

                        if (isset($_POST['save5'])) {
                            // Sanitize and validate input
                            $p_id = mysqli_real_escape_string($conn, $_POST['p_id']);
                            $passenger_name = mysqli_real_escape_string($conn, $_POST['passenger_name']);
                            $email = mysqli_real_escape_string($conn, $_POST['email']);
                            $phone = mysqli_real_escape_string($conn, $_POST['phone']);
                            $city = mysqli_real_escape_string($conn, $_POST['city']);
                            $date_of_birth = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
                            $flying_from = mysqli_real_escape_string($conn, $_POST['flying_from']);
                            $flying_to = mysqli_real_escape_string($conn, $_POST['flying_to']);
                            $departing_date = mysqli_real_escape_string($conn, $_POST['departing_date']);
                            $selected_flight = mysqli_real_escape_string($conn, $_POST['selected_flight']);
                            $class = mysqli_real_escape_string($conn, $_POST['optradio']);
                            $selected_seat = mysqli_real_escape_string($conn, $_POST['selected_seat']);
                            $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

                            // Get flight details and pricing
                            $flight_sql = "SELECT * FROM flights WHERE flight_number = '$selected_flight'";
                            $flight_result = mysqli_query($conn, $flight_sql);

                            if (mysqli_num_rows($flight_result) > 0) {
                                $flight_row = mysqli_fetch_assoc($flight_result);
                                
                                // Determine price based on class
                                $price = 0;
                                switch ($class) {
                                    case 'Economy':
                                        $price = $flight_row['price_economy'];
                                        break;
                                    case 'Business':
                                        $price = $flight_row['price_business'];
                                        break;
                                    case 'First':
                                        $price = $flight_row['price_first'];
                                        break;
                                }

                                // Include the booking reference generator
                                require_once 'booking_reference_generator.php';
                                
                                // Generate unique identifiers using the new system
                                $reference_generator = new BookingReferenceGenerator($conn);
                                $booking_reference = $reference_generator->generateUniqueReference();
                                $ticket_number = $reference_generator->generateUniqueTicketNumber();

                                // Check if seat is available
                                $seat_check_sql = "SELECT * FROM seats WHERE flight_number = '$selected_flight' AND seat_number = '$selected_seat' AND is_available = 1";
                                $seat_check_result = mysqli_query($conn, $seat_check_sql);

                                if (mysqli_num_rows($seat_check_result) > 0) {
                                    // Start transaction
                                    mysqli_begin_transaction($conn);

                                    try {
                                        // Insert passenger
                                        $passenger_sql = "INSERT INTO passenger (p_id, passenger_name, email, phone, city, date_of_birth, flight_number) 
                                                        VALUES ('$p_id', '$passenger_name', '$email', '$phone', '$city', '$date_of_birth', '$selected_flight')";

                                        if (!mysqli_query($conn, $passenger_sql)) {
                                            throw new Exception("Error inserting passenger: " . mysqli_error($conn));
                                        }

                                        // Insert ticket
                                        $ticket_sql = "INSERT INTO tickets (ticket_number, seat_number, passenger_name, flying_to, flying_from, departing_date, price, class, flight_number, p_id, payment_status, booking_reference) 
                                                     VALUES ('$ticket_number', '$selected_seat', '$passenger_name', '$flying_to', '$flying_from', '$departing_date', '$price', '$class', '$selected_flight', '$p_id', 'Paid', '$booking_reference')";

                                        if (!mysqli_query($conn, $ticket_sql)) {
                                            throw new Exception("Error inserting ticket: " . mysqli_error($conn));
                                        }

                                        // Get ticket ID for payment record
                                        $ticket_id = mysqli_insert_id($conn);

                                        // Insert payment record
                                        $payment_sql = "INSERT INTO payments (ticket_id, amount, payment_method, payment_status, transaction_id, payment_gateway) 
                                                      VALUES ('$ticket_id', '$price', '$payment_method', 'Success', 'TXN' . rand(100000, 999999), 'Demo Gateway')";

                                        if (!mysqli_query($conn, $payment_sql)) {
                                            throw new Exception("Error inserting payment: " . mysqli_error($conn));
                                        }

                                        // Update seat as occupied
                                        $seat_update_sql = "UPDATE seats SET is_available = 0, is_reserved = 0 WHERE flight_number = '$selected_flight' AND seat_number = '$selected_seat'";
                                        if (!mysqli_query($conn, $seat_update_sql)) {
                                            throw new Exception("Error updating seat: " . mysqli_error($conn));
                                        }

                                        // Commit transaction
                                        mysqli_commit($conn);

                                        // Display success message
                                        echo '<div class="alert alert-success" role="alert">
                                                <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Booking Successful!</h4>
                                                <p>Your ticket has been booked successfully. Please find your booking details below:</p>
                                              </div>';

                                        echo '<div class="row">
                                                <div class="col-md-6">
                                                    <h5><i class="fas fa-user"></i> Passenger Details</h5>
                                                    <table class="table table-borderless">
                                                        <tr><td><strong>Name:</strong></td><td>' . $passenger_name . '</td></tr>
                                                        <tr><td><strong>Email:</strong></td><td>' . $email . '</td></tr>
                                                        <tr><td><strong>Phone:</strong></td><td>' . $phone . '</td></tr>
                                                        <tr><td><strong>Passport/ID:</strong></td><td>' . $p_id . '</td></tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h5><i class="fas fa-plane"></i> Flight Details</h5>
                                                    <table class="table table-borderless">
                                                        <tr><td><strong>Flight:</strong></td><td>' . $selected_flight . '</td></tr>
                                                        <tr><td><strong>Route:</strong></td><td>' . $flying_from . ' → ' . $flying_to . '</td></tr>
                                                        <tr><td><strong>Date:</strong></td><td>' . $departing_date . '</td></tr>
                                                        <tr><td><strong>Class:</strong></td><td>' . $class . '</td></tr>
                                                        <tr><td><strong>Seat:</strong></td><td>' . $selected_seat . '</td></tr>
                                                    </table>
                                                </div>
                                              </div>';

                                        echo '<div class="row mt-4">
                                                <div class="col-md-12">
                                                    <div class="alert alert-success border-left" style="border-left: 4px solid #28a745 !important; background: #d4edda;">
                                                        <h5><i class="fas fa-key"></i> Your Booking Reference</h5>
                                                        <p class="mb-2">Save this reference to check your booking status or make changes</p>
                                                        <h3 class="text-center mb-3" style="font-family: monospace; letter-spacing: 2px; color: #28a745;">
                                                            ' . $booking_reference . '
                                                        </h3>
                                                        <button class="btn btn-outline-success btn-sm" onclick="copyToClipboard(\'' . $booking_reference . '\')">
                                                            <i class="fas fa-copy"></i> Copy Reference
                                                        </button>
                                                    </div>
                                                </div>
                                              </div>';
                                              
                                        echo '<div class="row mt-3">
                                                <div class="col-md-12">
                                                    <h5><i class="fas fa-ticket-alt"></i> Booking Information</h5>
                                                    <table class="table table-borderless">
                                                        <tr><td><strong>Ticket Number:</strong></td><td><code>' . $ticket_number . '</code></td></tr>
                                                        <tr><td><strong>Booking Reference:</strong></td><td><code>' . $booking_reference . '</code></td></tr>
                                                        <tr><td><strong>Total Amount:</strong></td><td><span class="text-success font-weight-bold">₹' . number_format($price, 2) . '</span></td></tr>
                                                        <tr><td><strong>Payment Method:</strong></td><td>' . $payment_method . '</td></tr>
                                                        <tr><td><strong>Payment Status:</strong></td><td><span class="badge badge-success">Paid</span></td></tr>
                                                    </table>
                                                </div>
                                              </div>';

                                        echo '<div class="alert alert-info mt-4" role="alert">
                                                <h6><i class="fas fa-info-circle"></i> Important Information</h6>
                                                <ul class="mb-0">
                                                    <li>Please arrive at the airport at least 2 hours before departure</li>
                                                    <li>Carry a valid ID proof and this booking confirmation</li>
                                                    <li>Check-in online 24 hours before departure</li>
                                                    <li>For any queries, contact our customer service</li>
                                                </ul>
                                              </div>';

                                    } catch (Exception $e) {
                                        // Rollback transaction
                                        mysqli_rollback($conn);
                                        echo '<div class="alert alert-danger" role="alert">
                                                <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Booking Failed</h4>
                                                <p>Sorry, there was an error processing your booking. Please try again.</p>
                                                <p><strong>Error:</strong> ' . $e->getMessage() . '</p>
                                              </div>';
           }

     } else {
                                    echo '<div class="alert alert-warning" role="alert">
                                            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Seat Not Available</h4>
                                            <p>The selected seat is no longer available. Please go back and select another seat.</p>
                                          </div>';
                                }

                            } else {
                                echo '<div class="alert alert-danger" role="alert">
                                        <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Flight Not Found</h4>
                                        <p>The selected flight is not available. Please go back and select a valid flight.</p>
                                      </div>';
                            }

                        } else {
                            echo '<div class="alert alert-info" role="alert">
                                    <h4 class="alert-heading"><i class="fas fa-info-circle"></i> No Booking Data</h4>
                                    <p>No booking data received. Please go back to the booking page and try again.</p>
                                  </div>';
                        }

                        mysqli_close($conn);
                        ?>

                        <div class="text-center mt-4">
                            <a href="Ticketbooking.html" class="btn btn-primary mr-3">
                                <i class="fas fa-plus"></i> Book Another Ticket
                            </a>
                            <a href="Frontpage.html" class="btn btn-secondary">
                                <i class="fas fa-home"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Copy to clipboard function
        function copyToClipboard(text) {
            // Create a temporary input element
            const tempInput = document.createElement('input');
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            // Show feedback
            alert('Booking Reference copied to clipboard: ' + text);
        }
    </script>
</body>
</html>