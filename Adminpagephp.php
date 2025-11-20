<?php
// Check if this is an AJAX request first
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Set proper headers for AJAX requests
if ($is_ajax) {
  header('Content-Type: text/plain');
}

// Only output HTML if it's NOT an AJAX request
if (!$is_ajax) {
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>admin page</title>
    <link rel="stylesheet" href="css/Adminpage.css">
  </head>
  <body class="separetephpfile">
    <h5>Click on the below button to go back to admin page</h5>
    <div class="new_button">
    <a class="button" href="Adminpage.php" class="">Admin Page</a>
  </div>
    <h5>Click on the below button to go back to main page</h5>
    <div class="new_button">
    <a class="button" href="Frontpage.html" class="">Home page</a>
  </div>
  </body>
</html>
<?php
}

$servername="localhost";
$username="root";
$password="";
$database_name="airport_management_system";

$conn=mysqli_connect($servername,$username,$password,$database_name);

if(!$conn){
  if ($is_ajax) {
    echo 'ERROR: Database connection failed';
    exit;
  }
  die("connection failed :" . mysqli_connect_error());
}
if(isset($_POST['save'])){
  $flight_number=$_POST['flight_number'];
  $flight_company=$_POST['flight_company'];
  $departing_time=$_POST['departing_time'];
  $arrival_time=$_POST['arrival_time'];
  $no_of_seats=$_POST['no_of_seats'];
  $source=$_POST['source'];
  $destination=$_POST['destination'];
  $price_economy=$_POST['price_economy'];
  $price_business=$_POST['price_business'];
  $price_first=$_POST['price_first'];

  $sql_query="INSERT INTO flights(flight_number,flight_company,departing_time,arrival_time,no_of_seats,source,destination,price_economy,price_business,price_first)
  VALUES ('$flight_number','$flight_company','$departing_time','$arrival_time','$no_of_seats','$source','$destination','$price_economy','$price_business','$price_first')";
  if(mysqli_query($conn,$sql_query)){
        // Create seats for this flight
        $seats_per_row = 6;
        $rows = ceil($no_of_seats / $seats_per_row);
        
        for ($row = 1; $row <= $rows; $row++) {
            for ($col = 1; $col <= $seats_per_row; $col++) {
                if (($row - 1) * $seats_per_row + $col > $no_of_seats) break;
                
                $seat_letter = chr(64 + $col); // A, B, C, D, E, F
                $seat_number = $row . $seat_letter;
                
                // Set seat class based on row (First: 1-3, Business: 4-8, Economy: rest)
                if ($row <= 3) {
                    $seat_class = 'First';
                } elseif ($row <= 8) {
                    $seat_class = 'Business';
                } else {
                    $seat_class = 'Economy';
                }
                
                $seat_sql = "INSERT IGNORE INTO seats (flight_number, seat_number, seat_class, is_available, is_reserved) 
                            VALUES ('$flight_number', '$seat_number', '$seat_class', 1, 0)";
                mysqli_query($conn, $seat_sql);
            }
        }
        
        echo '<script>alert("Flight and seats added successfully! Please go back to admin page.")</script>';
    }
   else{
       echo "error:" .$sql_query ."" . mysqli_error($conn);
   }

   mysqli_close($conn);
}

