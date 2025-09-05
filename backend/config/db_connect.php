<?php
// backend/config/db_connect.php
declare(strict_types=1);

function db(): mysqli {
  // Edit these to your local DB credentials
  $DB_HOST = '127.0.0.1';
  $DB_NAME = 'caferio';
  $DB_USER = 'root';
  $DB_PASS = '';
  $DB_CHARSET = 'utf8mb4';

  $conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
  if ($conn->connect_errno) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error'=>'DB connection failed','detail'=>$conn->connect_error], JSON_UNESCAPED_UNICODE);
    exit;
  }
  $conn->set_charset($DB_CHARSET);
  return $conn;
}
