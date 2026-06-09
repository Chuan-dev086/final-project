<?php
require 'header.php';

// 权限校验
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit;
}
if ($_SESSION['role'] !== 'Admin') {
    // 你可以弹窗提示，或者直接跳转回管理列表页
    echo "<script>alert('你没有权限进行此操作！'); window.location.href='manage-groups.php';</script>";
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = trim($_POST['group_name'] ?? '');

    if (empty($group_name)) {
        $error = 'Group Name is required!';
    } else {
        // SQL 仅插入 group_name
        $query = "INSERT INTO groups (group_name) VALUES (:group_name)";
        $stmt = $db->prepare($query);
        $stmt->execute([':group_name' => $group_name]);

        header('Location: manage-groups.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>K Pop Management System </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="add-group.css">
</head>

<body>
    <div class="form-container">
        <h2 class="form-title">Add New Group</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="border-radius: 12px; background-color: #ef444422; color: #f87171; border: 1px solid #ef444444;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form action="add-group.php" method="POST">
            <div class="mb-4">
                <label for="group_name" class="form-label">Group Name</label>
                <input type="text" class="form-control" id="group_name" name="group_name" placeholder="Enter group name" required>
            </div>

            <button type="submit" class="btn-submit">Create Group</button>
        </form>
        <a href="manage-groups.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

</body>

</html>