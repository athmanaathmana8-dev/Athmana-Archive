<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Add Employee</title>
    <link rel="stylesheet" href="css/Adminpage.css">
  </head>
  <body class="separetephpfile">
    <h5>Click on the below button to go back to admin page</h5>
    <div class="new_button">
    <a class="button" href="Adminpage.php#Employee" class="">Admin Page</a>
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

if(isset($_POST['save1'])){
  // Sanitize inputs
  $emp_id = mysqli_real_escape_string($conn, $_POST['emp_id']);
  $emp_name = mysqli_real_escape_string($conn, $_POST['emp_name']);
  $job = mysqli_real_escape_string($conn, $_POST['job']);
  $salary = mysqli_real_escape_string($conn, $_POST['salary']);
  
  // Check if flight number is selected
  if(!empty($_POST['Fruit'])) {
    $selected = mysqli_real_escape_string($conn, $_POST['Fruit']);
  } else {
    echo '<script>';
    echo 'alert("Please select a flight number for the employee!");';
    echo 'window.location.href="Adminpage.php#Employee";';
    echo '</script>';
    mysqli_close($conn);
    exit;
  }

  // Get airport_id (assuming first airport or default)
  $airport_id = 1; // Default airport ID
  $airport_result = mysqli_query($conn, "SELECT airport_id FROM airport LIMIT 1");
  if($airport_result && mysqli_num_rows($airport_result) > 0) {
    $airport_row = mysqli_fetch_assoc($airport_result);
    $airport_id = $airport_row["airport_id"];
  }

  // Check if employee ID already exists
  $check_query = "SELECT * FROM employee WHERE emp_id='$emp_id'";
  $check_result = mysqli_query($conn, $check_query);
  
  if(mysqli_num_rows($check_result) > 0) {
    echo '<script>';
    echo 'alert("Employee ID already exists! Please use a different ID.");';
    echo 'window.location.href="Adminpage.php#Employee";';
    echo '</script>';
    mysqli_close($conn);
    exit;
  }

  // Insert employee with quotes around all string values
  $sql_query = "INSERT INTO employee(emp_id, emp_name, job, salary, airport_id, flight_number)
  VALUES ('$emp_id', '$emp_name', '$job', '$salary', '$airport_id', '$selected')";
  
  if (mysqli_query($conn, $sql_query)) {
    echo '<script>';
    echo 'alert("Employee added successfully!");';
    echo 'window.location.href="Adminpage.php#Employee";';
    echo '</script>';
  } else {
    $error = addslashes(mysqli_error($conn));
    echo '<script>';
    echo 'alert("Error adding employee: ' . $error . '");';
    echo 'window.location.href="Adminpage.php#Employee";';
    echo '</script>';
  }

  mysqli_close($conn);
}

?>
