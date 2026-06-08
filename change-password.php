<?php
require 'header.php'; // 确保这里引入了你的数据库连接 $db 并开启了 session

// 1. 安全检查：如果未登录，直接踢回登录页
if (!isset($_SESSION['user_id'])) {
    header('Location: login-form.php'); // 对接你底部的登录文件名
    exit;
}

$error = '';
$success = '';

// 2. 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required!';
    } else {
        // A. 查出当前登录用户在数据库里的加密密码（假设你的用户表名叫 users）
        $query = 'SELECT password FROM users WHERE id = :id';
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // B. 验证旧密码是否正确
            if (!password_verify($current_password, $user['password'])) {
                $error = 'Current password is incorrect!';
            }
            // C. 检查两次输入的新密码是否相同
            elseif ($new_password !== $confirm_password) {
                $error = 'New password and confirmation do not match!';
            }
            // E. 验证通过，执行更新
            else {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

                $update_query = 'UPDATE users SET password = :password WHERE id = :id';
                $update_stmt = $db->prepare($update_query);
                $update_stmt->execute([
                    ':password' => $new_hash,
                    ':id' => $_SESSION['user_id']
                ]);

                $success = 'Password updated successfully! Please log in again.';

                header("Location: dashboard.php");
                exit;
            }
        } else {
            $error = 'User session invalid!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Security Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="change-password.css">
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="password-card">
                    <h1 class="password-title text-center mb-2">Change Password</h1>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" style="border-radius: 12px; background-color: #ef444422; color: #f87171; border: 1px solid #ef444444;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" style="border-radius: 12px; background-color: #10b98122; color: #34d399; border: 1px solid #10b98144;">
                            <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form action="change-password.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-lock-fill"></i> Current Password</label>
                            <input type="password" name="current_password" class="form-control"
                                placeholder="Enter your current password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-key-fill"></i> New Password</label>
                            <input type="password" name="new_password" class="form-control"
                                placeholder="Enter your new password " required>
                            <div class="info-text">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><i class="bi bi-repeat"></i> Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control"
                                placeholder="Re-enter your new password" required>
                        </div>

                        <button type="submit" class="btn-password"><i class="bi bi-arrow-left ms-1"></i>Update Password </button>
                    </form>
                </div>

                <div class="d-flex justify-content-center align-items-center gap-4 pt-4">
                    <a href="dashboard.php" class="bottom-link">
                        <i class="bi bi-arrow-left-circle"></i> Back to Directory
                    </a>
                    <span class="text-white-50">|</span>
                    <a href="logout.php" class="bottom-link">
                        Log In Again <i class="bi bi-box-arrow-in-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>