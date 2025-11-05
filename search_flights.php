<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$servername = "localhost";
$username = "root";
$password = "";
$database_name = "airport_management_system";

$conn = mysqli_connect($servername, $username, $password, $database_name);

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $from = mysqli_real_escape_string($conn, $input['from'] ?? '');
    $to = mysqli_real_escape_string($conn, $input['to'] ?? '');
    $date = mysqli_real_escape_string($conn, $input['date'] ?? '');
    
    if (empty($from) || empty($to) || empty($date)) {
        echo json_encode(['error' => 'Missing required parameters']);
        exit;
    }
    
    // Search for flights (compatible with current schema)
    // Note: If your schema has departing_date, you can add: AND DATE(f.departing_time) = '$date'
    $sql = "SELECT f.*, 
                   (SELECT COUNT(*) FROM seats s WHERE s.flight_number = f.flight_number AND s.is_available = 1) as available_seats
            FROM flights f 
            WHERE f.source = '$from' 
            AND f.destination = '$to' 
            ORDER BY f.departing_time";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
        exit;
    }
    
    $flights = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $flights[] = [
            'flight_number' => $row['flight_number'],
            'flight_company' => $row['flight_company'],
            'departing_time' => $row['departing_time'],
            'arrival_time' => $row['arrival_time'],
            'price_economy' => (float)$row['price_economy'],
            'price_business' => (float)$row['price_business'],
            'price_first' => (float)$row['price_first'],
            'available_seats' => (int)$row['available_seats'],
            'source' => $row['source'],
            'destination' => $row['destination']
        ];
    }
    
    echo json_encode($flights); // Return array directly, not wrapped
    
} else {
    echo json_encode(['error' => 'Method not allowed']);
}

mysqli_close($conn);
?>
