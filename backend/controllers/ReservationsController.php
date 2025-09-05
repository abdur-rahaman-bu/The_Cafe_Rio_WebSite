<?php
// backend/controllers/ReservationsController.php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class ReservationsController
{
  // POST ?r=reservations&a=create
  // Body JSON: { user_id, reservation_date(Y-m-d), reservation_time(H:i), duration_minutes(>=30), people_count(>=1), table_type['family','couple','window'], table_id, special_request? }
  public static function create(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);

    $d = read_json();
    $missing = require_fields($d, ['user_id','reservation_date','reservation_time','duration_minutes','people_count','table_type','table_id']);
    if ($missing) error_response("Missing field: $missing", 400);

    $user_id = (int)$d['user_id'];
    $date = s($d['reservation_date']);
    $time = s($d['reservation_time']);
    $dur = (int)$d['duration_minutes'];
    $people = (int)$d['people_count'];
    $type = strtolower(s($d['table_type']));
    $table_id = (int)$d['table_id'];
    $special = s($d['special_request'] ?? '');

    if (!is_positive_int($user_id)) error_response('Invalid user_id', 400);
    if (!validate_date($date, 'Y-m-d')) error_response('Invalid reservation_date', 400);
    if (!validate_time(substr($time,0,5), 'H:i')) error_response('Invalid reservation_time', 400);
    if ($dur < 30 || $dur > 240) error_response('Invalid duration_minutes', 400);
    if ($people <= 0) error_response('Invalid people_count', 400);
    if (!in_array($type, ['family','couple','window'], true)) error_response('Invalid table_type', 400);
    if ($table_id <= 0) error_response('Invalid table_id', 400);

    // Ensure user exists
    $q = $conn->prepare("SELECT user_id FROM users WHERE user_id=? LIMIT 1");
    $q->bind_param('i', $user_id);
    $q->execute(); $ux = $q->get_result()->fetch_assoc(); $q->close();
    if (!$ux) error_response('User not found', 400);

    // Ensure table valid
    $q = $conn->prepare("SELECT table_id, capacity, status FROM tables WHERE table_id=? LIMIT 1");
    $q->bind_param('i', $table_id);
    $q->execute(); $tb = $q->get_result()->fetch_assoc(); $q->close();
    if (!$tb) error_response('Table not found', 400);
    if ((string)$tb['status'] !== 'active') error_response('Table not available', 400);
    if ((int)$tb['capacity'] < $people) error_response('People exceed capacity', 400);

    // Time window
    $base = DateTime::createFromFormat('H:i', substr($time,0,5));
    if (!$base) error_response('Invalid time format', 400);
    $end = (clone $base)->modify("+{$dur} minutes");
    $from = $base->format('H:i:s'); $to = $end->format('H:i:s');

    // Overlap on same date/table
    $sqlChk = "SELECT 1
               FROM reservation_tables rt
               JOIN reservations r ON r.reservation_id = rt.reservation_id
               WHERE r.reservation_date = ?
                 AND r.status IN ('pending','confirmed')
                 AND rt.table_id = ?
                 AND (? < rt.to_time AND ? > rt.from_time)
               LIMIT 1";
    $st = $conn->prepare($sqlChk);
    $st->bind_param('siss', $date, $table_id, $from, $to);
    $st->execute(); $has = $st->get_result()->fetch_assoc(); $st->close();
    if ($has) error_response('Table already occupied for this slot', 400);

    // Create reservation (confirmed) + assign table
    $conn->begin_transaction();
    try{
      $status = 'confirmed';
      $ins = $conn->prepare("INSERT INTO reservations
        (user_id, reservation_date, reservation_time, duration_minutes, people_count, table_type, status, special_request)
        VALUES (?,?,?,?,?,?,?,?)");
      $ins->bind_param('issiiiss', $user_id, $date, $time, $dur, $people, $type, $status, $special);
      if (!$ins->execute()) throw new Exception('DB error creating reservation');
      $rid = $ins->insert_id; $ins->close();

      $ins2 = $conn->prepare("INSERT INTO reservation_tables (reservation_id, table_id, from_time, to_time) VALUES (?,?,?,?)");
      $ins2->bind_param('iiss', $rid, $table_id, $from, $to);
      if (!$ins2->execute()) throw new Exception('DB error assigning table');
      $ins2->close();

      $conn->commit();
      respond(['message'=>'Reservation created','reservation'=>[
        'reservation_id'=>$rid,'status'=>$status,'table'=>['table_id'=>$table_id]
      ]], 201);
    }catch(Throwable $e){
      $conn->rollback();
      error_response($e->getMessage(), 500);
    }
  }

  // GET ?r=reservations&a=list&limit=100&page=1
  public static function list(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);
    [$page,$limit,$offset] = validate_pagination($_GET['page'] ?? 1, $_GET['limit'] ?? 100, 200);
    $sql = "SELECT r.reservation_id,r.user_id,u.name AS customer_name,
                   r.reservation_date,r.reservation_time,r.duration_minutes,
                   r.people_count,r.table_type,r.status,r.created_at
            FROM reservations r JOIN users u ON u.user_id = r.user_id
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?";
    $st = $conn->prepare($sql); $st->bind_param('ii', $limit, $offset);
    $st->execute(); $rs = $st->get_result();
    $items=[]; while($r=$rs->fetch_assoc()){
      $items[]=[
        'reservation_id'=>(int)$r['reservation_id'],'user_id'=>(int)$r['user_id'],
        'customer_name'=>$r['customer_name'],'reservation_date'=>$r['reservation_date'],
        'reservation_time'=>$r['reservation_time'],'duration_minutes'=>(int)$r['duration_minutes'],
        'people_count'=>(int)$r['people_count'],'table_type'=>$r['table_type'],
        'status'=>$r['status'],'created_at'=>$r['created_at'],
      ];
    }
    $st->close(); respond(['page'=>$page,'limit'=>$limit,'count'=>count($items),'items'=>$items]);
  }

  // GET ?r=reservations&a=details&reservation_id=123
  public static function details(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);
    $rid = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : 0;
    if ($rid <= 0) error_response('Invalid reservation_id', 400);

    $st = $conn->prepare("SELECT r.reservation_id,r.user_id,u.name AS customer_name,u.email,
                                 r.reservation_date,r.reservation_time,r.duration_minutes,
                                 r.people_count,r.table_type,r.status,r.special_request,r.created_at
                          FROM reservations r JOIN users u ON u.user_id=r.user_id
                          WHERE r.reservation_id=? LIMIT 1");
    $st->bind_param('i', $rid);
    $st->execute(); $resv=$st->get_result()->fetch_assoc(); $st->close();
    if (!$resv) error_response('Reservation not found', 404);

    $q = $conn->prepare("SELECT rt.table_id,t.name,t.capacity,t.zone,rt.from_time,rt.to_time
                         FROM reservation_tables rt JOIN tables t ON t.table_id=rt.table_id
                         WHERE rt.reservation_id=?");
    $q->bind_param('i', $rid);
    $q->execute(); $rs=$q->get_result();
    $tables=[]; while($r=$rs->fetch_assoc()){
      $tables[]=[ 'table_id'=>(int)$r['table_id'],'name'=>$r['name'],'capacity'=>(int)$r['capacity'],'zone'=>$r['zone'],'from_time'=>$r['from_time'],'to_time'=>$r['to_time'] ];
    }
    $q->close(); respond(['reservation'=>$resv,'tables'=>$tables]);
  }

  // GET ?r=reservations&a=user_list&actor_user_id=5&limit=100&page=1
  public static function user_list(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);
    $actor = isset($_GET['actor_user_id']) ? (int)$_GET['actor_user_id'] : 0;
    if ($actor <= 0) error_response('actor_user_id required', 400);
    [$page,$limit,$offset] = validate_pagination($_GET['page'] ?? 1, $_GET['limit'] ?? 100, 200);

    $sql = "SELECT reservation_id,reservation_date,reservation_time,duration_minutes,people_count,table_type,status,created_at
            FROM reservations WHERE user_id=? ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $st = $conn->prepare($sql); $st->bind_param('iii', $actor, $limit, $offset);
    $st->execute(); $rs=$st->get_result();
    $items=[]; while($r=$rs->fetch_assoc()){
      $items[]=[
        'reservation_id'=>(int)$r['reservation_id'],'reservation_date'=>$r['reservation_date'],
        'reservation_time'=>$r['reservation_time'],'duration_minutes'=>(int)$r['duration_minutes'],
        'people_count'=>(int)$r['people_count'],'table_type'=>$r['table_type'],
        'status'=>$r['status'],'created_at'=>$r['created_at'],
      ];
    }
    $st->close(); respond(['page'=>$page,'limit'=>$limit,'count'=>count($items),'items'=>$items]);
  }

  // POST ?r=reservations&a=update_status { reservation_id, status }
  public static function update_status(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $d = read_json(); $missing = require_fields($d, ['reservation_id','status']);
    if ($missing) error_response("Missing field: $missing", 400);

    $rid=(int)$d['reservation_id']; $stn=strtolower(s($d['status']));
    if (!in_array($stn, ['pending','confirmed','cancelled'], true)) error_response('Invalid status', 400);
    $st=$conn->prepare("UPDATE reservations SET status=? WHERE reservation_id=?");
    $st->bind_param('si', $stn, $rid); if(!$st->execute()) error_response('DB error while updating status', 500);
    $st->close(); respond(['message'=>'Reservation updated','reservation_id'=>$rid,'status'=>$stn]);
  }
}
