<?php
// login.php
require_once 'config.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required.";

    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id,password FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($user = $res->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // login
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                header("Location: profile.php");
                exit;
            } else {
                $errors[] = "Invalid credentials.";
            }
        } else {
            $errors[] = "Invalid credentials.";
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
    <link rel="stylesheet" href="style.css">

    <meta charset="utf-8"><title>Login</title></head>
<body>
    <div class="container">
        <h2>Login</h2>
<?php if (!empty($_GET['registered'])): ?><p style="color:green">Registration successful. Login below.</p><?php endif; ?>
<?php if (!empty($errors)): ?>
  <ul style="color:red;"><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
<?php endif; ?>
<form method="post" action="">
  <label>Email: <input type="email" name="email" value="<?=htmlspecialchars($_POST['email'] ?? '')?>"></label><br>
  <label>Password: <input type="password" name="password"></label><br>
  <button type="submit">Login</button>
</form>
    </div>

<p><a href="register.php">Register</a></p>
</body>
</html>
