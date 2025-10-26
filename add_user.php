<?php
// add_user.php
require_once 'functions.php';
require_admin($mysqli);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';

    if ($name === '') $errors[] = "Name required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required.";
    if (strlen($password) < 6) $errors[] = "Password min 6.";

    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $mysqli->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
            $ins->bind_param('ssss', $name, $email, $hash, $role);
            if ($ins->execute()) {
                header("Location: dashboard.php");
                exit;
            } else {
                $errors[] = "Insert failed.";
            }
            $ins->close();
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Add User</title></head><body>
<h2>Add User</h2>
<?php if (!empty($errors)) foreach ($errors as $e) echo "<p style='color:red'>$e</p>"; ?>
<form method="post" action="">
  <label>Name: <input type="text" name="name" value="<?=htmlspecialchars($_POST['name'] ?? '')?>"></label><br>
  <label>Email: <input type="email" name="email" value="<?=htmlspecialchars($_POST['email'] ?? '')?>"></label><br>
  <label>Password: <input type="password" name="password"></label><br>
  <label>Role:
    <select name="role">
      <option value="user">User</option>
      <option value="admin">Admin</option>
    </select>
  </label><br>
  <button type="submit">Add</button>
</form>
<p><a href="dashboard.php">Back</a></p>
</body></html>
