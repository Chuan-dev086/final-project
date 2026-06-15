<?php
require 'header.php';

// 1. 获取输入，削掉两边空格
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

// 2. 【核心升级】把字符串里的 % 和 _ 符号全部死死去掉
// 这样如果用户输入 "%%" 就会被过滤成 "", 从而触发下面的拦截
$cleanQuery = str_replace(['%', '_'], '', $searchQuery);

// 3. 拦截空搜索（包括纯空格、纯百分号、纯下划线等恶意输入）
if ($cleanQuery === '') {
    echo "<script>alert('Please enter a valid keyword to search!'); window.location.href='dashboard.php';</script>";
    exit;
}

// 4. 用过滤干净的关键词去绑定 SQL 查询
$likeQuery = "%" . $cleanQuery . "%";

// 升级版 SQL，继续完美支持艺名、真名、生日和发行日期数字
$stmt = $db->prepare("
    SELECT 'Idol' AS type, CONCAT(name, ' (', stage_name, ')') AS display_name, id 
    FROM idols 
    WHERE name LIKE ? OR stage_name LIKE ? OR dob LIKE ?
    
    UNION
    
    SELECT 'Group' AS type, group_name AS display_name, id 
    FROM `groups` 
    WHERE group_name LIKE ?
    
    UNION
    
    SELECT 'Album' AS type, name AS display_name, id 
    FROM albums 
    WHERE name LIKE ? OR release_date LIKE ?
");

$stmt->execute([
    $likeQuery, $likeQuery, $likeQuery,
    $likeQuery,
    $likeQuery, $likeQuery
]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="search.css">
</head>

<body>
    <div class="container py-5 search-container">
        <h2>Search Result : "<?= htmlspecialchars($searchQuery) ?>"</h2>
        <hr class="border-secondary">
        <?php if (empty($results)): ?>
            <div class="py-4 text-white-50">No match found</div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($results as $row):
                    $baseUrl = ($row['type'] === 'Idol') ? 'manage-idols.php?id=' : (($row['type'] === 'Group') ? 'manage-groups.php?id=' : 'manage-albums.php?id=');
                    $link = $baseUrl . $row['id'];
                ?>
                    <li class="list-group-item result-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-white">
                                <strong class="text-success">[<?= $row['type'] ?>]</strong>
                                <?= htmlspecialchars($row['display_name']) ?>
                            </span>
                            <a href="<?= $link ?>" class="result-link">View Details</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-outline-warning">Back to Dashboard</a>
        </div>
    </div>
</body>

</html>