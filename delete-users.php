<?php
require 'header.php';

// 1. 安全防线一：检查用户是否登录，且角色必须是超级管理员 Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo "<script>alert('Unauthorized Action! Admin access required.'); window.location.href='dashboard.php';</script>";
    exit;
}

// 2. 获取要删除的用户 ID
$id = $_GET['id'] ?? null;

if ($id) {
    // 🌟 安全防线二（防自杀机制）：如果接收到的 ID 和当前登录的 Admin ID 相同，拦截！
    if ($id == $_SESSION['user_id']) {
        echo "<script>alert('Security Error: You cannot delete your own admin account while logged in!'); window.location.href='manage-users.php';</script>";
        exit;
    }

    try {
        // 3. 执行删除操作
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        // 可选：如果删除成功，可以顺便给个提示（或者直接静默刷新）
        echo "<script>alert('User permanently deleted successfully.'); window.location.href='manage-users.php';</script>";
        exit;
        
    } catch (PDOException $e) {
        // 容错处理：比如该用户关联了其他表的数据导致外键约束报错
        echo "<script>alert('Error: Cannot delete this user. They might be linked to other data.'); window.location.href='manage-users.php';</script>";
        exit;
    }
}

// 如果没有传 ID，直接退回列表页
header("Location: manage-users.php");
exit;