if(isset($_POST['save2'])){
  // Get and clean the flight number
  $flight_number = isset($_POST['flight_number']) ? trim($_POST['flight_number']) : '';
  $flight_number = mysqli_real_escape_string($conn, $flight_number);
  
  // Debug: Log the received flight number (remove in production)
  if ($is_ajax) {
    error_log("Delete request - Flight number received: " . $flight_number);
  }
  
  if (empty($flight_number)) {
    if ($is_ajax) {
      echo 'ERROR: Flight number is required';
    } else {
      echo '<script>alert("Error: Flight number is required");</script>';
    }
    mysqli_close($conn);
    if ($is_ajax) exit;
  } else {
    $sql = "SELECT * FROM flights WHERE flight_number='$flight_number'";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
      if ($is_ajax) {
        echo 'ERROR: Database query failed: ' . mysqli_error($conn);
      } else {
        echo '<script>alert("Error: Database query failed");</script>';
      }
      mysqli_close($conn);
      if ($is_ajax) exit;
    } else if (mysqli_num_rows($result)>0) {
      // Delete in correct order to respect foreign key constraints
      // Order: Delete child records first, then parent records
      // NOTE: We do NOT delete passengers - they may have tickets for other flights!
      
      // 1. Delete payments (references tickets)
      $payments_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'payments'");
      if ($payments_table_exists && mysqli_num_rows($payments_table_exists) > 0) {
        // Delete payments for tickets of this flight
        mysqli_query($conn, "DELETE p FROM payments p INNER JOIN tickets t ON p.ticket_id = t.ticket_id WHERE t.flight_number='$flight_number'");
      }
      
      // 2. Delete tickets (references passenger and flights) - only for this flight
      $tickets_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'tickets'");
      if ($tickets_table_exists && mysqli_num_rows($tickets_table_exists) > 0) {
        mysqli_query($conn, "DELETE FROM tickets WHERE flight_number='$flight_number'");
      }
      
      // 3. Delete reservations (references passenger, flights, and seats) - only for this flight
      $reservations_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'reservations'");
      if ($reservations_table_exists && mysqli_num_rows($reservations_table_exists) > 0) {
        mysqli_query($conn, "DELETE FROM reservations WHERE flight_number='$flight_number'");
      }
      
      // 4. Delete flight_blocks (references flights and employee) - only for this flight
      $flight_blocks_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'flight_blocks'");
      if ($flight_blocks_table_exists && mysqli_num_rows($flight_blocks_table_exists) > 0) {
        mysqli_query($conn, "DELETE FROM flight_blocks WHERE flight_number='$flight_number'");
      }
      
      // 5. Handle passenger records - we DON'T delete passengers because they may have other tickets
      // The foreign key constraint will be handled by the database when we delete the flight
      // If passenger.flight_number has a foreign key constraint, we need to update it to NULL first
      // But if it doesn't allow NULL, the database will handle the cascade or we'll get an error
      $passenger_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'passenger'");
      if ($passenger_table_exists && mysqli_num_rows($passenger_table_exists) > 0) {
        // Try to update passenger flight_number to NULL (if column allows it)
        // This will fail silently if NOT NULL constraint exists, which is fine
        // The database foreign key constraint will prevent flight deletion if needed
        @mysqli_query($conn, "UPDATE passenger SET flight_number = NULL WHERE flight_number='$flight_number'");
      }
      
      // 6. Update employee records to remove flight_number reference (set to NULL)
      $employee_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'employee'");
      if ($employee_table_exists && mysqli_num_rows($employee_table_exists) > 0) {
        mysqli_query($conn, "UPDATE employee SET flight_number = NULL WHERE flight_number='$flight_number'");
      }
      
      // 7. Delete associated seats (if table exists)
      $seats_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'seats'");
      if ($seats_table_exists && mysqli_num_rows($seats_table_exists) > 0) {
        mysqli_query($conn, "DELETE FROM seats WHERE flight_number='$flight_number'");
      }
      
      // 8. Delete associated bookings (if bookings table exists)
      $bookings_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'bookings'");
      if ($bookings_table_exists && mysqli_num_rows($bookings_table_exists) > 0) {
        mysqli_query($conn, "DELETE FROM bookings WHERE flight_number='$flight_number'");
      }
      
      // 9. Delete the flight (must be last due to foreign key constraints)
      $sql_query= "DELETE FROM flights WHERE flight_number='$flight_number'";
      if ($conn->query($sql_query) === TRUE) {
        if ($is_ajax) {
          echo 'SUCCESS';
        } else {
          echo '<script>';
          echo 'alert("Flight has been deleted successfully!");';
          echo 'window.location.href="Adminpage.php#Flights";';
          echo '</script>';
        }
      } else {
        if ($is_ajax) {
          echo 'ERROR: ' . mysqli_error($conn);
        } else {
          $error = addslashes(mysqli_error($conn));
          echo '<script>alert("Error deleting flight: ' . $error . '");</script>';
        }
      }
    } else {
        // Flight not found
        if ($is_ajax) {
          echo 'ERROR: Flight not found';
        } else {
          echo '<script>';
          echo 'alert("Sorry, the flight number does not exist in database.");';
          echo 'window.location.href="Adminpage.php#Flights";';
          echo '</script>';
        }
    }
  }

  mysqli_close($conn);
  if ($is_ajax) {
    exit;
  }
}






