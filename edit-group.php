<?php
require 'header.php';

// 1. 登录校验
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: manage-groups.php');
    exit;
}

// 2. 查询当前选中的组合名字
$stmt = $db->prepare("SELECT * FROM `groups` WHERE id = :id");
$stmt->execute([':id' => $id]);
$group = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$group) {
    echo "<script>alert('Group not found!'); window.location.href='manage-groups.php';</script>";
    exit;
}


// 🌟 3. 核心：点进来后，才从数据库里把这个 group 的所有组员艺名查出来
$members_stmt = $db->prepare("
    SELECT i.stage_name 
    FROM idols i 
    JOIN idol_group ig ON i.id = ig.idol_id 
    WHERE ig.group_id = :group_id
    ORDER BY i.stage_name ASC
");
$members_stmt->execute([':group_id' => $id]);
$members = $members_stmt->fetchAll(PDO::FETCH_COLUMN); // 拿到纯名字数组

$error = '';

// 4. 处理表单修改逻辑（只有 Admin 有权修改提交）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['role'] !== 'Admin') {
        echo "<script>alert('Only Admin can edit!'); window.location.href='manage-groups.php';</script>";
        exit;
    }

    $group_name = trim($_POST['group_name'] ?? '');

    if (empty($group_name)) {
        $error = 'Group Name cannot be empty!';
    }
    $updateQuery = "UPDATE `groups` SET group_name = :group_name WHERE id = :id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([
        ':group_name' => $group_name,
        ':id' => $id
    ]);
    header('Location: manage-groups.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Details & Edit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="edit-group.css">
</head>

<body>
    <div class="form-container">
        <h2 class="form-title" style="background: linear-gradient(to right, #a78bfa, #ff6b6b); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <i class="bi bi-pencil-square me-2"></i>Edit Group
            <?php else: ?>
                <i class="bi bi-eye-fill me-2"></i>Group Details
            <?php endif; ?>
        </h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="border-radius: 12px; background-color: #ef444422; color: #f87171; border: 1px solid #ef444444;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>
        <form action="edit-group.php?id=<?= $id ?>" method="POST">
            <div class="mb-4">
                <label for="group_name" class="form-label">Group Name</label>
                    <input type="text" class="form-control" id="group_name" name="group_name"
                        value="<?= $group['group_name'] ?>"
                        <?= $_SESSION['role'] !== 'Admin' ? 'readonly' : '' ?> required>
            </div>
            <!-- members content  -->
            <div class="mb-4">
                <label class="form-label">Current Members</label>
                <div class="d-flex flex-wrap gap-2 pt-1">
                    <?php if (empty($members)): ?>
                        <span class="text-white-50 small">No members assigned to this group yet.</span>
                    <?php else: ?>
                        <?php foreach ($members as $name): ?>
                            <span style="background: rgba(167, 139, 250, 0.15); color: #c084fc; border: 1px solid rgba(167, 139, 250, 0.3); padding: 6px 14px; border-radius: 20px; font-size: 14px; font-weight: 500;">
                                <i class="bi bi-person-badge-fill me-1 small"></i><?= $name ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>


            <!-- 只有管理员显示 Save 按钮 -->
            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #725ac1, #a78bfa); color: white;">
                    <i class="bi bi-save-fill me-2"></i>Save Changes
                </button>
            <?php endif; ?>
        </form>

        <a href="manage-groups.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Groups
        </a>
    </div>
</body>

</html>