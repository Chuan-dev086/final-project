<?php
require 'header.php'; // 引入已有的 session_start() 和 $db 连接

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = $_SESSION['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (!empty($username) && !empty($email)) {

        $query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $db->prepare($query);

        if ($stmt->execute([$username, $email, $user_id])) {

            // 【核心修改】成功更新数据库后，同步更新 Session 里的用户名
            $_SESSION['username'] = $username;

            // 更新成功，直接跳转回仪表盘
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Database update failed.";
        }
    } else {
        echo "Please fill in all fields.";
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?> 