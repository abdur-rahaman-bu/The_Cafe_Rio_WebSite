<?php
// backend/controllers/UsersController.php — profile get/update (self)
declare(strict_types=1);
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class UsersController
{
  // GET ?r=users&a=get_profile&actor_user_id=5
  public static function get_profile(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);
    $actor = isset($_GET['actor_user_id']) ? (int)$_GET['actor_user_id'] : 0;
    if ($actor <= 0) error_response('actor_user_id required', 400);

    $st = $conn->prepare("SELECT user_id,name,email,role,created_at,updated_at FROM users WHERE user_id=? LIMIT 1");
    $st->bind_param('i', $actor); $st->execute();
    $u = $st->get_result()->fetch_assoc(); $st->close();
    if (!$u) error_response('User not found', 404);

    respond(['user'=>[
      'user_id'=>(int)$u['user_id'], 'name'=>$u['name'], 'email'=>$u['email'], 'role'=>$u['role'],
      'created_at'=>$u['created_at'], 'updated_at'=>$u['updated_at']
    ]]);
  }

  // POST ?r=users&a=update_profile  Body: { actor_user_id, name?, email?, new_password?, current_password? }
  public static function update_profile(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $d = read_json();
    $missing = require_fields($d, ['actor_user_id']);
    if ($missing) error_response("Missing field: $missing", 400);

    $actor = (int)$d['actor_user_id']; if ($actor<=0) error_response('Invalid actor_user_id', 400);

    // Load current
    $st = $conn->prepare("SELECT user_id,name,email,password_hash FROM users WHERE user_id=? LIMIT 1");
    $st->bind_param('i', $actor); $st->execute(); $u=$st->get_result()->fetch_assoc(); $st->close();
    if (!$u) error_response('User not found', 404);

    $fields=[]; $types=''; $vals=[];
    if (array_key_exists('name',$d)) {
      $name = s((string)$d['name']); if ($name==='') error_response('Name cannot be empty', 400);
      $fields[]='name=?'; $types.='s'; $vals[]=$name;
    }
    if (array_key_exists('email',$d)) {
      $email = s((string)$d['email']); if (!filter_var($email, FILTER_VALIDATE_EMAIL)) error_response('Invalid email', 400);
      // unique check except self
      $q = $conn->prepare("SELECT 1 FROM users WHERE email=? AND user_id<>? LIMIT 1");
      $q->bind_param('si', $email, $actor); $q->execute(); $ex=$q->get_result()->fetch_assoc(); $q->close();
      if ($ex) error_response('Email already in use', 400);
      $fields[]='email=?'; $types.='s'; $vals[]=$email;
    }

    // Password change flow (optional): needs current_password and new_password
    if (array_key_exists('new_password',$d)) {
      $newp = (string)$d['new_password']; if (strlen($newp) < 6) error_response('Password too short (min 6)', 400);
      $curr = (string)($d['current_password'] ?? '');
      $currHash = (string)($u['password_hash'] ?? '');
      $ok = $currHash ? password_verify($curr, $currHash) : ($curr !== '');
      if (!$ok) error_response('Current password incorrect', 400);
      $hash = password_hash($newp, PASSWORD_DEFAULT);
      $fields[]='password_hash=?'; $types.='s'; $vals[]=$hash;
    }

    if (empty($fields)) respond(['message'=>'No changes']);

    $sql = "UPDATE users SET ".implode(', ',$fields)." WHERE user_id=?";
    $types.='i'; $vals[]=$actor;
    $up = $conn->prepare($sql); $up->bind_param($types, ...$vals);
    if (!$up->execute()) error_response('DB error updating profile', 500);
    $up->close();

    respond(['message'=>'Profile updated','user_id'=>$actor]);
  }
}
