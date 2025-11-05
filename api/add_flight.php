<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once __DIR__ . '/db.php'; // provides $mysqli

function respond($arr, $code = 200) {
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

// Accept JSON or form data
$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!is_array($data) || empty($data)) {
  $data = $_POST;
}

// Validate required fields
$required = ['flight_number','flight_company','departing_time','arrival_time','no_of_seats','source','destination','price_economy','price_business','price_first'];
foreach ($required as $f) {
  if (!isset($data[$f]) || $data[$f] === '') {
    respond(['success'=>false,'error'=>"Missing field: $f"], 400);
  }
}

$flight_number = strtoupper(trim($data['flight_number']));
$flight_company = trim($data['flight_company']);
$departing_time = trim($data['departing_time']);
$arrival_time = trim($data['arrival_time']);
$no_of_seats = (int)$data['no_of_seats'];
$source = trim($data['source']);
$destination = trim($data['destination']);
$price_economy = (float)$data['price_economy'];
$price_business = (float)$data['price_business'];
$price_first = (float)$data['price_first'];

if ($no_of_seats <= 0) respond(['success'=>false,'error'=>'No of seats must be greater than 0'], 400);
if ($source === $destination) respond(['success'=>false,'error'=>'Source and destination cannot be the same'], 400);

// Insert or update flight
$stmt = $mysqli->prepare("INSERT INTO flights (flight_number, flight_company, departing_time, arrival_time, no_of_seats, source, destination, price_economy, price_business, price_first)
                          VALUES (?,?,?,?,?,?,?,?,?,?)
                          ON DUPLICATE KEY UPDATE flight_company=VALUES(flight_company), departing_time=VALUES(departing_time), arrival_time=VALUES(arrival_time), no_of_seats=VALUES(no_of_seats), source=VALUES(source), destination=VALUES(destination), price_economy=VALUES(price_economy), price_business=VALUES(price_business), price_first=VALUES(price_first)");
$stmt->bind_param('ssssisssdd', $flight_number, $flight_company, $departing_time, $arrival_time, $no_of_seats, $source, $destination, $price_economy, $price_business, $price_first);

if (!$stmt->execute()) {
  respond(['success'=>false,'error'=>'Insert failed','details'=>$stmt->error], 500);
}

// Create seats for this flight (idempotent)
$seats_per_row = 6;
$rows = (int)ceil($no_of_seats / $seats_per_row);
$ins = $mysqli->prepare("INSERT IGNORE INTO seats (flight_number, seat_number, is_available, is_reserved) VALUES (?,?,1,0)");
for ($r=1; $r <= $rows; $r++) {
  for ($c=1; $c <= $seats_per_row; $c++) {
    if ((($r-1)*$seats_per_row + $c) > $no_of_seats) break;
    $seat = $r . chr(64+$c);
    $ins->bind_param('ss', $flight_number, $seat);
    $ins->execute();
  }
}

respond(['success'=>true,'message'=>'Flight saved','flight_number'=>$flight_number]);

























