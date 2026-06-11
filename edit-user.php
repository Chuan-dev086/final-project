<?php
require 'header.php';

// 1. 严格权限校验
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

// 3. 处理资料修改提交逻辑（只能修改 role）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'User';

    // 只更新 role 字段
    $updateQuery = "UPDATE users SET role = :role WHERE id = :id";
    $updateParams = [
        ':role' => $role,
        ':id' => $id
    ];
    
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute($updateParams);

    header('Location: manage-users.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Role - KPOP HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="edit-user.css">
</head>

<body>
    <div class="form-container" style="max-width: 520px;">

        <h2 class="form-title" style="background: linear-gradient(to right, #ff6b6b, #ff8e53); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="bi bi-shield-lock me-2"></i>Edit User Role
        </h2>

        <div class="mb-4 p-3" style="background: rgba(255, 255, 255, 0.03); border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
            <div class="mb-2">
                <span class="text-white-50 small d-block">Username</span>
                <span class="fw-semibold text-white fs-5"><i class="bi bi-person me-2 text-primary"></i><?= htmlspecialchars($user['username']) ?></span>
            </div>
            <div>
                <span class="text-white-50 small d-block">Email Address</span>
                <span class="text-white-50"><i class="bi bi-envelope me-2 text-info"></i><?= htmlspecialchars($user['email']) ?></span>
            </div>
        </div>

        <form action="edit-user.php?id=<?= $id ?>" method="POST">
            
            <div class="mb-4">
                <label for="role" class="form-label">System Role Permission</label>
                <select class="form-control form-select" id="role" name="role" <?= ($user['id'] == $_SESSION['user_id']) ? 'disabled' : '' ?>>
                    <option value="User" <?= $user['role'] === 'User' ? 'selected' : '' ?>>User (Read Only / Browse)</option>
                    <option value="Manager" <?= $user['role'] === 'Manager' ? 'selected' : '' ?>>Manager (Data CRUD)</option>
                    <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin (Full Control)</option>
                </select>

                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                    <div class="form-text text-warning small mt-2">
                        <i class="bi bi-exclamation-circle me-1"></i>You cannot demote your own account while logged in.
                    </div>
                    <input type="hidden" name="role" value="<?= $user['role'] ?>">
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #ff6b6b, #ff8e53); color: white; margin-top: 10px;">
                <i class="bi bi-check-circle-fill me-2"></i>Save Role Changes
            </button>
        </form>

        <a href="manage-users.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Users List
        </a>
    </div>
</body>

</html>