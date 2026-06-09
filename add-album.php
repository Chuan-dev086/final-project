<?php
require 'header.php';

// 1. 严格的安全检查：未登录或非管理员直接拦截
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !=='Manager'){
    echo "<script>alert('Unauthorized access!'); window.location.href='manage-albums.php';</script>";
    exit;
}

// 2. 读取所有组合，供下拉菜单（Select）选择
$groups_stmt = $db->query("SELECT id, group_name FROM groups ORDER BY group_name ASC");
$groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';

// 3. 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $group_id = $_POST['group_id'] ?? ''; 
    $songs = trim($_POST['songs'] ?? ''); // 🌟 接收新增的歌曲信息

    // 🌟 接收三个独立的日期输入框值
    $year = trim($_POST['release_year'] ?? '');
    $month = trim($_POST['release_month'] ?? '');
    $day = trim($_POST['release_day'] ?? '');

    // 验证所有字段（因为数据库全是 NOT NULL，所有字段均为必填）
    if (empty($name) || empty($group_id) || empty($songs) || empty($year) || empty($month) || empty($day)) {
        $error = 'All fields are required! Please select a group and fill in the songs.';
    } else {
        // 后端自动补零，确保写入数据库的 date 格式绝对标准 (YYYY-MM-DD)
        $month = (int)$month < 10 ? '0' . (int)$month : $month;
        $day   = (int)$day   < 10 ? '0' . (int)$day   : $day;
        $release_date = "$year-$month-$day";
        
        // 🌟 从 Session 中自动抓取当前录入数据的管理员 ID，对应 created_by 字段
        $created_by = $_SESSION['user_id'];

        try {
            // 🌟 严格对应你的数据库字段进行高效插入
            $query = 'INSERT INTO albums (name, release_date, group_id, created_by, songs) 
                      VALUES (:name, :release_date, :group_id, :created_by, :songs)';
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':name' => $name,
                ':release_date' => $release_date,
                ':group_id' => $group_id,
                ':created_by' => $created_by,
                ':songs' => $songs
            ]);

            // 添加成功后，重定向回专辑管理页
            header('Location: manage-albums.php');
            exit;
        } catch (PDOException $e) {
            // 如果发生外键错误或其他数据库错误，捕获并提示出来
            $error = 'Database Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Album</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="add-album.css">
</head>

<body>
    <div class="form-container">
        <h2 class="form-title"><i class="bi bi-disc-fill me-2"></i>Add New Album</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="border-radius: 12px; background-color: #ef444422; color: #f87171; border: 1px solid #ef444444;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="add-album.php" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Album Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Album Name" required>
            </div>

            <div class="mb-3">
                <label for="group_id" class="form-label">Group</label>
                <select class="form-control" id="group_id" name="group_id" required>
                    <option value="" disabled selected>-- Select a Group --</option>
                    <?php foreach ($groups as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= $g['group_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="songs" class="form-label">Songs List</label>
                <input type="text" class="form-control" id="songs" name="songs" placeholder="e.g., Title, Track 2, Track 3" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Release Date</label>
                <div class="row g-2">
                    <div class="col-4">
                        <input type="number" class="form-control" name="release_year"
                            placeholder="Year (年)" min="1970" max="<?= date('Y') ?>" required>
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control" name="release_month"
                            placeholder="Month (月)" min="1" max="12" required>
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control" name="release_day"
                            placeholder="Day (日)" min="1" max="31" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-check-circle-fill me-2"></i>Add Album
            </button>
        </form>
        
        <a href="manage-albums.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Albums
        </a>
    </div>
</body>

</html>