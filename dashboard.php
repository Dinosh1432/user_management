<?php
// dashboard.php
require_once 'functions.php';
require_admin($mysqli);

$stmt = $mysqli->prepare("SELECT id,name,email,role,profile_pic,created_at FROM users ORDER BY id DESC");
$stmt->execute();
$res = $stmt->get_result();
$users = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin Dashboard</title></head>
<body>
<h2>Admin Dashboard</h2>
<p><a href="add_user.php">Add User</a> | <a href="profile.php">My Profile</a> | <a href="logout.php">Logout</a></p>
<table border="1" cellpadding="6">
  <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Profile</th><th>Actions</th></tr>
  <?php foreach($users as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= $u['role'] ?></td>
      <td>
        <?php if ($u['profile_pic']): ?>
          <img src="uploads/<?= htmlspecialchars($u['profile_pic']) ?>" width="50">
        <?php endif; ?>
      </td>
      <td>
        <a href="edit_user.php?id=<?= $u['id'] ?>">Edit</a> |
        <a href="delete_user.php?id=<?= $u['id'] ?>" onclick="return confirm('Delete user?')">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
</body>
</html>
