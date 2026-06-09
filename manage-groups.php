<?php
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login-form.php');
    exit;
}

$albums_stmt = $db->prepare("SELECT id, name, release_date FROM albums WHERE group_id = :group_id ORDER BY release_date DESC");


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
                            <th class="text-center">Albums</th>
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
                                <?php
                                $albums_stmt->execute([':group_id' => $row['id']]);
                                $group_albums = $albums_stmt->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <tr>
                                    <td><span class="custom-id"><?= $row['id'] ?></span></td>
                                    <td><span class="custom-group-name"><?= $row['group_name'] ?></span></td>
                                    <td>
                                        <span class="custom-members">
                                            <i class="bi bi-person-fill small me-1"></i><?= $row['dynamic_members_count'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="mb-4">
                                            <div class="p-3" >
                                                <?php if (empty($group_albums)): ?>
                                                    <div class="text-white-50 small text-center py-3">
                                                        <i class="bi bi-folder-symlink d-block fs-4 mb-2 opacity-50"></i>
                                                        No albums released yet for this group.
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex flex-column gap-2">
                                                        <?php foreach ($group_albums as $alb): ?>
                                                            <div class="d-flex align-items-center justify-content-between py-2 px-3"
                                                                style="background: rgba(255, 255, 255, 0.03); border-radius: 10px;">

                                                                <div class="d-flex flex-column">
                                                                    <span class="text-white fw-medium" style="font-size: 15px;">
                                                                        <?= $alb['name'] ?>
                                                                    </span>
                                                                    <span class="text-white-50" style="font-size: 12px; font-family: monospace;">
                                                                        <i class="bi bi-calendar3 me-1"></i><?= $alb['release_date'] ?>
                                                                    </span>
                                                                </div>

                                                                <a href="edit-album.php?id=<?= $alb['id'] ?>" class="text-decoration-none small"
                                                                    style="color: #c084fc; transition: opacity 0.2s;" onmouseover="this.style.opacity=0.7" onmouseout="this.style.opacity=1">
                                                                    Detail <i class="bi bi-arrow-right-short fs-5 align-middle"></i>
                                                                </a>

                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
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