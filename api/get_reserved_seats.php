<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$flight = isset($_GET['flight_number']) ? trim($_GET['flight_number']) : '';
if ($flight === '') { echo json_encode([]); exit; }

$stmt = $mysqli->prepare("SELECT seat FROM reservations WHERE flight_number = ?");
if (!$stmt) { http_response_code(500); echo json_encode(['error'=>'prepare_failed','details'=>$mysqli->error]); exit; }
$stmt->bind_param('s', $flight);
$stmt->execute();
$res = $stmt->get_result();
$seats = [];
while ($row = $res->fetch_assoc()) $seats[] = $row['seat'];
echo json_encode($seats);