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
$servername="localhost";
$username="root";
$password="";
$database_name="airport_management_system";

$conn=mysqli_connect($servername,$username,$password,$database_name);

if(!$conn){
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
  $flight_number = mysqli_real_escape_string($conn, $_POST['flight_number']);
  
  // Check if this is an AJAX request
  $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
  
  $sql = "SELECT * FROM flights WHERE flight_number='$flight_number'";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result)>0) {
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
      if ($is_ajax) {
        echo 'ERROR: Flight not found';
      } else {
        echo '<script>';
        echo 'alert("Sorry, the flight number does not exist in database.");';
        echo 'window.location.href="Adminpage.php#Flights";';
        echo '</script>';
      }
  }

  mysqli_close($conn);
  if ($is_ajax) {
    exit;
  }
}






if(isset($_POST['save3'])){
  $emp_id = mysqli_real_escape_string($conn, $_POST['emp_id']);
  
  // Check if this is an AJAX request
  $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
  
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
  
  // Check if this is an AJAX request
  $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
  
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
