<?php
require 'header.php'; // 确保这里连接了数据库 $db

// receive the word of search bar 
$searchQuery = $_GET['q'] ?? '';
$results = [];

if (!empty($searchQuery)) {
// union 把这三张表的搜索结果“堆叠”在一起，一次性返回给页面
// WHERE LIKE ?：是模糊匹配(Fuzzy Matching)。? 是占位符(placeholder)
    $sql = "SELECT 'Idol' as type, name, id FROM idols WHERE name LIKE ? OR stage_name LIKE ?
        UNION
        SELECT 'Group' as type, group_name as name, id FROM groups WHERE group_name LIKE ?
        UNION
        SELECT 'Album' as type, name, id FROM albums WHERE name LIKE ? OR songs LIKE ? ";

    $stmt = $db->prepare($sql);
    $searchTerm = "%$searchQuery%";  //在搜索词前后加上 % 是 SQL 的通配符，
    //表示匹配包含该词的任意字符串（例如搜“BTS”，能搜到“BTS-Butter”）。
    // 注意这里多了一个 $searchTerm，对应上方新增的 OR stage_name LIKE ?
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC); //把查询到的所有结果转换成一个“关联数组”
                                            //，方便你在 HTML 中通过 foreach 循环展示出来。
}
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
            <div class="py-4 text-white-50">No match found </div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($results as $row):
                    $link = '#';
                    if ($row['type'] === 'Idol') $link = 'manage-idols.php';
                    elseif ($row['type'] === 'Group') $link = 'manage-groups.php';
                    elseif ($row['type'] === 'Album') $link = 'manage-albums.php';
                ?>
                    <li class="list-group-item result-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-primary">
                                <strong class="text-success">[<?= $row['type'] ?>]</strong>
                                <?= htmlspecialchars($row['name']) ?>
                            </span>
                            <a href="<?= $link ?>" class="result-link">
                                Go to Page 
                            </a>
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