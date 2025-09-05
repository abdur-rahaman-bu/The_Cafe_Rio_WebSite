<?php
// backend/controllers/OrdersController.php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class OrdersController
{
  // POST ?r=orders&a=create
  // Body: { user_id, items:[{item_id,qty}]? , notes?, payment_method?, total_amount? }
  public static function create(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);

    $data = read_json();
    $missing = require_fields($data, ['user_id']);
    if ($missing) error_response("Missing field: $missing", 400);

    $user_id = (int)$data['user_id'];
    $items   = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
    $notes   = s($data['notes'] ?? '');
    $pm      = s($data['payment_method'] ?? 'cod');

    if (!is_positive_int($user_id)) error_response('Invalid user_id', 400);
    if (!in_array($pm, ['cod','bkash','nagad','sslcommerz'], true)) error_response('Invalid payment_method', 400);

    // Ensure user exists
    $chk = $conn->prepare("SELECT user_id FROM users WHERE user_id=? LIMIT 1");
    $chk->bind_param('i', $user_id);
    $chk->execute();
    $u = $chk->get_result()->fetch_assoc();
    $chk->close();
    if (!$u) error_response('User not found', 400);

    $total = 0.0; $lines = [];
    if (!empty($items)) {
      // normalize items and aggregate qty by item_id
      $idSet = [];
      foreach ($items as $it) {
        $iid = (int)($it['item_id'] ?? 0);
        $qty = (int)($it['qty'] ?? 0);
        if ($iid <= 0 || $qty <= 0) error_response('Invalid item in items list', 400);
        $idSet[$iid] = ($idSet[$iid] ?? 0) + $qty;
      }
      $ids = array_keys($idSet);
      if (empty($ids)) error_response('No valid items', 400);

      // fetch catalog prices
      $in = implode(',', array_fill(0, count($ids), '?'));
      $types = str_repeat('i', count($ids));
      $q = $conn->prepare("SELECT item_id,name,price FROM menu_items WHERE item_id IN ($in)");
      $q->bind_param($types, ...$ids);
      $q->execute();
      $rs = $q->get_result();
      $catalog = [];
      while ($r = $rs->fetch_assoc()) {
        $catalog[(int)$r['item_id']] = ['name'=>$r['name'], 'price'=>(float)$r['price']];
      }
      $q->close();

      // compute lines
      foreach ($idSet as $iid => $qty) {
        if (!isset($catalog[$iid])) error_response("Menu item not found: $iid", 400);
        $name  = $catalog[$iid]['name'];
        $price = $catalog[$iid]['price'];
        $lt    = $price * $qty;
        $total += $lt;
        $lines[] = ['item_id'=>$iid, 'name'=>$name, 'unit_price'=>$price, 'qty'=>$qty, 'line_total'=>$lt];
      }
    } else {
      // mock: rely on provided total
      $total = isset($data['total_amount']) ? (float)$data['total_amount'] : -1;
      if ($total < 0) error_response('Either items[] or a valid total_amount is required', 400);
    }

    // create in transaction
    $conn->begin_transaction();
    try {
      $status = 'pending';
      $pstatus = 'unpaid';
      $stmt = $conn->prepare("INSERT INTO orders (user_id,status,total_amount,payment_method,payment_status,notes) VALUES (?,?,?,?,?,?)");
      $stmt->bind_param('isdsss', $user_id, $status, $total, $pm, $pstatus, $notes);
      if (!$stmt->execute()) throw new Exception('DB error while creating order');
      $oid = $stmt->insert_id;
      $stmt->close();

      if (!empty($lines)) {
        // order_items: (order_id,item_id,name,unit_price,qty,line_total)
        $ins = $conn->prepare("INSERT INTO order_items (order_id,item_id,name,unit_price,qty,line_total) VALUES (?,?,?,?,?,?)");
        foreach ($lines as $ln) {
          $iid = $ln['item_id'];
          $nm  = $ln['name'];
          $up  = $ln['unit_price'];
          $qty = $ln['qty'];
          $lt  = $ln['line_total'];
          $ins->bind_param('iisdid', $oid, $iid, $nm, $up, $qty, $lt); // i i s d i d
          if (!$ins->execute()) throw new Exception('DB error while creating order items');
        }
        $ins->close();
      }

      $conn->commit();
      respond(['message'=>'Order created','order'=>[
        'order_id'=>$oid,'user_id'=>$user_id,'status'=>$status,'total_amount'=>$total,'payment_method'=>$pm,'payment_status'=>$pstatus,'items'=>$lines
      ]], 201);
    } catch (Throwable $e) {
      $conn->rollback();
      error_response($e->getMessage(), 500);
    }
  }

  // POST ?r=orders&a=pay  Body: { actor_user_id, order_id }
  // User-scoped payment: marks payment_status='paid' and status='confirmed' if owner matches and currently unpaid
  public static function pay(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $d = read_json();
    $missing = require_fields($d, ['actor_user_id','order_id']);
    if ($missing) error_response("Missing field: $missing", 400);

    $actor = (int)$d['actor_user_id'];
    $oid   = (int)$d['order_id'];
    if ($actor <= 0 || $oid <= 0) error_response('Invalid actor/order', 400);

    // fetch order
    $st = $conn->prepare("SELECT user_id,payment_status,status,total_amount FROM orders WHERE order_id=? LIMIT 1");
    $st->bind_param('i', $oid);
    $st->execute();
    $o = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$o) error_response('Order not found', 404);
    if ((int)$o['user_id'] !== $actor) error_response('Not allowed (ownership)', 403);
    if ((string)$o['payment_status'] === 'paid') respond(['message'=>'Already paid','order_id'=>$oid,'payment_status'=>'paid','status'=>$o['status']]);

    // simulate successful payment and confirm order
    $status = 'confirmed';
    $pstatus = 'paid';
    $u = $conn->prepare("UPDATE orders SET payment_status=?, status=? WHERE order_id=?");
    $u->bind_param('ssi', $pstatus, $status, $oid);
    if (!$u->execute()) error_response('DB error while updating payment', 500);
    $u->close();
    respond(['message'=>'Payment successful','order_id'=>$oid,'status'=>$status,'payment_status'=>$pstatus,'total_amount'=>(float)$o['total_amount']]);
  }

  // Admin list — GET ?r=orders&a=list&limit=100&actor_user_id=1
  public static function list(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);
    $actor = isset($_GET['actor_user_id']) ? (int)$_GET['actor_user_id'] : 0;
    if (!is_admin_user($conn, $actor)) error_response('Unauthorized (admin only)', 401);

    $status    = isset($_GET['status']) ? s($_GET['status']) : null;
    $date_from = isset($_GET['date_from']) ? s($_GET['date_from']) : null;
    $date_to   = isset($_GET['date_to']) ? s($_GET['date_to']) : null;
    $user_id   = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

    if ($date_from !== null && !validate_date($date_from, 'Y-m-d')) $date_from = null;
    if ($date_to   !== null && !validate_date($date_to,   'Y-m-d')) $date_to = null;

    [$page,$limit,$offset] = validate_pagination($_GET['page'] ?? 1, $_GET['limit'] ?? 100, 200);

    $where=[]; $types=''; $params=[];
    if ($status)    { $where[]='o.status = ?';            $types.='s'; $params[]=$status; }
    if ($date_from) { $where[]='DATE(o.created_at) >= ?'; $types.='s'; $params[]=$date_from; }
    if ($date_to)   { $where[]='DATE(o.created_at) <= ?'; $types.='s'; $params[]=$date_to; }
    if ($user_id)   { $where[]='o.user_id = ?';           $types.='i'; $params[]=$user_id; }
    $whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

    $sql = "SELECT o.order_id,o.user_id,u.name AS customer_name,o.status,o.total_amount,o.payment_status,o.payment_method,o.created_at
            FROM orders o JOIN users u ON u.user_id=o.user_id
            $whereSql
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?";
    $types .= 'ii'; $params[] = $limit; $params[] = $offset;

    $st = $conn->prepare($sql);
    if ($types) $st->bind_param($types, ...$params);
    if (!$st->execute()) error_response('DB error while fetching orders', 500);
    $rs = $st->get_result();
    $items=[];
    while ($r = $rs->fetch_assoc()) {
      $items[] = [
        'order_id'       => (int)$r['order_id'],
        'user_id'        => (int)$r['user_id'],
        'customer_name'  => $r['customer_name'],
        'status'         => $r['status'],
        'payment_status' => $r['payment_status'],
        'payment_method' => $r['payment_method'],
        'total_amount'   => (float)$r['total_amount'],
        'created_at'     => $r['created_at'],
      ];
    }
    $st->close();
    respond(['page'=>$page,'limit'=>$limit,'count'=>count($items),'items'=>$items]);
  }

  // Admin details — GET ?r=orders&a=details&order_id=123&actor_user_id=1
  public static function details(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);
    $actor = isset($_GET['actor_user_id']) ? (int)$_GET['actor_user_id'] : 0;
    if (!is_admin_user($conn, $actor)) error_response('Unauthorized (admin only)', 401);

    $oid = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
    if ($oid <= 0) error_response('Invalid order_id', 400);

    $st = $conn->prepare("SELECT o.order_id,o.user_id,u.name AS customer_name,o.total_amount,o.status,o.payment_status,o.payment_method,o.notes,o.created_at
                          FROM orders o JOIN users u ON u.user_id=o.user_id WHERE o.order_id=? LIMIT 1");
    $st->bind_param('i', $oid);
    $st->execute();
    $order = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$order) error_response('Order not found', 404);

    $q = $conn->prepare("SELECT item_id,name,unit_price,qty,line_total FROM order_items WHERE order_id=?");
    $q->bind_param('i', $oid);
    $q->execute();
    $rs = $q->get_result();
    $lines=[];
    while ($r = $rs->fetch_assoc()) {
      $lines[] = [
        'item_id'=>(int)$r['item_id'],
        'name'=>$r['name'],
        'unit_price'=>(float)$r['unit_price'],
        'qty'=>(int)$r['qty'],
        'line_total'=>(float)$r['line_total'],
      ];
    }
    $q->close();
    respond(['order'=>$order,'items'=>$lines]);
  }

  // User-scoped list — GET ?r=orders&a=user_list&actor_user_id=5
  public static function user_list(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);
    $actor = isset($_GET['actor_user_id']) ? (int)$_GET['actor_user_id'] : 0;
    if ($actor <= 0) error_response('actor_user_id required', 400);

    [$page,$limit,$offset] = validate_pagination($_GET['page'] ?? 1, $_GET['limit'] ?? 100, 200);

    $sql = "SELECT o.order_id,o.status,o.total_amount,o.payment_status,o.payment_method,o.created_at
            FROM orders o
            WHERE o.user_id=?
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?";
    $st = $conn->prepare($sql);
    $st->bind_param('iii', $actor, $limit, $offset);
    if (!$st->execute()) error_response('DB error while fetching orders', 500);
    $rs = $st->get_result();
    $items=[];
    while ($r = $rs->fetch_assoc()) {
      $items[] = [
        'order_id'=>(int)$r['order_id'],
        'status'=>$r['status'],
        'payment_status'=>$r['payment_status'],
        'payment_method'=>$r['payment_method'],
        'total_amount'=>(float)$r['total_amount'],
        'created_at'=>$r['created_at'],
      ];
    }
    $st->close();
    respond(['page'=>$page,'limit'=>$limit,'count'=>count($items),'items'=>$items]);
  }
}
