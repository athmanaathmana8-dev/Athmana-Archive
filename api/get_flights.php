<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

require_once __DIR__ . '/db.php'; // ensure this file exists and has correct DB creds

$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to   = isset($_GET['to'])   ? trim($_GET['to'])   : '';

// base query: exclude currently blocked flights
$sql = "SELECT flight_number, flight_company, departing_time, arrival_time, no_of_seats, source, destination,
               price_economy, price_business, price_first, is_blocked, blocked_from, blocked_until, created_at, updated_at
        FROM flights
        WHERE (is_blocked = 0 OR (blocked_until IS NOT NULL AND blocked_until < NOW()))";

$params = [];
$types = '';
if ($from !== '') {
    $sql .= " AND LOWER(source) = LOWER(?)";
    $params[] = $from;
    $types .= 's';
}
if ($to !== '') {
    $sql .= " AND LOWER(destination) = LOWER(?)";
    $params[] = $to;
    $types .= 's';
}

$sql .= " ORDER BY departing_time ASC";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed', 'details' => $mysqli->error]);
    exit;
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Execute failed', 'details' => $stmt->error]);
    exit;
} 

$result = $stmt->get_result();
$flights = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($flights);