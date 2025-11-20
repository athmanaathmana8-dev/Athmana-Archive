
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "airport_management_system";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

// Ensure admin table exists and default admin account is set up
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'admin'");
if (mysqli_num_rows($check_table) == 0) {
  // Create admin table
  $create_table_sql = "CREATE TABLE IF NOT EXISTS admin (
    admin_id VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    admin_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
  )";
  mysqli_query($conn, $create_table_sql);
}

// Ensure default admin account exists with correct credentials
$check_admin = mysqli_query($conn, "SELECT admin_id FROM admin WHERE admin_id = 'admin123'");
if (mysqli_num_rows($check_admin) == 0) {
  // Create default admin account
  $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
  $insert_sql = "INSERT INTO admin (admin_id, password, admin_name, email, is_active) 
                 VALUES ('admin123', '$hashed_password', 'Default Admin', 'admin@airport.com', TRUE)";
  mysqli_query($conn, $insert_sql);
} else {
  // Update password to ensure it's correct (in case it was changed)
  $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
  $update_sql = "UPDATE admin SET password = '$hashed_password', is_active = TRUE 
                 WHERE admin_id = 'admin123'";
  mysqli_query($conn, $update_sql);
}

if (isset($_POST['save8'])){
  $admin_id = mysqli_real_escape_string($conn, $_POST['admin_id']);
  $input_password = $_POST['password'];

  // Query admin from database
  $sql = "SELECT admin_id, password, admin_name, is_active FROM admin WHERE admin_id = '$admin_id'";
  $result = mysqli_query($conn, $sql);
  
  if ($result && mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
    
    // Check if admin is active
    if ($admin['is_active'] == 0 || $admin['is_active'] == false) {
      echo '<script>alert("Your admin account is inactive. Please contact system administrator.")</script>';
      include('Adminloginpage.html');
    } else {
      // Verify password
      if (password_verify($input_password, $admin['password'])) {
        // Update last login
        $update_login = "UPDATE admin SET last_login = CURRENT_TIMESTAMP WHERE admin_id = '$admin_id'";
        mysqli_query($conn, $update_login);
        
        // Start session for admin
        session_start();
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['admin_name'];
        
        echo '<script>alert("Hey admin, You have logged in successfully!")</script>';
        include('Adminpage.php');
      } else {
        echo '<script>alert("Invalid ID or Password")</script>';
        include('Adminloginpage.html');
      }
    }
  } else {
    echo '<script>alert("Invalid ID or Password")</script>';
    include('Adminloginpage.html');
  }
}

if(isset($_POST['save9'])){
   include('Frontpage.html');
}
?>
