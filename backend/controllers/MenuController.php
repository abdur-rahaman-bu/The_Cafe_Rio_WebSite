<?php
// backend/controllers/MenuController.php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class MenuController
{
  // GET ?r=menu&a=list&status=available&limit=300&q=&category=
  public static function list(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') error_response('Method not allowed', 405);

    $status   = isset($_GET['status']) ? s($_GET['status']) : null; // available/unavailable
    $q        = isset($_GET['q']) ? s($_GET['q']) : '';
    $category = isset($_GET['category']) ? s($_GET['category']) : '';

    [$page, $limit, $offset] = validate_pagination($_GET['page'] ?? 1, $_GET['limit'] ?? 300, 1000);

    $where=[]; $types=''; $params=[];
    if ($status && in_array($status, ['available','unavailable'], true)) { $where[]='m.status=?'; $types.='s'; $params[]=$status; }
    if ($q!==''){ $where[]='(m.name LIKE CONCAT("%",?,"%") OR m.description LIKE CONCAT("%",?,"%") OR m.category LIKE CONCAT("%",?,"%"))'; $types.='sss'; $params[]=$q; $params[]=$q; $params[]=$q; }
    if ($category!==''){ $where[]='m.category LIKE CONCAT("%",?,"%")'; $types.='s'; $params[]=$category; }
    $whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

    $sql = "SELECT m.item_id,m.name,m.price,m.category,m.description,m.image,m.status,m.created_at
            FROM menu_items m
            $whereSql
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?";
    $types.='ii'; $params[]=$limit; $params[]=$offset;

    $st = $conn->prepare($sql);
    if ($types) $st->bind_param($types, ...$params);
    if (!$st->execute()) error_response('DB error', 500);
    $rs = $st->get_result();

    $items=[]; while($r=$rs->fetch_assoc()){
      $items[]=[
        'item_id'=>(int)$r['item_id'],
        'name'=>$r['name'],
        'price'=>(float)$r['price'],
        'category'=>$r['category'],
        'description'=>$r['description'],
        'image'=>$r['image'],
        'status'=>$r['status'],
        'created_at'=>$r['created_at'],
      ];
    }
    $st->close();
    respond(['page'=>$page,'limit'=>$limit,'count'=>count($items),'items'=>$items]);
  }

  // POST ?r=menu&a=create
  // Body JSON: { actor_user_id, name, price, category?, description?, status? }
  public static function create(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $d = read_json();
    $missing = require_fields($d, ['actor_user_id','name','price']);
    if ($missing) error_response("Missing field: $missing", 400);

    $actor=(int)$d['actor_user_id']; if (!is_admin_user($conn,$actor)) error_response('Unauthorized (admin only)', 401);
    $name=s($d['name']); $price=(float)$d['price'];
    $category=s($d['category'] ?? ''); $description=s($d['description'] ?? '');
    $status=s($d['status'] ?? 'available');
    if ($name==='') error_response('Invalid name', 400);
    if (!($price>=0)) error_response('Invalid price', 400);
    if (!in_array($status, ['available','unavailable'], true)) error_response('Invalid status', 400);

    $st=$conn->prepare("INSERT INTO menu_items (name,price,category,description,image,status) VALUES (?,?,?,?,?,?)");
    $image=''; // upload separately
    $st->bind_param('sdssss', $name,$price,$category,$description,$image,$status);
    if (!$st->execute()) error_response('DB error creating item', 500);
    $id=$st->insert_id; $st->close();
    respond(['message'=>'Created','item_id'=>$id], 201);
  }

  // POST ?r=menu&a=update
  // Body JSON: { actor_user_id, item_id, name?, price?, category?, description?, status? }
  public static function update(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $d = read_json();
    $missing = require_fields($d, ['actor_user_id','item_id']);
    if ($missing) error_response("Missing field: $missing", 400);

    $actor=(int)$d['actor_user_id']; if (!is_admin_user($conn,$actor)) error_response('Unauthorized (admin only)', 401);
    $id=(int)$d['item_id']; if ($id<=0) error_response('Invalid item_id', 400);

    $fields=[]; $types=''; $vals=[];
    if (array_key_exists('name',$d)){ $name=s($d['name']); if ($name==='') error_response('Invalid name',400); $fields[]='name=?'; $types.='s'; $vals[]=$name; }
    if (array_key_exists('price',$d)){ $price=(float)$d['price']; if (!($price>=0)) error_response('Invalid price',400); $fields[]='price=?'; $types.='d'; $vals[]=$price; }
    if (array_key_exists('category',$d)){ $category=s($d['category']); $fields[]='category=?'; $types.='s'; $vals[]=$category; }
    if (array_key_exists('description',$d)){ $desc=s($d['description']); $fields[]='description=?'; $types.='s'; $vals[]=$desc; }
    if (array_key_exists('status',$d)){ $status=s($d['status']); if (!in_array($status,['available','unavailable'],true)) error_response('Invalid status',400); $fields[]='status=?'; $types.='s'; $vals[]=$status; }

    if (!$fields) respond(['message'=>'No changes','item_id'=>$id]);

    $sql="UPDATE menu_items SET ".implode(', ',$fields)." WHERE item_id=?";
    $types.='i'; $vals[]=$id;
    $st=$conn->prepare($sql); if(!$st) error_response('DB prepare error',500);
    $st->bind_param($types, ...$vals);
    if (!$st->execute()) error_response('DB error updating item',500);
    $st->close();
    respond(['message'=>'Updated','item_id'=>$id]);
  }

  // POST multipart ?r=menu&a=upload_image
  // Fields: actor_user_id, item_id, image(file)
  public static function upload_image(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);

    $actor = (int)($_POST['actor_user_id'] ?? 0);
    $id    = (int)($_POST['item_id'] ?? 0);
    if ($actor<=0 || $id<=0) error_response('Invalid actor/item', 400);
    if (!is_admin_user($conn,$actor)) error_response('Unauthorized (admin only)', 401);

    if (empty($_FILES) || !isset($_FILES['image'])) error_response('No file uploaded', 400);

    // allow up to 5 MB, jpg/png/webp
    $MAX = 5 * 1024 * 1024;
    $check = validate_image_upload($_FILES['image'], ['jpg','jpeg','png','webp'], ['image/jpeg','image/png','image/webp'], $MAX);
    if (!($check['ok'] ?? false)) error_response($check['error'] ?? 'Invalid image', 400);

    // Save in web-accessible folder: backend/public/uploads/menu
    $destDir = __DIR__ . '/../public/uploads/menu';
    ensure_dir($destDir);

    // Optional: remove any previous image for this item (keep it simple—only if stored)
    $prev = '';
    if ($st0 = $conn->prepare("SELECT image FROM menu_items WHERE item_id=?")){
      $st0->bind_param('i', $id);
      if ($st0->execute()){
        $rs0 = $st0->get_result();
        if ($r0 = $rs0->fetch_assoc()){ $prev = $r0['image'] ?? ''; }
      }
      $st0->close();
    }
    if ($prev){
      $prevPath = rtrim($destDir,'/\\') . DIRECTORY_SEPARATOR . $prev;
      if (is_file($prevPath)) @unlink($prevPath);
    }

    $fname = safe_filename('menu-'.$id.'-'.time(), $check['ext']);
    $path = rtrim($destDir,'/\\') . DIRECTORY_SEPARATOR . $fname;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $path)) error_response('Failed to save image', 500);

    // Save filename only
    $st=$conn->prepare("UPDATE menu_items SET image=? WHERE item_id=?");
    $img = $fname;
    $st->bind_param('si', $img, $id);
    if (!$st->execute()) error_response('DB error while updating image', 500);
    $st->close();

    respond(['message'=>'Image uploaded','item_id'=>$id,'image'=>$img], 201);
  }

  // POST ?r=menu&a=delete  Body JSON: { actor_user_id, item_id }
  public static function delete(mysqli $conn): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
    $d=read_json(); $missing=require_fields($d,['actor_user_id','item_id']); if($missing) error_response("Missing field: $missing",400);

    $actor=(int)$d['actor_user_id']; if(!is_admin_user($conn,$actor)) error_response('Unauthorized (admin only)',401);
    $id=(int)$d['item_id']; if($id<=0) error_response('Invalid item_id',400);

    $st=$conn->prepare("DELETE FROM menu_items WHERE item_id=?");
    $st->bind_param('i',$id);
    if (!$st->execute()){
      $errno = $conn->errno;
      $st->close();
      if ($errno === 1451) error_response('Cannot delete: item is referenced by existing orders. Mark as unavailable instead.', 409);
      error_response('DB error deleting item', 500);
    }
    $st->close();
    respond(['message'=>'Deleted','item_id'=>$id]);
  }
}
