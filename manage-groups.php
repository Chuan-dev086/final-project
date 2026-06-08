<?php
// 1. 引入初始化文件（包含 session_start() 和数据库 $db 连接）
require 'header.php';

// 2. 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 3. 处理删除逻辑
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // 先断开中间表 idol_group 的关联记录，防止外键约束报错
    $clearRelQuery = "DELETE FROM idol_group WHERE group_id = :id";
    $clearStmt = $db->prepare($clearRelQuery);
    $clearStmt->execute([':id' => $delete_id]);

    // 再删除组合本身
    $deleteQuery = "DELETE FROM `groups` WHERE id = :id";
    $stmt = $db->prepare($deleteQuery);
    $stmt->execute([':id' => $delete_id]);

    header("Location: manage-groups.php");
    exit;
}

// 4. 核心查询：只查 id 和 group_name，并动态统计人数（已剔除不存在的 company 字段）
$query = "SELECT g.id, g.group_name, COUNT(ig.idol_id) AS dynamic_members_count 
          FROM `groups` g 
          LEFT JOIN idol_group ig ON g.id = ig.group_id 
          GROUP BY g.id 
          ORDER BY g.id ASC ";
$stmt = $db->prepare($query);
$stmt->execute();
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Groups - KPOP HUB</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="manage-groups.css">
</head>

<body>
    <div class="container my-4">

        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <a href="dashboard.php" class="btn-action-back small">
                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
            </a>
            <?php if ($_SESSION['role'] === 'Admin'): ?>
            <a href="add-group.php" class="btn btn-add-group small">
                <i class="bi bi-plus-circle me-1"></i>Add New Group
            </a>
            <?php endif;?>
        </div>

        <div class="glass-panel">
            <div class="mb-4">
                <h2 class="gradient-title mb-1"><i class="bi bi-people-fill me-2"></i>Groups List</h2>
            </div>

            <div class="table-responsive">
                <table class="table table-glass align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Group Name</th>
                            <th>Members</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($groups)): ?>
                            <tr>
                                <!-- colspan  -->
                                <td colspan="4" class="text-center text-white-50 py-4">
                                    <i class="bi bi-folder-x fs-3 d-block mb-2"></i>No groups found. Click "Add New Group" to create one.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($groups as $row): ?>
                                <tr>
                                    <td><span class="text-white-50"><?= $row['id'] ?></span></td>
                                    <td>
                                        <strong class="text-primary"><?= $row['group_name'] ?></strong>
                                    </td>
                                    <td>
                                        <i class="bi bi-person-fill text-purple-300 small me-1"></i><?= $row['dynamic_members_count'] ?>
                                    </td>
                            
                                    <td class="text-center">
                                        <a href="edit-group.php?id=<?= $row['id'] ?>" class="btn-edit">
                                            <i class="bi bi-pencil-square me-1"></i>View 
                                        </a>
                                        <?php if($_SESSION['role'] === 'Admin'):?>
                                        <a href="manage-groups.php?delete_id=<?= $row['id'] ?>"
                                            onclick="return confirm('Are you sure you want to delete this group? (Idols inside will become Soloists)');"
                                            class="btn-delete">
                                            <i class="bi bi-trash3-fill me-1"></i>Delete
                                        </a>
                                        <?php endif;?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>