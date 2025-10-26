<?php
// profile.php
require_once 'functions.php';
require_login();
$user = current_user($mysqli);
$errors = [];
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // two forms possible: update info or change password
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') $errors[] = "Name required.";

        // handle file
        if (!empty($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = validate_upload($_FILES['profile_pic']);
            if ($uploadResult['ok']) {
                $dest = __DIR__ . '/uploads/' . $uploadResult['filename'];
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest)) {
                    // remove old
                    if ($user['profile_pic'] && file_exists(__DIR__.'/uploads/'.$user['profile_pic'])) {
                        @unlink(__DIR__.'/uploads/'.$user['profile_pic']);
                    }
                    $stmt = $mysqli->prepare("UPDATE users SET name=?, profile_pic=? WHERE id=?");
                    $stmt->bind_param('ssi', $name, $uploadResult['filename'], $user['id']);
                    if ($stmt->execute()) {
                        $messages[] = "Profile updated.";
                        $_SESSION['user_id'] = $user['id']; // keep session
                        $user = current_user($mysqli);
                    } else {
                        $errors[] = "Update failed.";
                    }
                    $stmt->close();
                } else {
                    $errors[] = "Failed to move uploaded file.";
                }
            } else {
                $errors[] = $uploadResult['error'];
            }
        } else {
            // update only name
            $stmt = $mysqli->prepare("UPDATE users SET name=? WHERE id=?");
            $stmt->bind_param('si', $name, $user['id']);
            if ($stmt->execute()) {
                $messages[] = "Profile name updated.";
                $user = current_user($mysqli);
            } else {
                $errors[] = "Update failed.";
            }
            $stmt->close();
        }

    } elseif (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (strlen($new) < 6) $errors[] = "New password min 6.";
        if ($new !== $confirm) $errors[] = "New passwords do not match.";

        // verify current password
        $stmt = $mysqli->prepare("SELECT password FROM users WHERE id=? LIMIT 1");
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if (!password_verify($current, $row['password'])) $errors[] = "Current password incorrect.";

        if (empty($errors)) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $upd = $mysqli->prepare("UPDATE users SET password=? WHERE id=?");
            $upd->bind_param('si', $hash, $user['id']);
            if ($upd->execute()) $messages[] = "Password changed.";
            else $errors[] = "Password change failed.";
            $upd->close();
        }
    }
}
?>
<!doctype html><html><head>
    <link rel="stylesheet" href="style.css">

    <meta charset="utf-8"><title>Profile</title></head><body>


<?php foreach($messages as $m) echo "<p style='color:green'>$m</p>"; ?>
<?php foreach($errors as $e) echo "<p style='color:red'>$e</p>"; ?>
<div class="container">
    <h3>Info</h3>
<form method="post" enctype="multipart/form-data">
  <label>Name: <input type="text" name="name" value="<?=htmlspecialchars($user['name'])?>"></label><br>
  <label>Profile picture: <input type="file" name="profile_pic"></label><br>
  <?php if ($user['profile_pic']): ?><img src="uploads/<?=htmlspecialchars($user['profile_pic'])?>" width="100"><br><?php endif; ?>
  <button type="submit" name="update_profile">Update Profile</button>
</form>

<h3>Change Password</h3>
<form method="post">
  <label>Current: <input type="password" name="current_password"></label><br>
  <label>New: <input type="password" name="new_password"></label><br>
  <label>Confirm: <input type="password" name="confirm_password"></label><br>
  <button type="submit" name="change_password">Change Password</button>
</form>
</div>

</body></html>
