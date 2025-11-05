<?php
/**
 * Delete All Bookings Script
 * WARNING: This will delete ALL bookings, tickets, payments, and passenger data from the database
 */

$servername = "localhost";
$username = "root";
$password = "";
$database_name = "airport_management_system";

$conn = mysqli_connect($servername, $username, $password, $database_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2>Deleting All Bookings...</h2>";
echo "<pre>";

mysqli_begin_transaction($conn);

try {
    // 1. Delete all payments
    $result = mysqli_query($conn, "DELETE FROM payments");
    $deleted = mysqli_affected_rows($conn);
    echo "Deleted $deleted payment(s)\n";
    
    // 2. Delete all tickets
    $result = mysqli_query($conn, "DELETE FROM tickets");
    $deleted = mysqli_affected_rows($conn);
    echo "Deleted $deleted ticket(s)\n";
    
    // 3. Delete all passengers
    $result = mysqli_query($conn, "DELETE FROM passenger");
    $deleted = mysqli_affected_rows($conn);
    echo "Deleted $deleted passenger(s)\n";
    
    // 4. Delete all reservations
    $result = mysqli_query($conn, "DELETE FROM reservations");
    $deleted = mysqli_affected_rows($conn);
    echo "Deleted $deleted reservation(s)\n";
    
    // 5. Update all seats to available
    $result = mysqli_query($conn, "UPDATE seats SET is_available = 1, is_reserved = 0, reserved_until = NULL");
    $updated = mysqli_affected_rows($conn);
    echo "Updated $updated seat(s) to available\n";
    
    mysqli_commit($conn);
    
    echo "\n✅ SUCCESS: All bookings have been deleted!\n";
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Transaction rolled back. No data was deleted.\n";
}

mysqli_close($conn);

echo "</pre>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete All Bookings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            color: #333;
        }
        pre {
            background: white;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <a href="view_all_bookings.html" class="back-link">← Back to Bookings</a>
</body>
</html>