if(isset($_POST['save3'])){
  $emp_id = mysqli_real_escape_string($conn, $_POST['emp_id']);
  
  $sql = "SELECT * FROM employee WHERE emp_id='$emp_id'";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) > 0) {
      $sql_query= "DELETE FROM employee WHERE emp_id='$emp_id'";
      if ($conn->query($sql_query) === TRUE) {
        if ($is_ajax) {
          echo 'SUCCESS';
        } else {
          echo '<script>';
          echo 'alert("Employee has been deleted successfully!");';
          echo 'window.location.href="Adminpage.php#Employee";';
          echo '</script>';
        }
      } else {
        if ($is_ajax) {
          echo 'ERROR: ' . mysqli_error($conn);
        } else {
          $error = addslashes(mysqli_error($conn));
          echo '<script>alert("Error deleting employee: ' . $error . '");</script>';
        }
      }
  } else {
      if ($is_ajax) {
        echo 'ERROR: Employee not found';
      } else {
        echo '<script>';
        echo 'alert("Sorry, the employee ID does not exist in database.");';
        echo 'window.location.href="Adminpage.php#Employee";';
        echo '</script>';
      }
  }

  mysqli_close($conn);
  if ($is_ajax) {
    exit;
  }
}

// Bulk Delete Flights
if(isset($_POST['bulk_delete']) && isset($_POST['flight_numbers'])){
  // Reconnect to database if connection was closed
  if(!isset($conn) || !$conn) {
    $conn = mysqli_connect("localhost", "root", "", "airport_management_system");
  }
  
  $flight_numbers_str = $_POST['flight_numbers'];
  $flight_numbers = explode(',', $flight_numbers_str);
  
  $success_count = 0;
  $error_count = 0;
  $errors = [];
  
  foreach($flight_numbers as $flight_number) {
    $flight_number = mysqli_real_escape_string($conn, trim($flight_number));
    
    if(empty($flight_number)) {
      continue;
    }
    
    // Check if flight exists
    $sql = "SELECT * FROM flights WHERE flight_number='$flight_number'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
      // Delete the flight
      $sql_query = "DELETE FROM flights WHERE flight_number='$flight_number'";
      if (mysqli_query($conn, $sql_query)) {
        $success_count++;
      } else {
        $error_count++;
        $errors[] = $flight_number . ': ' . mysqli_error($conn);
      }
    } else {
      $error_count++;
      $errors[] = $flight_number . ': Flight not found';
    }
  }
  
  if ($is_ajax) {
    if ($error_count == 0 && $success_count > 0) {
      echo 'SUCCESS';
    } else if ($success_count > 0) {
      echo 'PARTIAL_SUCCESS: ' . $success_count . ' deleted, ' . $error_count . ' failed. ' . implode(', ', $errors);
    } else {
      echo 'ERROR: ' . implode(', ', $errors);
    }
    mysqli_close($conn);
    exit;
  } else {
    if ($error_count == 0 && $success_count > 0) {
      echo '<script>';
      echo 'alert("Successfully deleted ' . $success_count . ' flight(s)!");';
      echo 'window.location.href="Adminpage.php#Flights";';
      echo '</script>';
    } else {
      $error_msg = addslashes('Partially successful: ' . $success_count . ' deleted, ' . $error_count . ' failed. ' . implode(', ', $errors));
      echo '<script>alert("' . $error_msg . '"); window.location.href="Adminpage.php#Flights";</script>';
    }
  }
  
  mysqli_close($conn);
  if ($is_ajax) {
    exit;
  }
}
