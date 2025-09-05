<?php
// backend/controllers/AnalyticsController.php — Admin KPIs for dashboard
declare(strict_types=1);

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class AnalyticsController
{
  // GET ?r=analytics&a=dashboard&actor_user_id=1
  public static function dashboard(mysqli $conn): void
  {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);

    $actor = isset($_GET['actor_user_id']) ? (int)$_GET['actor_user_id'] : 0;
    if (!is_admin_user($conn, $actor)) error_response('Unauthorized (admin only)', 401);

    // Orders last 24h: count + revenue, and status breakdown
    $q1 = $conn->query("
      SELECT
        COUNT(*)                           AS orders_24h,
        COALESCE(SUM(total_amount),0)      AS revenue_24h,
        SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) AS pending_24h,
        SUM(CASE WHEN status='confirmed' THEN 1 ELSE 0 END) AS confirmed_24h
      FROM orders
      WHERE created_at >= NOW() - INTERVAL 24 HOUR
    ");
    $o24 = $q1 ? $q1->fetch_assoc() : ['orders_24h'=>0,'revenue_24h'=>0,'pending_24h'=>0,'confirmed_24h'=>0];

    // Reservations last 24h count
    $q2 = $conn->query("
      SELECT COUNT(*) AS reservations_24h
      FROM reservations
      WHERE created_at >= NOW() - INTERVAL 24 HOUR
    ");
    $r24 = $q2 ? $q2->fetch_assoc() : ['reservations_24h'=>0];

    // Top items last 7 days (by qty)
    $st = $conn->prepare("
      SELECT oi.item_id, oi.name, SUM(oi.qty) AS qty, SUM(oi.line_total) AS revenue
      FROM order_items oi
      JOIN orders o ON o.order_id = oi.order_id
      WHERE o.created_at >= NOW() - INTERVAL 7 DAY
      GROUP BY oi.item_id, oi.name
      ORDER BY qty DESC
      LIMIT 5
    ");
    $st->execute();
    $rs = $st->get_result();
    $top = [];
    while($r=$rs->fetch_assoc()){
      $top[] = [
        'item_id' => (int)$r['item_id'],
        'name'    => $r['name'],
        'qty'     => (int)$r['qty'],
        'revenue' => (float)$r['revenue'],
      ];
    }
    $st->close();

    respond([
      'orders_24h'       => (int)$o24['orders_24h'],
      'revenue_24h'      => (float)$o24['revenue_24h'],
      'pending_24h'      => (int)$o24['pending_24h'],
      'confirmed_24h'    => (int)$o24['confirmed_24h'],
      'reservations_24h' => (int)$r24['reservations_24h'],
      'top_items_7d'     => $top
    ]);
  }
}
