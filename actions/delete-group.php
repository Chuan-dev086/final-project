<?php
// 1. 引入初始化文件（获取 $db 和 session）
require 'header.php';

// 2. 严格的权限校验：只有登录的 Admin 才能执行删除
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

// 3. 获取传递过来的组合 ID
$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $db->beginTransaction(); // 开启事务

        // 1. 删除关联
        $clearStmt = $db->prepare("DELETE FROM idol_group WHERE group_id = :id");
        $clearStmt->execute([':id' => $id]);

        // 2. 删除组合
        $stmt = $db->prepare("DELETE FROM `groups` WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $db->commit(); // 全部成功，提交更改
    } catch (PDOException $e) {
        $db->rollBack(); // 只要有一个出错，全部撤销
    }
}

// 4. 执行完毕后，干净利落地重定向回组合管理页面
header("Location: manage-groups.php");
exit; 

?>