<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$database_name = "airport_management_system";

$conn = mysqli_connect($servername, $username, $password, $database_name);

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $block_id = mysqli_real_escape_string($conn, $_POST['block_id']);
    
    if (empty($block_id)) {
        echo json_encode(['success' => false, 'message' => 'Block ID is required']);
        exit;
    }
    
    // Get flight number from block record
    $get_flight_sql = "SELECT flight_number FROM flight_blocks WHERE block_id = '$block_id'";
    $get_flight_result = mysqli_query($conn, $get_flight_sql);
    
    if (mysqli_num_rows($get_flight_result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Block record not found']);
        exit;
    }
    
    $flight_row = mysqli_fetch_assoc($get_flight_result);
    $flight_number = $flight_row['flight_number'];
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update flight status to unblocked
        $update_sql = "UPDATE flights SET is_blocked = 0, blocked_from = NULL, blocked_until = NULL 
                       WHERE flight_number = '$flight_number'";
        
        if (!mysqli_query($conn, $update_sql)) {
            throw new Exception("Error updating flight status: " . mysqli_error($conn));
        }
        
        // Delete the block record
        $delete_sql = "DELETE FROM flight_blocks WHERE block_id = '$block_id'";
        
        if (!mysqli_query($conn, $delete_sql)) {
            throw new Exception("Error deleting block record: " . mysqli_error($conn));
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode(['success' => true, 'message' => 'Flight unblocked successfully']);
        
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

mysqli_close($conn);
?>






