<?php
// functions.php
require_once 'config.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user($mysqli) {
    if (!is_logged_in()) return null;
    $id = (int)$_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT id,name,email,role,profile_pic FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();
    return $user;
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function require_admin($mysqli) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    $user = current_user($mysqli);
    if (!$user || $user['role'] !== 'admin') {
        echo "Access denied.";
        exit;
    }
}

function validate_upload($file) {
    $allowed_types = ['image/jpeg','image/png','image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if ($file['error'] === UPLOAD_ERR_NO_FILE) return ['ok'=>true, 'filename'=>null];
    if ($file['error'] !== UPLOAD_ERR_OK) return ['ok'=>false, 'error'=>'File upload error.'];

    if (!in_array(mime_content_type($file['tmp_name']), $allowed_types)) {
        return ['ok'=>false, 'error'=>'Invalid file type. Only JPG/PNG/GIF allowed.'];
    }

    if ($file['size'] > $max_size) {
        return ['ok'=>false, 'error'=>'File too large. Max 2MB.'];
    }

    // generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('p_', true) . '.' . $ext;
    return ['ok'=>true, 'filename'=>$filename];
}
