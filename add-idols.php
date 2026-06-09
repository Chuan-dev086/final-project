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
// 读取所有组合供下拉菜单选择 (注意字段名为 group_name)
$groups_stmt = $db->query("SELECT id, group_name FROM groups ORDER BY group_name ASC");
$groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $stage_name = trim($_POST['stage_name'] ?? '');
    $group_id = $_POST['group_id'] ?? ''; // 接收选中的组合 ID

    // 接收三个独立的输入框值
    $year = trim($_POST['dob_year'] ?? '');
    $month = trim($_POST['dob_month'] ?? '');
    $day = trim($_POST['dob_day'] ?? '');

    if (empty($name) || empty($stage_name) || empty($year) || empty($month) || empty($day)) {
        $error = 'All fields are required!';
    } else {
        // 🌟 核心技巧：在后端用整型比对进行快速判断补零，确保数据库格式绝对标准
        $month = (int)$month < 10 ? '0' . (int)$month : $month;
        $day   = (int)$day   < 10 ? '0' . (int)$day   : $day;
        // 拼接成标准 YYYY-MM-DD 格式
        $dob = "$year-$month-$day";

        // 1. 先插入爱豆主表
        $query = 'INSERT INTO idols (name, stage_name, dob) VALUES (:name, :stage_name, :dob)';
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':name' => $name,
            ':stage_name' => $stage_name,
            ':dob' => $dob
        ]);

        // 2. 获取刚才生成的爱豆 ID
        $idol_id = $db->lastInsertId();

        // 3. 如果管理员选了组合，向中间表 idol_group 插入关联数据
        if (!empty($group_id)) {
            $group_query = 'INSERT INTO idol_group (group_id, idol_id) VALUES (:group_id, :idol_id)';
            $group_stmt = $db->prepare($group_query);
            $group_stmt->execute([
                ':group_id' => $group_id,
                ':idol_id' => $idol_id
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
    <title>Add New Idol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="add-idols.css">
</head>

<body>
    <div class="form-container">
        <h2 class="form-title">Add New Idol</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="border-radius: 12px; background-color: #ef444422; color: #f87171; border: 1px solid #ef444444;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="add-idols.php" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Real Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Real Name" required>
            </div>

            <div class="mb-3">
                <label for="stage_name" class="form-label">Stage Name</label>
                <input type="text" class="form-control" id="stage_name" name="stage_name" 
                placeholder="Stage Name" required>
            </div>

            <!-- 组合下拉选择框 -->
            <div class="mb-3">
                <label for="group_id" class="form-label">Group </label>
                <select class="form-control" id="group_id" name="group_id">
                    <option value="">Soloist </option>
                    <?php foreach ($groups as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= $g['group_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Date of Birth</label>
                <div class="row g-2">
                    <div class="col-4">
                        <input type="number" class="form-control" name="dob_year"
                            placeholder="Year (年)" min="1970" max="<?= date('Y') ?>" required>
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control" name="dob_month"
                            placeholder="Month (月)" min="1" max="12" required>
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control" name="dob_day"
                            placeholder="Day (日)" min="1" max="31" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-check-circle-fill me-2"></i>Add Idol
            </button>
        </form>
        <a href="manage-idols.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

</body>

</html>