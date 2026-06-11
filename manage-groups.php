<?php
require 'header.php';

// 1. 登录验证
if (!isset($_SESSION['user_id'])) {
    header('Location: login-form.php');
    exit;
}

// 安全转义快捷函数
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// 权限安全检查
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';

// 2. 核心大查询：直接统计成员数和专辑数
$query = "SELECT 
            g.id, 
            g.group_name, 
            (SELECT COUNT(ig.idol_id) FROM idol_group ig WHERE ig.group_id = g.id) AS dynamic_members_count,
            COUNT(a.id) AS albums_count
          FROM `groups` g
          LEFT JOIN albums a ON g.id = a.group_id
          GROUP BY g.id
          ORDER BY g.id ASC";

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
            <?php if ($is_admin): ?>
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
                            <th class="col-id">ID</th>
                            <th class="col-group-name">Group Name</th>
                            <th class="col-members">Members Count</th>
                            <th class="col-albums">Albums Count</th>
                            <th class="text-center col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($groups)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-white-50 py-5">
                                    <i class="bi bi-folder-x fs-3 d-block mb-2"></i>No groups found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($groups as $row): ?>
                                <tr>
                                    <td><span class="custom-id"><?= h($row['id']) ?></span></td>
                                    <td><span class="custom-group-name fw-semibold"><?= h($row['group_name']) ?></span></td>
                                    <td>
                                        <span class="custom-members">
                                            <i class="bi bi-person-fill small me-1"></i><?= h($row['dynamic_members_count']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="custom-albums">
                                            <i class="bi bi-journal-album small me-1"></i><?= h($row['albums_count']) ?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">
                                            <?php if ($is_admin): ?>
                                                <a href="edit-group.php?id=<?= urlencode($row['id']) ?>" class="btn-edit">
                                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                                </a>
                                                <a href="delete-group.php?id=<?= urlencode($row['id']) ?>"
                                                    onclick="return confirm('Are you sure you want to delete this group?');"
                                                    class="btn-delete">
                                                    <i class="bi bi-trash3-fill me-1"></i>Delete
                                                </a>
                                            <?php else: ?>
                                                <a href="edit-group.php?id=<?= urlencode($row['id']) ?>" class="btn-edit">
                                                    <i class="bi bi-eye-fill me-1"></i>View
                                                </a>
                                            <?php endif; ?>
                                        </div>
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