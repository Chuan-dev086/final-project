<?php
require 'header.php';

// 检查是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? '';
if (empty($id)) {
    header('Location: manage-groups.php');
    exit;
}

// 获取组合及成员数据
$query = "SELECT g.group_name, i.stage_name as idol_name 
          FROM groups g
          LEFT JOIN idol_group ig ON g.id = ig.group_id
          LEFT JOIN idols i ON ig.idol_id = i.id
          WHERE g.id = :id";
$stmt = $db->prepare($query);
$stmt->execute([':id' => $id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$results) {
    header('Location: manage-groups.php');
    exit;
}

$current_group_name = $results[0]['group_name'];
$members = array_filter(array_column($results, 'idol_name'));

// 处理编辑请求（只有 Admin 才有权限执行）
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'Admin') {
    $new_name = trim($_POST['group_name'] ?? '');
    if (!empty($new_name)) {
        $updateStmt = $db->prepare("UPDATE groups SET group_name = :name WHERE id = :id");
        $updateStmt->execute([':name' => $new_name, ':id' => $id]);
        $current_group_name = $new_name;
        $success = 'Group updated successfully!';
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
    <link rel="stylesheet" href="edit-group.css">
</head>

<body>
    <div class="form-container">
        <h2 class="form-title"><?= ($_SESSION['role'] === 'Admin') ? 'Edit Group' : 'View Group' ?></h2>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <form action="edit-group.php?id=<?= $id ?>" method="POST">
                <div class="mb-4">
                    <label class="form-label">Group Name</label>
                    <input type="text" class="form-control" name="group_name" value="<?= $current_group_name ?>" required>
                </div>
                
                <div class="mb-4 ">
                    <label class="form-label">Members:</label>
                    <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05);">
                        <?php if (!empty($members)): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($members as $name): ?>
                                    <span class="badge" style="background: #a78bfa33; color: #e9d5ff; border: 1px solid #a78bfa;">
                                        <?= $name ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-white-50 m-0 small">No members assigned.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Update Changes</button>
            </form>
        <?php else: ?>
            <div class="mb-4">
                <label class="form-label">Group Name</label>
                <div class="p-3" style="background: #111827; border-radius: 12px; color: #fff;">
                    <?= $current_group_name ?>
                </div>
            </div>
        <?php endif; ?>





        <a href="manage-groups.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
</body>

</html>