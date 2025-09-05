<?php
// backend/controllers/TablesController.php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class TablesController
{
  // GET ?r=tables&a=list&status=active&min_capacity=2&zone=couple
  public static function list(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);

    $status = isset($_GET['status']) ? s($_GET['status']) : null;
    $minCap = isset($_GET['min_capacity']) ? (int)$_GET['min_capacity'] : null;
    $zone   = isset($_GET['zone']) ? s($_GET['zone']) : null;

    $where = [];
    $types = '';
    $params = [];

    if ($status && in_array($status, ['active','inactive'], true)) {
      $where[] = 't.status = ?';
      $types .= 's';
      $params[] = $status;
    }
    if ($minCap && $minCap > 0) {
      $where[] = 't.capacity >= ?';
      $types .= 'i';
      $params[] = $minCap;
    }
    if ($zone) {
      $where[] = 't.zone = ?';
      $types .= 's';
      $params[] = $zone;
    }

    $sql = "SELECT t.table_id, t.name, t.capacity, t.zone, t.status, t.created_at, t.updated_at
            FROM tables t " . ($where ? ('WHERE ' . implode(' AND ', $where)) : '') . "
            ORDER BY t.capacity ASC, t.name ASC";
    $st = $conn->prepare($sql);
    if ($types !== '') $st->bind_param($types, ...$params);
    if (!$st->execute()) error_response('DB error', 500);
    $rs = $st->get_result();

    $rows = [];
    while ($r = $rs->fetch_assoc()) {
      $rows[] = [
        'table_id'   => (int)$r['table_id'],
        'name'       => $r['name'],
        'capacity'   => (int)$r['capacity'],
        'zone'       => $r['zone'],
        'status'     => $r['status'],
        'created_at' => $r['created_at'],
        'updated_at' => $r['updated_at'],
      ];
    }
    $st->close();
    respond(['items' => $rows]);
  }

  // GET ?r=tables&a=availability&date=YYYY-MM-DD&time=HH:MM&duration=90&people=2
  public static function availability(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);

    $date   = s($_GET['date'] ?? '');
    $time   = s($_GET['time'] ?? '');
    $dur    = (int)($_GET['duration'] ?? 90);
    $people = (int)($_GET['people'] ?? 0);

    if (!validate_date($date, 'Y-m-d')) error_response('Invalid date', 400);
    if (!validate_time(substr($time, 0, 5), 'H:i')) error_response('Invalid time', 400);
    if ($dur < 30 || $dur > 240) error_response('duration must be between 30..240 minutes', 400);

    $dt = DateTime::createFromFormat('H:i', substr($time, 0, 5));
    if (!$dt) error_response('Invalid time format', 400);
    $dtEnd = clone $dt;
    $dtEnd->modify("+{$dur} minutes");
    $from = $dt->format('H:i:s');
    $to   = $dtEnd->format('H:i:s');

    $where = "t.status='active'";
    $types = '';
    $params = [];
    if ($people > 0) {
      $where .= " AND t.capacity >= ?";
      $types = 'i';
      $params = [$people];
    }

    $sql = "
      SELECT
        t.table_id, t.name, t.capacity, t.zone,
        EXISTS(
          SELECT 1
          FROM reservation_tables rt
          JOIN reservations r ON r.reservation_id = rt.reservation_id
          WHERE r.reservation_date = ?
            AND r.status IN ('pending','confirmed')
            AND rt.table_id = t.table_id
            AND (? < rt.to_time AND ? > rt.from_time)
        ) AS is_occupied
      FROM tables t
      WHERE $where
      ORDER BY t.capacity ASC, t.name ASC
    ";
    $types2  = $types ? ('sss' . $types) : 'sss';
    $params2 = array_merge([$date, $from, $to], $params);

    $st = $conn->prepare($sql);
    $st->bind_param($types2, ...$params2);
    if (!$st->execute()) error_response('DB error while computing availability', 500);
    $rs = $st->get_result();

    $available = [];
    $occupied  = [];
    while ($r = $rs->fetch_assoc()) {
      $row = [
        'table_id' => (int)$r['table_id'],
        'name'     => $r['name'],
        'capacity' => (int)$r['capacity'],
        'zone'     => $r['zone'],
      ];
      if ((int)$r['is_occupied'] === 1) $occupied[] = $row; else $available[] = $row;
    }
    $st->close();

    respond([
      'query'     => ['date' => $date, 'from' => $from, 'to' => $to, 'duration' => $dur, 'people' => $people],
      'available' => $available,
      'occupied'  => $occupied
    ]);
  }

  // POST ?r=tables&a=create { actor_user_id, name, capacity, zone?, status? }
  public static function create(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $data = read_json();
    $missing = require_fields($data, ['actor_user_id','name','capacity']);
    if ($missing) error_response("Missing field: $missing", 400);

    $actor = (int)$data['actor_user_id'];
    if (!is_admin_user($conn, $actor)) error_response('Admin authorization required', 403);

    $name   = s((string)$data['name']);
    $cap    = (int)$data['capacity'];
    $zone   = s($data['zone'] ?? '');
    $status = s($data['status'] ?? 'active');

    if ($name === '' || $cap <= 0) error_response('Invalid name/capacity', 400);
    if (!in_array($status, ['active','inactive'], true)) $status = 'active';

    $st = $conn->prepare("INSERT INTO tables (name, capacity, zone, status) VALUES (?,?,?,?)");
    $st->bind_param('siss', $name, $cap, $zone, $status);
    if (!$st->execute()) error_response('DB error while creating table', 500);
    $id = $st->insert_id;
    $st->close();
    respond(['message' => 'Table created', 'table_id' => $id], 201);
  }

  // POST ?r=tables&a=update { actor_user_id, table_id, ...fields }
  public static function update(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $data = read_json();
    $missing = require_fields($data, ['actor_user_id','table_id']);
    if ($missing) error_response("Missing field: $missing", 400);

    $actor = (int)$data['actor_user_id'];
    if (!is_admin_user($conn, $actor)) error_response('Admin authorization required', 403);

    $id = (int)$data['table_id'];
    if ($id <= 0) error_response('Invalid table_id', 400);

    $fields = [];
    $types  = '';
    $vals   = [];

    if (array_key_exists('name', $data)) {
      $name = s((string)$data['name']);
      if ($name === '') error_response('Name cannot be empty', 400);
      $fields[] = 'name=?'; $types .= 's'; $vals[] = $name;
    }
    if (array_key_exists('capacity', $data)) {
      $cap = (int)$data['capacity'];
      if ($cap <= 0) error_response('Capacity must be > 0', 400);
      $fields[] = 'capacity=?'; $types .= 'i'; $vals[] = $cap;
    }
    if (array_key_exists('zone', $data)) {
      $zone = s((string)$data['zone']);
      $fields[] = 'zone=?'; $types .= 's'; $vals[] = $zone;
    }
    if (array_key_exists('status', $data)) {
      $status = s((string)$data['status']);
      if (!in_array($status, ['active','inactive'], true)) error_response('Invalid status', 400);
      $fields[] = 'status=?'; $types .= 's'; $vals[] = $status;
    }

    if (empty($fields)) respond(['message' => 'No changes', 'table_id' => $id]);

    $sql = "UPDATE tables SET " . implode(', ', $fields) . " WHERE table_id=?";
    $types .= 'i'; $vals[] = $id;

    $st = $conn->prepare($sql);
    if (!$st) error_response('DB prepare error', 500);
    $st->bind_param($types, ...$vals);
    if (!$st->execute()) error_response('DB error while updating table', 500);
    $st->close();
    respond(['message' => 'Table updated', 'table_id' => $id]);
  }

  // POST ?r=tables&a=delete { actor_user_id, table_id }
  public static function delete(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $data = read_json();
    $missing = require_fields($data, ['actor_user_id','table_id']);
    if ($missing) error_response("Missing field: $missing", 400);

    $actor = (int)$data['actor_user_id'];
    if (!is_admin_user($conn, $actor)) error_response('Admin authorization required', 403);

    $id = (int)$data['table_id'];
    if ($id <= 0) error_response('Invalid table_id', 400);

    $st = $conn->prepare("DELETE FROM tables WHERE table_id=?");
    $st->bind_param('i', $id);
    if (!$st->execute()) error_response('DB error while deleting table', 500);
    $st->close();
    respond(['message' => 'Table deleted', 'table_id' => $id]);
  }
}
