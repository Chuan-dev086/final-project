<?php
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 核心查询：外面只统计人数，不查名字，确保运行速度和界面的整洁
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
            <?php endif; ?>
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
                            <th>Members Count</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($groups)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-white-50 py-4">
                                    <i class="bi bi-folder-x fs-3 d-block mb-2"></i>No groups found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($groups as $row): ?>
                                <tr>
                                    <td><span class="custom-id"><?= $row['id'] ?></span></td>
                                    <td><span class="custom-group-name"><?= $row['group_name'] ?></span></td>
                                    <td>
                                        <!-- 🌟 外面精简：只显示数字 + Members，不放具体名字 -->
                                        <span class="custom-members">
                                            <i class="bi bi-person-fill small me-1"></i><?= $row['dynamic_members_count'] ?> 
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <!-- 点击 View 带着 ID 进到里面看详情 -->
                                        <a href="edit-group.php?id=<?= $row['id'] ?>" class="btn-edit">
                                            <i class="bi bi-pencil-square me-1"></i>View
                                        </a>
                                        <?php if ($_SESSION['role'] === 'Admin'): ?>
                                            <a href="delete-group.php?id=<?= $row['id'] ?>"
                                                onclick="return confirm('Are you sure you want to delete this group?');"
                                                class="btn-delete">
                                                <i class="bi bi-trash3-fill me-1"></i>Delete
                                            </a>
                                        <?php endif; ?>
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