<?php
// show & log errors for debugging (remove in production)
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true);
if (!is_array($input)) { http_response_code(400); echo json_encode(['error'=>'Invalid JSON']); exit; }

$flight_number = trim($input['flight_number'] ?? '');
$name = trim($input['name'] ?? '');
$passport = trim($input['passport'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$seat = trim($input['seat'] ?? '');
$travel_class = trim($input['travel_class'] ?? '');

if (!$flight_number || !$name || !$seat) { http_response_code(400); echo json_encode(['error'=>'Missing fields']); exit; }

// Validate passport format: exactly 8 characters, first is uppercase letter A-Z, next 7 are digits
// First digit cannot be 0, last digit cannot be 0
if ($passport) {
    $cleanPassport = preg_replace('/[\s-]/', '', $passport); // Remove spaces and dashes
    if (strlen($cleanPassport) !== 8) {
        http_response_code(400);
        echo json_encode(['error' => 'Passport/ID must be exactly 8 characters long']);
        exit;
    }
    if (!preg_match('/^[A-Z][0-9]{7}$/', $cleanPassport)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid passport/ID format']);
        exit;
    }
    if ($cleanPassport[1] === '0') {
        http_response_code(400);
        echo json_encode(['error' => 'First digit after the letter cannot be 0']);
        exit;
    }
    if ($cleanPassport[7] === '0') {
        http_response_code(400);
        echo json_encode(['error' => 'Last digit cannot be 0']);
        exit;
    }
    $passport = $cleanPassport; // Use the cleaned version
}

$mysqli->begin_transaction();
try {
    $chk = $mysqli->prepare("SELECT flight_number FROM flights WHERE flight_number = ? LIMIT 1 FOR UPDATE");
    if (!$chk) throw new Exception($mysqli->error);
    $chk->bind_param('s', $flight_number);
    $chk->execute();
    $r = $chk->get_result();
    if ($r->num_rows === 0) {
        $mysqli->rollback();
        http_response_code(404);
        echo json_encode(['error'=>'Flight not found']);
        exit;
    }
    $chk->close();

    $checkSeat = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM reservations WHERE flight_number = ? AND seat = ? FOR UPDATE");
    if (!$checkSeat) throw new Exception($mysqli->error);
    $checkSeat->bind_param('ss', $flight_number, $seat);
    $checkSeat->execute();
    $cntRow = $checkSeat->get_result()->fetch_assoc();
    $checkSeat->close();
    if ($cntRow && intval($cntRow['cnt']) > 0) {
        $mysqli->rollback();
        http_response_code(409);
        echo json_encode(['error'=>'Seat already reserved']);
        exit;
    }

    $ins = $mysqli->prepare("INSERT INTO reservations (flight_number, passenger_name, passport, email, phone, seat, travel_class) VALUES (?,?,?,?,?,?,?)");
    if (!$ins) throw new Exception($mysqli->error);
    $ins->bind_param('sssssss', $flight_number, $name, $passport, $email, $phone, $seat, $travel_class);
    if (!$ins->execute()) throw new Exception($ins->error);
    $reservation_id = $mysqli->insert_id;
    $ins->close();

    $mysqli->commit();
    echo json_encode(['success'=>true, 'reservation_id'=>$reservation_id]);
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['error'=>'Server error','details'=>$e->getMessage()]);
}