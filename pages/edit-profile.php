<?php
require 'header.php';

// 1. 获取当前登录用户的 ID
$user_id = $_SESSION['user_id'];

// 2. 使用 PDO 语法从数据库查询当前用户数据（完美对接你的 $db）
$query = "SELECT username, email FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="edit-profile.css">
</head>

<body>
    <div class="edit-profile-card">
        <div class="container mt-5">
            <div class="dashboard-card" style="max-width: 500px; margin: auto;">
                <h3 class="form-title mb-4">Edit Profile</h3>

                <form action="update-profile.php" method="POST">

                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="glass-input" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>

                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="glass-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>

                    <button type="submit" class="action-link" style="width: 100%; border: none; cursor: pointer;">
                        <span>Save Changes</span>
                        <i class="bi bi-check2-circle"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>