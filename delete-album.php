<?php
// 1. 引入初始化文件（获取 $db 和 session）
require 'header.php';

// 2. 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 🌟 3. 权限校验：只有 Admin 或 Manager 才可以执行删除 CRUD
if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') {
    echo "<script>alert('Unauthorized access!'); window.location.href='manage-albums.php';</script>";
    exit;
}

// 4. 获取传递过来的专辑 ID
$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // 执行删除专辑
        $stmt = $db->prepare("DELETE FROM albums WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        // 如果有数据库关联错误可以在这里捕获
    }
}

// 5. 执行完毕后，干净利落地重定向回专辑管理列表页
header("Location: manage-albums.php");
exit; 

?> 
