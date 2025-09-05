<?php
// backend/public/index.php — router (front controller)
declare(strict_types=1);

// CORS (adjust for production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }

// Force JSON responses by default
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

// Parse route params
$r = $_GET['r'] ?? '';
$a = $_GET['a'] ?? '';
if ($r === '' || $a === '') error_response('Missing r/a', 400);

// Map resource => controller file
$map = [
  'auth'         => __DIR__ . '/../controllers/AuthController.php',
  'users'        => __DIR__ . '/../controllers/UsersController.php',
  'menu'         => __DIR__ . '/../controllers/MenuController.php',
  'tables'       => __DIR__ . '/../controllers/TablesController.php',
  'reservations' => __DIR__ . '/../controllers/ReservationsController.php',
  'orders'       => __DIR__ . '/../controllers/OrdersController.php',
  'reviews'      => __DIR__ . '/../controllers/ReviewsController.php',
  'analytics'    => __DIR__ . '/../controllers/AnalyticsController.php',
  'payment'      => __DIR__ . '/../controllers/PaymentController.php',
  // 'assets'     => __DIR__ . '/../controllers/AssetsController.php',
];

// Validate resource file
if (!isset($map[$r]) || !file_exists($map[$r])) error_response('Resource not found', 404);
require_once $map[$r];

// Map resource => controller class
$controllerMap = [
  'auth'         => 'AuthController',
  'users'        => 'UsersController',
  'menu'         => 'MenuController',
  'tables'       => 'TablesController',
  'reservations' => 'ReservationsController',
  'orders'       => 'OrdersController',
  'reviews'      => 'ReviewsController',
  'analytics'    => 'AnalyticsController',
  'payment'      => 'PaymentController',
  // 'assets'     => 'AssetsController',
];

// Validate controller class
if (!isset($controllerMap[$r]) || !class_exists($controllerMap[$r])) error_response('Controller not found', 404);

// Validate action method
$cls = $controllerMap[$r];
if (!method_exists($cls, $a)) error_response('Action not found', 404);

// Open DB connection
$conn = db();

try {
  // Dispatch to static method: Controller::action($conn)
  call_user_func([$cls, $a], $conn);
} catch (Throwable $e) {
  error_response('Server error', 500);
} finally {
  if ($conn instanceof mysqli) { $conn->close(); }
}
