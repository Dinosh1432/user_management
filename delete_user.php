<?php
// delete_user.php
require_once 'functions.php';
require_admin($mysqli);

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: dashboard.php"); exit; }

// get user to delete
$stmt = $mysqli->prepare("SELECT profile_pic FROM users WHERE id=? LIMIT 1");
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
$u = $res->fetch_assoc();
$stmt->close();

$del = $mysqli->prepare("DELETE FROM users WHERE id=?");
$del->bind_param('i',$id);
if ($del->execute()) {
    if (!empty($u['profile_pic']) && file_exists(__DIR__.'/uploads/'.$u['profile_pic'])) {
        @unlink(__DIR__.'/uploads/'.$u['profile_pic']);
    }
}
header("Location: dashboard.php");
exit;
