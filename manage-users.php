<?php
require 'header.php';

// 1. 严格权限校验：只有 Admin 才能管理用户
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo "<script>alert('Access Denied! Admin only.'); window.location.href='dashboard.php';</script>";
    exit;
}

// 2. 查询所有用户列表
$query = "SELECT id, username, email, role FROM users ORDER BY id ASC";
$users = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - KPOP HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- 引用专辑管理相同的极光毛玻璃底色 -->
    <link rel="stylesheet" href="manage-users.css">
</head>

<body>
    <div class="container my-4">
        <!-- 🌟 优化：移除了 add-user 按钮，只保留纯粹的返回面板 -->
        <div class="mb-4 px-2">
            <a href="dashboard.php" class="btn-action-back small">
                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>

        <div class="glass-panel">
            <div class="mb-4">
                <h2 class="gradient-title mb-1"><i class="bi bi-shield-lock-fill me-2"></i>User Management</h2>
            </div>

            <div class="table-responsive">
                <table class="table table-glass align-middle mb-0">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Email Address</th>
                            <th>System Role</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-white-50 py-4">
                                    <i class="bi bi-people fs-3 d-block mb-2"></i>No users found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $row): ?>
                                <tr>
                                    <td><span class="text-white-50 font-monospace"><?= $row['id'] ?></span></td>
                                    <td><span class="custom-album-name"><i class="bi bi-person-circle small me-2"></i><?= $row['username'] ?></span></td>
                                    <td><span class="custom-date-row"><?= $row['email'] ?></span></td>
                                    <td>
                                        <?php switch ($row['role']):
                                            case 'Admin': ?>
                                                <span class="role-badge badge-admin">
                                                    <i class="bi bi-shield-fill-check me-1"></i>Admin
                                                </span>
                                                <?php break; ?> // 💡 记得加上 break 防止代码击穿

                                            <?php
                                            case 'Manager': ?>
                                                <span class="role-badge badge-manager">
                                                    <i class="bi bi-person-workspace me-1"></i>Manager
                                                </span>
                                                <?php break; ?>

                                            <?php
                                            default: // 💡 相当于 else，也就是普通的 User 角色
                                            ?>
                                                <span class="role-badge badge-user">
                                                    <i class="bi bi-person-fill me-1"></i>User
                                                </span>
                                        <?php
                                                break;
                                        endswitch;
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="edit-user.php?id=<?= $row['id'] ?>" class="btn-edit">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </a>

                                        <!-- 防自杀机制：如果是当前登录的自己，不允许删除 -->
                                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                            <a href="delete-user.php?id=<?= $row['id'] ?>"
                                                onclick="return confirm('WARNING: Are you sure you want to permanently delete this user?');"
                                                class="btn-delete">
                                                <i class="bi bi-trash3-fill me-1"></i>Delete
                                            </a>
                                        <?php else: ?>
                                            <span class="text-white-50 small italic">(You)</span>
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