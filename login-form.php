<?php
// DRY principle 
require 'header.php';

// if user already login redirected to dashboard 
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success_msg = '';

// 登录逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "Login Failed !! , Username or Password Incorrect ";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'] ?? 'user';

            header("Location: dashboard.php");
            exit;
        } elseif ($password === $user['password']) {
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = :password WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->execute([
                ':password' => $new_hash,
                ':id' => $user['id']
            ]);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'] ?? 'user';

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Login Failed !! , Username or Password Incorrect ";
        }
    }
}

// reset password 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_reset') {
    $reset_username = trim($_POST['reset_username'] ?? '');
    $reset_email    = trim($_POST['reset_email'] ?? '');
    $new_password   = $_POST['new_password'] ?? '';

    if (empty($reset_username) || empty($reset_email) || empty($new_password)) {
        $error = "All fields are required for password reset.";
    } else {
        // 安全核验：必须用户名和邮箱完全匹配上同一个用户
        $check_stmt = $db->prepare("SELECT id FROM users WHERE username = :username AND email = :email");
        $check_stmt->execute([
            ':username' => $reset_username,
            ':email'    => $reset_email
        ]);
        $user_found = $check_stmt->fetch();

        if ($user_found) {
            // 使用 BCRYPT 对用户输入的新密码进行安全哈希加密
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $update_stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $update_stmt->execute([
                ':password' => $hashed_password,
                ':id'       => $user_found['id']
            ]);

            $success_msg = "<strong>Password Updated Successfully!</strong><br>You can now log in with your new custom password.";
        } else {
            $error = "Verification failed. Username and Email do not match.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Reset - KPOP HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="login-form.css">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-6">
                <div class="login-card">
                    <!-- the error card  -->
                    <?php if (!empty($error)): ?>
                        <div class="mb-3 p-3 text-center small"
                            style="background: rgba(220, 53, 69, 0.15); border: 1px solid rgba(220, 53, 69, 0.3); border-radius: 12px; color: #f87171;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($success_msg)): ?>
                        <div class="mb-3 p-3 text-center small"
                            style="background: rgba(25, 135, 84, 0.15); border: 1px solid rgba(25, 135, 84, 0.3); border-radius: 12px; color: #75b798;">
                            <i class="bi bi-check-circle-fill me-2"></i><?= $success_msg ?>
                        </div>
                    <?php endif; ?>
                    <div id="login-section">
                        <h1 class="login-title text-center mb-4">Login Your Account</h1>
                        <form method="POST">
                            <input type="hidden" name="action" value="login">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                            </div>
                            <input type="hidden" name="role" value="User">

                            <button type="submit" class="btn-signup w-100 mt-3">Login</button>
                        </form>

                        <div class="text-end mt-3">
                            <a href="javascript:void(0);" onclick="toggleForm('reset')" class="text-decoration-none small text-white-50">
                                <i class="bi bi-question-circle me-1"></i>Forgot Password? Reset It
                            </a>
                        </div>
                    </div>

                    <!-- reset password  -->
                    <div id="reset-section" style="display: none;">
                        <h1 class="login-title text-center mb-4" style="background: linear-gradient(to right, #ff6b6b, #ff8e53); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
                            Reset Password
                        </h1>
                        <form method="POST">
                            <input type="hidden" name="action" value="request_reset">

                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="reset_username" class="form-control" placeholder="Confirm your username" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Registered Email</label>
                                <input type="email" name="reset_email" class="form-control" placeholder="example@kpop.com" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-warning"><i class="bi bi-key-fill me-1"></i>Enter New Password</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Set your new custom password" required>
                            </div>

                            <button type="submit" class="btn-signup w-100" style="border-color: #ff6b6b; color: #ff6b6b;">
                                Change Password Instantly
                            </button>
                        </form>

                        <div class="text-start mt-4">
                            <a href="javascript:void(0);" onclick="toggleForm('login')" class="text-decoration-none small text-white-50">
                                <i class="bi bi-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <div class="d-flex justify-content-center align-items-center gap-5 mx-auto pt-3">
                <a href="registration-form.php" class="text-decoration-none small text-white-50">
                    Don't Have Account? Sign up here <i class="bi bi-arrow-right-circle"></i>
                </a>
            </div>
        </div>
    </div>

    <script>
        // change the form 
        function toggleForm(target) {
            const loginSec = document.getElementById('login-section');
            const resetSec = document.getElementById('reset-section');
            if (target === 'reset') {
                loginSec.style.display = 'none';
                resetSec.style.display = 'block';
            } else {
                loginSec.style.display = 'block';
                resetSec.style.display = 'none';
            }
        }
        //  verify for reset password 
        <?php if (isset($_POST['action']) && $_POST['action'] === 'request_reset'): ?>
            <?php if (!empty($error)): ?>
                // if info was wrong stay in reset pw page 
                toggleForm('reset');
            <?php else: ?>
                // if correct redirect to login-form
                toggleForm('login');
            <?php endif; ?>
        <?php endif; ?>
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>