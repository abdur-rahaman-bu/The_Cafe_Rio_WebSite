<?php
// backend/controllers/AuthController.php
declare(strict_types=1);
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class AuthController {
  // POST ?r=auth&a=login  Body: { email, password }
  public static function login(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $data = read_json();
    $missing = require_fields($data, ['email','password']);
    if ($missing) error_response("Missing field: $missing", 400);

    $email = s($data['email']); $pass = (string)$data['password'];

    $st = $conn->prepare("SELECT user_id,name,email,role,password_hash FROM users WHERE email=? LIMIT 1");
    $st->bind_param('s', $email); $st->execute();
    $u = $st->get_result()->fetch_assoc(); $st->close();
    if (!$u) error_response('Invalid credentials', 401);

    $hash = (string)($u['password_hash'] ?? '');
    $ok = $hash ? password_verify($pass, $hash) : ($pass !== '');
    if (!$ok) error_response('Invalid credentials', 401);

    respond(['user'=>[
      'user_id'=>(int)$u['user_id'], 'name'=>$u['name'], 'email'=>$u['email'], 'role'=>$u['role'],
    ]]);
  }

  // POST ?r=auth&a=register  Body: { name, email, password }
  public static function register(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $d = read_json();
    $missing = require_fields($d, ['name','email','password']);
    if ($missing) error_response("Missing field: $missing", 400);

    $name = s($d['name']); $email = s($d['email']); $pass = (string)$d['password'];
    if ($name==='') error_response('Name required', 400);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) error_response('Invalid email', 400);
    if (strlen($pass) < 6) error_response('Password too short (min 6)', 400);

    // unique email
    $q = $conn->prepare("SELECT 1 FROM users WHERE email=? LIMIT 1");
    $q->bind_param('s', $email); $q->execute(); $ex = $q->get_result()->fetch_assoc(); $q->close();
    if ($ex) error_response('Email already in use', 400);

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $role = 'user';
    $st = $conn->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)");
    $st->bind_param('ssss', $name, $email, $hash, $role);
    if (!$st->execute()) error_response('DB error creating user', 500);
    $uid = $st->insert_id; $st->close();

    respond(['message'=>'Registered','user'=>[
      'user_id'=>$uid,'name'=>$name,'email'=>$email,'role'=>$role
    ]], 201);
  }
}
