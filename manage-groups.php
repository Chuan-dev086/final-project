<?php
require 'header.php';

// 1. 登录验证
if (!isset($_SESSION['user_id'])) {
    header('Location: login-form.php');
    exit;
}

// 预处理查询单组专辑的 SQL
$albums_stmt = $db->prepare("SELECT id, name, release_date FROM albums WHERE group_id = :group_id ORDER BY release_date DESC");

// 核心查询：高效统计各组的人数
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
        <!-- 顶部导航区 -->
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

        <!-- 主玻璃卡片面板 -->
        <div class="glass-panel">
            <div class="mb-4">
                <h2 class="gradient-title mb-1"><i class="bi bi-people-fill me-2"></i>Groups List</h2>
            </div>

            <!-- 响应式水平保护盒：现在它会完美应对 min-width，在窗口过窄时提供安全的横向滚动条 -->
            <div class="table-responsive">
                <table class="table table-glass align-middle mb-0">
                    <thead>
                        <tr>
                            <!-- 通过升级后的 CSS 类名进行牢固的最小宽度防御 -->
                            <th class="col-id">ID</th>
                            <th class="col-group-name">Group Name</th>
                            <th class="col-members">Members Count</th>
                            <th class="col-albums">Albums</th>
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
                                <?php
                                $albums_stmt->execute([':group_id' => $row['id']]);
                                $group_albums = $albums_stmt->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <tr>
                                    <td><span class="custom-id"><?= $row['id'] ?></span></td>
                                    <td><span class="custom-group-name fw-semibold"><?= $row['group_name'] ?></span></td>
                                    <td>
                                        <span class="custom-members">
                                            <i class="bi bi-person-fill small me-1"></i><?= $row['dynamic_members_count'] ?>
                                        </span>
                                    </td>

                                    <!-- 已发行专辑列 -->
                                    <td>
                                        <?php if (empty($group_albums)): ?>
                                            <div class="text-white-50 small opacity-50 py-1">
                                                <i class="bi bi-folder-symlink me-1"></i>No albums released
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex flex-column gap-1 album-scroll-container">
                                                <?php foreach ($group_albums as $alb): ?>

                                                    <div class="d-flex align-items-center justify-content-between py-1 px-3 album-item-row">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="text-white small fw-medium text-truncate album-title-truncate" title="<?= $alb['name'] ?>">
                                                                <?= $alb['name'] ?>
                                                            </span>
                                                            <span class="text-white-50 opacity-70 album-year-badge">
                                                                (<?= date('Y', strtotime($alb['release_date'])) ?>)
                                                            </span>
                                                        </div>
                                                        <a href="edit-album.php?id=<?= $alb['id'] ?>" class="text-decoration-none album-detail-link">
                                                            Detail <i class="bi bi-arrow-right-short fs-6 align-middle"></i>
                                                        </a>
                                                    </div>

                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- 操作按钮列：保持文字牢固显示，绝不缩水隐藏 -->
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">
                                            <?php if ($_SESSION['role'] === 'Admin'): ?>
                                                <a href="edit-group.php?id=<?= $row['id'] ?>" class="btn-edit">
                                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                                </a>
                                            <?php else: ?>
                                                <a href="edit-group.php?id=<?= $row['id'] ?>" class="btn-edit">
                                                    <i class="bi bi-eye-fill me-1"></i>View
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($_SESSION['role'] === 'Admin'): ?>
                                                <a href="delete-group.php?id=<?= $row['id'] ?>"
                                                    onclick="return confirm('Are you sure you want to delete this group?');"
                                                    class="btn-delete">
                                                    <i class="bi bi-trash3-fill me-1"></i>Delete
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