
<?php
// Database connection - update these values to match your MySQL setup
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'airport_management_system';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed', 'details' => $mysqli->connect_error]);
    exit;
}
$mysqli->set_charset('utf8mb4');