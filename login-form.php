<?php
require 'header.php';

// if user already login redirected to dashboard 
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];


    $query = "SELECT * FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // 3. 验证用户是否存在
    if (!$user) {
        $error = "登录失败：用户名或密码错误。";
    }
    // 4. 使用标准的 password_verify 进行纯哈希比对（正常账号走这里）
    elseif (password_verify($password, $user['password'])) {
        // 密码正确，发放通行证
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'] ?? 'user';

        header("Location: dashboard.php");
        exit;
    }
    // 🌟 5. 核心拦截：如果哈希比对失败，判断是不是数据库里的“老明文”账号
    elseif ($password === $user['password']) {

        // 在后台立刻把传过来的明文换成标准的安全哈希
        $new_hash = password_hash($password, PASSWORD_DEFAULT);

        // 瞬间传送去 SQL 数据库，把原来的明文覆盖洗白
        $update_query = "UPDATE users SET password = :password WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->execute([
            ':password' => $new_hash,
            ':id' => $user['id']
        ]);

        // 悄悄更新完 SQL 后，直接放行登录！
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'] ?? 'user';

        header("Location: dashboard.php");
        exit;
    }
    // 6. 密码真正对不上的时候
    else {
        $error = "登录失败：用户名或密码错误。";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
                    <h1 class="login-title text-center mb-2">Login Your Account </h1>


                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                        </div>



                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>



                        <input type="hidden" name="role" value="User">

                        <button type="submit" class="btn-signup w-100 mt-4 ">Login</button>
                    </form>

                </div>
            </div>

            <div
                class="d-flex justify-content-center align-items-center gap-5 mx-auto pt-3">
                <a href="registration-form.php" class="text-decoration-none small">Don't Have Account? Sign up here
                    <i class="bi bi-arrow-right-circle"></i></a>
            </div>
        </div>

    </div>
    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>
</body>

</html>