<?php
// backend/helpers/Validator.php
declare(strict_types=1);

function s($v): string {
  $v = is_string($v) ? $v : (is_scalar($v) ? strval($v) : '');
  return trim($v);
}

function is_positive_int($v): bool {
  return is_int($v) ? $v > 0 : (ctype_digit((string)$v) && (int)$v > 0);
}

function require_fields(array $data, array $names): ?string {
  foreach ($names as $n) {
    if (!array_key_exists($n, $data) || $data[$n] === null || $data[$n] === '') return $n;
  }
  return null;
}

function validate_pagination($page, $limit, int $maxLimit = 200): array {
  $p = max(1, (int)$page);
  $l = max(1, min((int)$limit, $maxLimit));
  $o = ($p - 1) * $l;
  return [$p, $l, $o];
}

function is_admin_user(mysqli $conn, int $user_id): bool {
  if ($user_id <= 0) return false;
  $st = $conn->prepare("SELECT role FROM users WHERE user_id=? LIMIT 1");
  $st->bind_param('i', $user_id);
  $st->execute();
  $r = $st->get_result()->fetch_assoc();
  $st->close();
  return $r && isset($r['role']) && strtolower((string)$r['role']) === 'admin';
}

/* Filesystem helpers used by image upload */
function ensure_dir(string $path): void {
  if (!is_dir($path)) @mkdir($path, 0775, true);
}

function ext_from_mime(string $mime): string {
  static $map = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
  return $map[$mime] ?? '';
}

function safe_filename(string $base, string $ext): string {
  $slug = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower($base));
  $slug = trim($slug ?: 'img', '-');
  $uniq = bin2hex(random_bytes(4));
  return $slug . '-' . date('YmdHis') . '-' . $uniq . '.' . $ext;
}

function validate_image_upload(array $file, array $allowedExt, array $allowedMime, int $maxBytes): array {
  if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) return ['ok'=>false,'error'=>'No file uploaded'];
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) return ['ok'=>false,'error'=>'Upload error code: '.($file['error'] ?? -1)];
  if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxBytes) return ['ok'=>false,'error'=>'File too large or empty'];
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($file['tmp_name']) ?: '';
  if (!in_array($mime, $allowedMime, true)) return ['ok'=>false,'error'=>'Unsupported or undetected file type'];
  $ext = ext_from_mime($mime);
  if (!$ext || !in_array($ext, $allowedExt, true)) return ['ok'=>false,'error'=>'Extension not allowed'];
  return ['ok'=>true,'mime'=>$mime,'ext'=>$ext];
}

/* NEW: canonical validators requested */
function validate_date(string $date, string $format = 'Y-m-d'): bool {
  $dt = DateTime::createFromFormat($format, $date);
  return $dt && $dt->format($format) === $date;
}

function validate_time(string $time, string $format = 'H:i'): bool {
  $dt = DateTime::createFromFormat($format, $time);
  return $dt && $dt->format($format) === $time;
}
