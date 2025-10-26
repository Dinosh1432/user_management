<?php
// register.php
require_once 'config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($name === '') $errors[] = "Name required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required.";
    if (strlen($password) < 6) $errors[] = "Password min 6 chars.";
    if ($password !== $password2) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        // check existing
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';
            $insert = $mysqli->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
            $insert->bind_param('ssss', $name, $email, $hash, $role);
            if ($insert->execute()) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $errors[] = "Registration failed.";
            }
            $insert->close();
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="utf-8"><title>Register</title></head>
<body>
    <div class="container">
        <h2>Register</h2>
<?php if (!empty($errors)): ?>
  <ul style="color:red;"><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
<?php endif; ?>
<form method="post" action="">
  <label>Name: <input type="text" name="name" value="<?=htmlspecialchars($_POST['name'] ?? '')?>"></label><br>
  <label>Email: <input type="email" name="email" value="<?=htmlspecialchars($_POST['email'] ?? '')?>"></label><br>
  <label>Password: <input type="password" name="password"></label><br>
  <label>Confirm: <input type="password" name="password2"></label><br>
  <button type="submit">Register</button>
</form>
    </div>

<p><a href="login.php">Login</a></p>
</body>
</html>
