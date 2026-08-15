<?php
require 'header.php';

// 1. 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: manage-albums.php');
    exit;
}

// 2. 获取当前专辑完整信息
$stmt = $db->prepare("SELECT * FROM albums WHERE id = :id");
$stmt->execute([':id' => $id]);
$album = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$album) {
    echo "<script>alert('Album not found!'); window.location.href='manage-albums.php';</script>";
    exit;
}

// 3. 读取所有组合，供下拉菜单渲染
$groups_stmt = $db->query("SELECT id, group_name FROM `groups` ORDER BY group_name ASC");
$groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';

// 🌟 核心处理：智能解析数据库里的歌曲数据
// 无论以前是用逗号隔开的，还是换行隔开的，统一解析成干净的数组
$raw_songs = !empty($album['songs']) ? preg_split('/[\r\n,]+/', $album['songs']) : [];
// 去除每一首歌前后的空格，并过滤掉空行
$tracklist = array_filter(array_map('trim', $raw_songs));

// 转换成供后台 Textarea 显示的一行一首的文本格式
$songs_for_textarea = implode("\n", $tracklist);


// 4. 处理表单保存逻辑（Admin 和 Manager）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') {
        echo "<script>alert('Unauthorized action!'); window.location.href='manage-albums.php';</script>";
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $group_id = $_POST['group_id'] ?? null;
    $release_date = trim($_POST['release_date'] ?? '');
    
    // 🌟 接收前端文本框传过来的一行行的歌曲
    $songs_input = $_POST['songs'] ?? '';
    // 按换行符切开
    $submitted_songs = preg_split('/[\r\n]+/', $songs_input);
    // 清理空格和空行
    $cleaned_songs = array_filter(array_map('trim', $submitted_songs));
    // 统一用“逗号+空格”拼装存入数据库，保证你原有的数据库结构不被破坏
    $songs_to_save = implode(', ', $cleaned_songs);

    if (empty($name) || empty($release_date)) {
        $error = 'Album Name and Release Date are required!';
    } else {
        $updateQuery = "UPDATE albums SET name = :name, group_id = :group_id, release_date = :release_date, songs = :songs WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([
            ':name' => $name,
            ':group_id' => !empty($group_id) ? $group_id : null,
            ':release_date' => $release_date,
            ':songs' => $songs_to_save, // 写入清洗后的数据
            ':id' => $id
        ]);
        header('Location: manage-albums.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager') ? 'Edit Album' : 'Album Details' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="add-idols.css">
</head>
<body>
    <div class="form-container" style="max-width: 550px;">
        
        <h2 class="form-title" style="background: linear-gradient(to right, #a78bfa, #ff6b6b); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
            <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager'): ?>
                <i class="bi bi-pencil-square me-2"></i>Edit Album
            <?php else: ?>
                <i class="bi bi-disc-fill me-2"></i>Album Details
            <?php endif; ?>
        </h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="border-radius: 12px; background-color: #ef444422; color: #f87171; border: 1px solid #ef444444;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>

        <form action="edit-album.php?id=<?= $id ?>" method="POST">
            
            <!-- 专辑名称 -->
            <div class="mb-4">
                <label for="name" class="form-label">Album Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?= $album['name'] ?>" 
                       <?= ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') ? 'readonly' : '' ?> required>
            </div>

            <!-- 所属组合 -->
            <div class="mb-4">
                <label for="group_id" class="form-label">Group</label>
                <select class="form-control form-select" id="group_id" name="group_id" 
                        <?= ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') ? 'disabled' : '' ?>>
                    <option value="">-- Soloist / No Group --</option>
                    <?php foreach ($groups as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $g['id'] == $album['group_id'] ? 'selected' : '' ?>>
                            <?= $g['group_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 发行日期 -->
            <div class="mb-4">
                <label for="release_date" class="form-label">Release Date</label>
                <input type="date" class="form-control" id="release_date" name="release_date" 
                       value="<?= $album['release_date'] ?>" 
                       <?= ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') ? 'readonly' : '' ?> required>
            </div>

            <!-- 🌟 歌曲输入/展现区（已全面升级为一行一首） -->
            <div class="mb-4">
                <label for="songs" class="form-label"><i class="bi bi-music-note-list me-1 text-info"></i> Songs / Tracklist</label>
                
                <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager'): ?>
                    <!-- 情况 A：Admin/Manager 看到的是大文本域，可以回车换行编辑 -->
                    <textarea class="form-control" id="songs" name="songs" rows="6" 
                              placeholder="Type one song per line...&#10;Example:&#10;Song A&#10;Song B&#10;Song C" 
                              style="line-height: 1.6; resize: vertical; min-height: 120px;"><?= $songs_for_textarea ?></textarea>
                    <div class="form-text text-white-50 small mt-1">
                        <i class="bi bi-info-circle me-1"></i>Press <b>Enter</b> to start a new song line.
                    </div>
                <?php else: ?>
                    <!-- 情况 B：普通 User 看到的是高颜值数字化动态音轨列表 -->
                    <div class="p-3" style="background: rgba(17, 24, 39, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px;">
                        <?php if (empty($tracklist)): ?>
                            <div class="text-white-50 small text-center py-2">No tracks in this album.</div>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-2">
                                <?php $index = 1; foreach ($tracklist as $song_title): ?>
                                    <div class="d-flex align-items-center justify-content-between py-2 px-3" 
                                         style="background: rgba(255, 255, 255, 0.03); border-radius: 10px; ">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="text-white-50 small" style="font-family: monospace; width: 20px;">
                                                <?= str_pad($index++, 2, '0', STR_PAD_LEFT) ?>
                                            </span>
                                            <span class="text-white" style="font-size: 15px; font-weight: 500;">
                                                <?= $song_title ?>
                                            </span>
                                        </div>
                                        <i class="bi bi-play-circle-fill text-white-50" style="font-size: 14px;"></i>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 只有 Admin 和 Manager 展现 Save 按钮 -->
            <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager'): ?>
                <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #725ac1, #a78bfa); color: white; margin-top: 10px;">
                    <i class="bi bi-save-fill me-2"></i>Save Changes
                </button>
            <?php endif; ?>
        </form>
        
        <a href="manage-albums.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Albums
        </a>
    </div>
</body>
</html>