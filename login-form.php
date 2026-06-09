<?php
// DRY principle 
require 'header.php';

// if user already login redirected to dashboard 
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
// create variable to store error and print out at page (easy for debug)
$error = '';

// receive username & password through POST method and store it to variable below 
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

// query that find username at table 'users' in database  
    $query = "SELECT * FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // if username not in database and print out error 
    if (!$user) {
        $error = "登录失败：用户名或密码错误。";
    }
    // if got the username in DB then verify the poassword 
    elseif (password_verify($password, $user['password'])) {
    // if password correct , redirect to dashboard 
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'] ?? 'user';

        header("Location: dashboard.php");
        exit;
    }
    // check the password maybe this password not yet hashing 
    elseif ($password === $user['password']) {
        // hash the password 
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        // send the hashed password to database and cover the old password haven't hash 
        $update_query = "UPDATE users SET password = :password WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->execute([
            ':password' => $new_hash,
            ':id' => $user['id']
        ]);

        // when SQL updated ,redirect to dashboard 
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'] ?? 'user';

        header("Location: dashboard.php");
        exit;
    }
    // when password is incorrect
    else {
        $error = "Login Failed !! , Username or Password Incorrect ";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login-Form </title>
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
                    <!-- when username or password incorrect will show red alert box  -->
                    <?php if (!empty($error)): ?>
                        <div class="mb-3 p-3 text-center small"
                            style="background: rgba(220, 53, 69, 0.15); border: 1px solid rgba(220, 53, 69, 0.3); border-radius: 12px; color: #f87171;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                        </div>
                    <?php endif; ?>
                    <!-- Form Area  -->
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