<?php
// backend/helpers/Response.php
declare(strict_types=1);

function json_headers(int $status = 200): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
}

function respond(array $data, int $status = 200): void {
  json_headers($status);
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function error_response(string $message, int $status = 400, array $extra = []): void {
  json_headers($status);
  $payload = array_merge(['error' => $message], $extra);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function read_json(): array {
  $raw = file_get_contents('php://input') ?: '';
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
