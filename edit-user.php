<?php
require 'header.php';

// 1. 严格权限校验：只有 Admin 才能进入并编辑用户资料
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: dashboard.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: manage-users.php');
    exit;
}

// 2. 获取该用户的当前完整信息
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<script>alert('User not found!'); window.location.href='manage-users.php';</script>";
    exit;
}

$error = '';
$success = '';

// 3. 处理表单修改提交逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'User';
    $new_password = $_POST['new_password'] ?? ''; // 接收可能输入的新密码

    if (empty($username) || empty($email)) {
        $error = 'Username and Email cannot be empty!';
    } else {
        // 🌟 核心判断：如果新密码框不是空的，说明管理员想要重置该用户的密码
        if (!empty($new_password)) {
            // 使用 BCRYPT 安全哈希算法加密新密码
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // 更新包含密码在内的所有字段
            $updateQuery = "UPDATE users SET username = :username, email = :email, role = :role, password = :password WHERE id = :id";
            $updateParams = [
                ':username' => $username,
                ':email' => $email,
                ':role' => $role,
                ':password' => $hashed_password,
                ':id' => $id
            ];
        } else {
            // 如果新密码框是空的，说明不修改密码，SQL 中不触碰 password 字段
            $updateQuery = "UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id";
            $updateParams = [
                ':username' => $username,
                ':email' => $email,
                ':role' => $role,
                ':id' => $id
            ];
        }

        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute($updateParams);

        // 修改成功后，直接安全跳转回用户管理主页
        header('Location: manage-users.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Profile - KPOP HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="add-group.css">
</head>

<body>
    <div class="form-container" style="max-width: 520px;">

        <h2 class="form-title" style="background: linear-gradient(to right, #ff6b6b, #ff8e53); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="bi bi-person-gear me-2"></i>Edit User Profile
        </h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="border-radius: 12px; background-color: #ef444422; color: #f87171; border: 1px solid #ef444444;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>

        <form action="edit-user.php?id=<?= $id ?>" method="POST">

            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                    value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>

            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="mb-4">
                <label for="new_password" class="form-label">Reset Password (Optional)</label>
                <input type="password" class="form-control" id="new_password" name="new_password"
                    placeholder="Leave blank to keep current password">
                <div class="form-text text-white-50 small mt-1">
                    <i class="bi bi-info-circle me-1"></i>Only type here if the user wants to change/reset their password.
                </div>
            </div>

            <div class="mb-4">
                <label for="role" class="form-label">System Role Permission</label>

                <select class="form-control form-select" id="role" name="role" <?= ($user['id'] == $_SESSION['user_id']) ? 'disabled' : '' ?>>
                    <option value="User" <?= $user['role'] === 'User' ? 'selected' : '' ?>>User (Read Only / Browse)</option>
                    <option value="Manager" <?= $user['role'] === 'Manager' ? 'selected' : '' ?>>Manager (Data CRUD)</option>
                    <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin (Full Control)</option>
                </select>

                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                    <div class="form-text text-warning small mt-1">
                        <i class="bi bi-exclamation-circle me-1"></i>You cannot demote your own account while logged in.
                    </div>
                    <input type="hidden" name="role" value="<?= $user['role'] ?>">
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #ff6b6b, #ff8e53); color: white; margin-top: 10px;">
                <i class="bi bi-check-circle-fill me-2"></i>Save User Changes
            </button>
        </form>

        <a href="manage-users.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Users List
        </a>
    </div>
</body>

</html>