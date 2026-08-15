<?php
require 'header.php';

// 1. 严格权限校验：只有 Admin 才能进这个页面
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: dashboard.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: manage-users.php');
    exit;
}

// 2. 获取用户基础资料
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<script>alert('User not found!'); window.location.href='manage-users.php';</script>";
    exit;
}

// 判断当前查看的是不是 Admin 自己
$is_me = ($id == $_SESSION['user_id']);

// 3. 处理资料修改提交逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if ($is_me) {
        // 情况 A：Admin 修改自己 -> 允许更新 username 和 email，不准动 role
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        
        if (!empty($username) && !empty($email)) {
            $updateQuery = "UPDATE users SET username = :username, email = :email WHERE id = :id";
            $updateStmt = $db->prepare($updateQuery);
            $result = $updateStmt->execute([
                ':username' => $username, 
                ':email' => $email, 
                ':id' => $id
            ]);
            
            if ($result) {
                $_SESSION['username'] = $username; // 同步更新当前 Session
                header('Location: dashboard.php'); // 改自己就跳回仪表盘
                exit;
            }
        }
    } else {
        // 情况 B：Admin 修改别人 -> 只更新 role，不准动 username 和 email
        $role = $_POST['role'] ?? 'User';
        $updateQuery = "UPDATE users SET role = :role WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        if ($updateStmt->execute([':role' => $role, ':id' => $id])) {
            header('Location: manage-users.php'); // 改别人就跳回用户管理列表
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - KPOP HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="edit-user.css">
</head>

<body>
    <div class="form-container" style="max-width: 520px;">

        <h2 class="form-title" style="background: linear-gradient(to right, #ff6b6b, #ff8e53); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="bi bi-person-gear me-2"></i><?= $is_me ? 'Edit My Profile' : 'Edit User Role' ?>
        </h2>

        <form action="edit-user.php?id=<?= $id ?>" method="POST">
            
            <div class="mb-4 p-3" style="background: rgba(255, 255, 255, 0.03); border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
                
                <div class="mb-3">
                    <label class="form-label text-white-50 small">Username</label>
                    <?php if ($is_me): ?>
                        <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required style="background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1);">
                    <?php else: ?>
                        <span class="fw-semibold text-white fs-5 d-block"><i class="bi bi-person me-2 text-primary"></i><?= htmlspecialchars($user['username']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="mb-2">
                    <label class="form-label text-white-50 small">Email Address</label>
                    <?php if ($is_me): ?>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required style="background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1);">
                    <?php else: ?>
                        <span class="text-white-50 d-block"><i class="bi bi-envelope me-2 text-info"></i><?= htmlspecialchars($user['email']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="role" class="form-label">System Role Permission</label>
                <select class="form-control form-select" id="role" name="role" <?= $is_me ? 'disabled' : '' ?>>
                    <option value="User" <?= $user['role'] === 'User' ? 'selected' : '' ?>>User </option>
                    <option value="Manager" <?= $user['role'] === 'Manager' ? 'selected' : '' ?>>Manager </option>
                    <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                </select>

                <?php if ($is_me): ?>
                    <div class="form-text text-warning small mt-2">
                        <i class="bi bi-exclamation-circle me-1"></i>You cannot demote your own account while logged in.
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #ff6b6b, #ff8e53); color: white; margin-top: 10px;">
                <i class="bi bi-check-circle-fill me-2"></i>Save Changes
            </button>
        </form>

        <a href="manage-users.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Users List
        </a>
    </div>
</body>

</html>