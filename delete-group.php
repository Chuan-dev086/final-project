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
        // 先断开中间表 idol_group 的关联记录，防止外键约束报错
        $clearRelQuery = "DELETE FROM idol_group WHERE group_id = :id";
        $clearStmt = $db->prepare($clearRelQuery);
        $clearStmt->execute([':id' => $id]);

        // 再删除组合本身
        $deleteQuery = "DELETE FROM `groups` WHERE id = :id";
        $stmt = $db->prepare($deleteQuery);
        $stmt->execute([':id' => $id]);
        
    } catch (PDOException $e) {
        // 如果发生数据库错误，可以在这里处理，或者直接跳回
    }
}

// 4. 执行完毕后，干净利落地重定向回组合管理页面
header("Location: manage-groups.php");
exit; 

?>