<?php
// backend/controllers/ReviewsController.php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class ReviewsController
{
  // POST ?r=reviews&a=create
  // Body: { user_id, item_id, rating(1..5), comment? }
  public static function create(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $d = read_json();
    $missing = require_fields($d, ['user_id','item_id','rating']);
    if ($missing) error_response("Missing field: $missing", 400);

    $user_id = (int)$d['user_id'];
    $item_id = (int)$d['item_id'];
    $rating  = (int)$d['rating'];
    $comment = s($d['comment'] ?? '');

    if (!is_positive_int($user_id)) error_response('Invalid user_id', 400);
    if (!is_positive_int($item_id)) error_response('Invalid item_id', 400);
    if ($rating < 1 || $rating > 5) error_response('Rating must be 1..5', 400);

    // ensure user exists
    $q = $conn->prepare("SELECT user_id FROM users WHERE user_id=? LIMIT 1");
    $q->bind_param('i', $user_id);
    $q->execute(); $u = $q->get_result()->fetch_assoc(); $q->close();
    if (!$u) error_response('User not found', 400);

    // ensure item exists
    $q = $conn->prepare("SELECT item_id FROM menu_items WHERE item_id=? LIMIT 1");
    $q->bind_param('i', $item_id);
    $q->execute(); $m = $q->get_result()->fetch_assoc(); $q->close();
    if (!$m) error_response('Menu item not found', 400);

    // DUPLICATE CHECK REMOVED to allow multiple reviews per user-item

    $st = $conn->prepare("INSERT INTO reviews (user_id,item_id,rating,comment,created_at) VALUES (?,?,?,?,NOW())");
    $st->bind_param('iiis', $user_id, $item_id, $rating, $comment);
    if (!$st->execute()) error_response('DB error creating review', 500);
    $rid = $st->insert_id; $st->close();

    respond(['message'=>'Review created','review'=>[
      'review_id'=>$rid,'user_id'=>$user_id,'item_id'=>$item_id,'rating'=>$rating,'comment'=>$comment
    ]], 201);
  }

  // GET ?r=reviews&a=list&item_id=123&limit=50&page=1
  // returns: { rating_avg, rating_count, items: [...] }
  public static function list(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);
    $item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
    if ($item_id <= 0) error_response('item_id required', 400);
    [$page,$limit,$offset] = validate_pagination($_GET['page'] ?? 1, $_GET['limit'] ?? 50, 200);

    // aggregate
    $ag = $conn->prepare("SELECT AVG(rating) AS rating_avg, COUNT(*) AS rating_count FROM reviews WHERE item_id=?");
    $ag->bind_param('i', $item_id);
    $ag->execute(); $agg = $ag->get_result()->fetch_assoc() ?: ['rating_avg'=>null,'rating_count'=>0]; $ag->close();

    // include reviewer name
    $st = $conn->prepare("
      SELECT r.review_id, r.user_id, u.name AS user_name, r.rating, r.comment, r.created_at
      FROM reviews r
      JOIN users u ON u.user_id = r.user_id
      WHERE r.item_id=?
      ORDER BY r.created_at DESC
      LIMIT ? OFFSET ?
    ");
    $st->bind_param('iii', $item_id, $limit, $offset);
    $st->execute(); $rs = $st->get_result();
    $items = [];
    while ($r = $rs->fetch_assoc()) {
      $items[] = [
        'review_id'=>(int)$r['review_id'],
        'user_id'=>(int)$r['user_id'],
        'user_name'=>$r['user_name'],
        'rating'=>(int)$r['rating'],
        'comment'=>$r['comment'],
        'created_at'=>$r['created_at'],
      ];
    }
    $st->close();

    respond([
      'page'=>$page,'limit'=>$limit,'count'=>count($items),
      'rating_avg'=> $agg['rating_avg'] !== null ? round((float)$agg['rating_avg'], 2) : null,
      'rating_count'=> (int)$agg['rating_count'],
      'items'=>$items
    ]);
  }

  // GET ?r=reviews&a=user_list&actor_user_id=5
  public static function user_list(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);
    $actor = isset($_GET['actor_user_id']) ? (int)$_GET['actor_user_id'] : 0;
    if ($actor <= 0) error_response('actor_user_id required', 400);
    [$page,$limit,$offset] = validate_pagination($_GET['page'] ?? 1, $_GET['limit'] ?? 100, 200);

    $st = $conn->prepare("SELECT r.review_id,r.item_id,m.name AS item_name,r.rating,r.comment,r.created_at
                          FROM reviews r JOIN menu_items m ON m.item_id=r.item_id
                          WHERE r.user_id=? ORDER BY r.created_at DESC LIMIT ? OFFSET ?");
    $st->bind_param('iii', $actor, $limit, $offset);
    $st->execute(); $rs = $st->get_result();
    $items=[];
    while ($r = $rs->fetch_assoc()) {
      $items[] = [
        'review_id'=>(int)$r['review_id'],
        'item_id'=>(int)$r['item_id'],
        'item_name'=>$r['item_name'],
        'rating'=>(int)$r['rating'],
        'comment'=>$r['comment'],
        'created_at'=>$r['created_at'],
      ];
    }
    $st->close();
    respond(['page'=>$page,'limit'=>$limit,'count'=>count($items),'items'=>$items]);
  }

  // GET ?r=reviews&a=admin_list&actor_user_id=1&item_id?=&user_id?=&rating_min?=&date_from?=&date_to?=
  public static function admin_list(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);
    $actor = isset($_GET['actor_user_id']) ? (int)$_GET['actor_user_id'] : 0;
    if (!is_admin_user($conn, $actor)) error_response('Unauthorized (admin only)', 401);

    $item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : null;
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
    $rating_min = isset($_GET['rating_min']) ? (int)$_GET['rating_min'] : null;
    $date_from = isset($_GET['date_from']) ? s($_GET['date_from']) : null;
    $date_to   = isset($_GET['date_to'])   ? s($_GET['date_to'])   : null;

    if ($date_from !== null && !validate_date($date_from, 'Y-m-d')) $date_from = null;
    if ($date_to   !== null && !validate_date($date_to,   'Y-m-d')) $date_to   = null;

    [$page,$limit,$offset] = validate_pagination($_GET['page'] ?? 1, $_GET['limit'] ?? 100, 200);

    $where=[]; $types=''; $params=[];
    if ($item_id)   { $where[]='r.item_id=?'; $types.='i'; $params[]=$item_id; }
    if ($user_id)   { $where[]='r.user_id=?'; $types.='i'; $params[]=$user_id; }
    if ($rating_min){ $where[]='r.rating>=?'; $types.='i'; $params[]=$rating_min; }
    if ($date_from) { $where[]='DATE(r.created_at) >= ?'; $types.='s'; $params[]=$date_from; }
    if ($date_to)   { $where[]='DATE(r.created_at) <= ?'; $types.='s'; $params[]=$date_to; }
    $whereSql = $where ? ('WHERE '.implode(' AND ',$where)) : '';

    $sql = "SELECT r.review_id,r.user_id,u.name AS user_name,r.item_id,m.name AS item_name,r.rating,r.comment,r.created_at
            FROM reviews r
            JOIN users u ON u.user_id=r.user_id
            JOIN menu_items m ON m.item_id=r.item_id
            $whereSql
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?";
    $types.='ii'; $params[]=$limit; $params[]=$offset;

    $st = $conn->prepare($sql);
    if ($types) $st->bind_param($types, ...$params);
    if (!$st->execute()) error_response('DB error', 500);
    $rs = $st->get_result();
    $items=[];
    while ($r = $rs->fetch_assoc()) {
      $items[] = [
        'review_id'=>(int)$r['review_id'],
        'user_id'=>(int)$r['user_id'],
        'user_name'=>$r['user_name'],
        'item_id'=>(int)$r['item_id'],
        'item_name'=>$r['item_name'],
        'rating'=>(int)$r['rating'],
        'comment'=>$r['comment'],
        'created_at'=>$r['created_at'],
      ];
    }
    $st->close();
    respond(['page'=>$page,'limit'=>$limit,'count'=>count($items),'items'=>$items]);
  }

  // POST ?r=reviews&a=delete  Body: { actor_user_id, review_id }
  public static function delete(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $d = read_json();
    $missing = require_fields($d, ['actor_user_id','review_id']);
    if ($missing) error_response("Missing field: $missing", 400);

    $actor = (int)$d['actor_user_id'];
    if (!is_admin_user($conn, $actor)) error_response('Unauthorized (admin only)', 401);

    $rid = (int)$d['review_id'];
    if ($rid <= 0) error_response('Invalid review_id', 400);

    $st = $conn->prepare("DELETE FROM reviews WHERE review_id=?");
    $st->bind_param('i', $rid);
    if (!$st->execute()) error_response('DB error deleting review', 500);
    $st->close();

    respond(['message'=>'Deleted','review_id'=>$rid]);
  }
}
