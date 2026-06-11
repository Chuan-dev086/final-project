<?php
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'Admin') {
    echo "<script>alert('Unauthorized access!'); window.location.href='manage-idols.php';</script>";
    exit;
}

$id = $_GET['id'] ?? '';
if (empty($id)) {
    header('Location: manage-idols.php');
    exit;
}

$error = '';

// 🌟 核心优化：使用 LEFT JOIN 一次性把爱豆数据和他在中间表里的 group_id 查出来！
$query = 'SELECT i.*, ig.group_id 
          FROM idols i 
          LEFT JOIN idol_group ig ON i.id = ig.idol_id 
          WHERE i.id = :id';

$stmt = $db->prepare($query);
$stmt->execute([':id' => $id]);
$idol = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$idol) {
    header('Location: manage-idols.php');
    exit;
}

// 提取当前查出来的关系 ID
$current_group_id = $idol['group_id'] ?? '';

// 获取所有可选组合供下拉菜单渲染
$groups_stmt = $db->query("SELECT id, group_name FROM groups ORDER BY group_name ASC");
$groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);

// 拆解生日
$current_year = substr($idol['dob'], 0, 4);
$current_month = substr($idol['dob'], 5, 2);
$current_day = substr($idol['dob'], 8, 2);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $stage_name = trim($_POST['stage_name'] ?? '');
    $group_id = $_POST['group_id'] ?? ''; // 新选的选择框组合 ID

    $year = trim($_POST['dob_year'] ?? '');
    $month = trim($_POST['dob_month'] ?? '');
    $day = trim($_POST['dob_day'] ?? '');

    if (empty($name) || empty($stage_name) || empty($year) || empty($month) || empty($day)) {
        $error = 'All fields are required!';
    } else {
        // 纯三元补零
        $month = (int)$month < 10 ? '0' . (int)$month : $month;
        $day   = (int)$day   < 10 ? '0' . (int)$day   : $day;
        $dob = "$year-$month-$day";

        // 1. 更新主表基本信息
        $update_query = 'UPDATE idols SET name = :name, stage_name = :stage_name, dob = :dob WHERE id = :id';
        $update_stmt = $db->prepare($update_query);
        $update_stmt->execute([
            ':name' => $name,
            ':stage_name' => $stage_name,
            ':dob' => $dob,
            ':id' => $id
        ]);

        // 2. 清除该爱豆在中间表里的所有旧关联记录
        $delete_rel = $db->prepare("DELETE FROM idol_group WHERE idol_id = :idol_id");
        $delete_rel->execute([':idol_id' => $id]);

        // 3. 如果重新选了组合，再向关系表中加一条干净的新纪录
        if (!empty($group_id)) {
            $insert_rel = $db->prepare("INSERT INTO idol_group (group_id, idol_id) VALUES (:group_id, :idol_id)");
            $insert_rel->execute([
                ':group_id' => $group_id,
                ':idol_id' => $id
            ]);
        }

        header('Location: manage-idols.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Idol - K-pop Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="edit-idols.css">
</head>

<body>
    <div class="form-container">
        <h2 class="form-title">Edit Idol Info</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="border-radius: 12px; background-color: #ef444422; color: #f87171; border: 1px solid #ef444444;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="edit-idols.php?id=<?php echo $id; ?>" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Real Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($idol['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="stage_name" class="form-label">Stage Name</label>
                <input type="text" class="form-control" id="stage_name" name="stage_name" value="<?php echo $idol['stage_name']; ?>" required>
            </div>

            <!-- groups -->
            <div class="mb-3">
                <label for="group_id" class="form-label">Group (所属组合)</label>
                <select class="form-control" id="group_id" name="group_id">
                    <option value="" <?= empty($current_group_id) ? 'selected' : '' ?>>Soloist / No Group (个人歌手/无组合)</option>
                    <?php foreach ($groups as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $g['id'] == $current_group_id ? 'selected' : '' ?>><?= $g['group_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>


            <!-- DOB -->
            <div class="mb-4">
                <label class="form-label">Date of Birth</label>
                <div class="row g-2">
                    <div class="col-4">
                        <input type="number" class="form-control" name="dob_year"
                            value="<?= $current_year ?>" min="1970" max="<?= date('Y') ?>" required>
                    </div>

                    <div class="col-4">
                        <input type="number" class="form-control" name="dob_month"
                            value="<?= $current_month ?>" min="1" max="12" required>
                    </div>

                    <div class="col-4">
                        <input type="number" class="form-control" name="dob_day"
                            value="<?= $current_day ?>" min="1" max="31" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-pencil-square me-2"></i>Update Changes
            </button>
        </form>
        <a href="manage-idols.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Cancel & Back
        </a>
    </div>
</body>
<script>
    // 标记页面是否有未保存的更改
    let isDirty = false;

    // 监听页面内所有输入框和下拉菜单的变化
    const formFields = document.querySelectorAll('input, select');
    formFields.forEach(field => {
        field.addEventListener('input', () => {
            isDirty = true;
        });
    });

    // 当用户尝试关闭页面、刷新页面或点击跳转链接时触发
    window.addEventListener('beforeunload', (event) => {
        if (isDirty) {
            // 浏览器会拦截并显示自带的确认框
            event.preventDefault();
            event.returnValue = ''; // 某些浏览器需要此设置
        }
    });

    // 关键：在表单提交时，将标记设为 false
    // 否则点击“保存”按钮正常提交时，也会被拦截
    document.querySelector('form').addEventListener('submit', () => {
        isDirty = false;
    });
</script>

</html>