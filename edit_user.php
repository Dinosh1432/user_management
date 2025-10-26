<?php
// edit_user.php
require_once 'functions.php';
require_admin($mysqli);

$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo "Invalid id"; exit; }

$stmt = $mysqli->prepare("SELECT id,name,email,role,profile_pic FROM users WHERE id=? LIMIT 1");
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();
if (!$user) { echo "User not found"; exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';

    if ($name==='') $errors[] = "Name required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required.";

    // handle file
    $uploadResult = ['ok'=>true,'filename'=>null];
    if (!empty($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = validate_upload($_FILES['profile_pic']);
        if ($uploadResult['ok']) {
            $dest = __DIR__ . '/uploads/' . $uploadResult['filename'];
            if (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest)) {
                $uploadResult = ['ok'=>false,'error'=>'Failed to move uploaded file.'];
            }
        }
        if (!$uploadResult['ok']) $errors[] = $uploadResult['error'];
    }

    if (empty($errors)) {
        // if email changed, ensure uniqueness
        $stmt2 = $mysqli->prepare("SELECT id FROM users WHERE email=? AND id<>? LIMIT 1");
        $stmt2->bind_param('si',$email,$id);
        $stmt2->execute();
        $stmt2->store_result();
        if ($stmt2->num_rows > 0) {
            $errors[] = "Email already used by another user.";
        } else {
            $profile_pic_to_set = $user['profile_pic'];
            if (!empty($uploadResult['filename'])) {
                // delete old pic if exists
                if ($user['profile_pic'] && file_exists(__DIR__.'/uploads/'.$user['profile_pic'])) {
                    @unlink(__DIR__.'/uploads/'.$user['profile_pic']);
                }
                $profile_pic_to_set = $uploadResult['filename'];
            }

            $upd = $mysqli->prepare("UPDATE users SET name=?, email=?, role=?, profile_pic=? WHERE id=?");
            $upd->bind_param('ssssi', $name, $email, $role, $profile_pic_to_set, $id);
            if ($upd->execute()) {
                header("Location: dashboard.php");
                exit;
            } else {
                $errors[] = "Update failed.";
            }
            $upd->close();
        }
        $stmt2->close();
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Edit User</title></head><body>
<h2>Edit User</h2>
<?php foreach($errors as $e) echo "<p style='color:red'>$e</p>"; ?>
<form method="post" enctype="multipart/form-data" action="">
  <label>Name: <input type="text" name="name" value="<?=htmlspecialchars($user['name'])?>"></label><br>
  <label>Email: <input type="email" name="email" value="<?=htmlspecialchars($user['email'])?>"></label><br>
  <label>Role:
    <select name="role">
      <option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
      <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
    </select>
  </label><br>
  <label>Profile picture: <input type="file" name="profile_pic"></label><br>
  <?php if ($user['profile_pic']): ?>
    <img src="uploads/<?=htmlspecialchars($user['profile_pic'])?>" width="80"><br>
  <?php endif; ?>
  <button type="submit">Save</button>
</form>
<p><a href="dashboard.php">Back</a></p>
</body></html>
