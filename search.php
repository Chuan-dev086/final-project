<?php
require 'header.php';

$searchQuery = $_GET['q'] ?? '';
$results = [];

if (!empty(trim($searchQuery))) {
    // 增加 birth_date 和 release_date 到搜索范围
    $sql = "SELECT 'Idol' as type, name, id FROM idols WHERE name LIKE ? OR stage_name LIKE ? OR dob LIKE ?
    UNION
    SELECT 'Group' as type, group_name as name, id FROM groups WHERE group_name LIKE ?
    UNION
    SELECT 'Album' as type, name, id FROM albums WHERE name LIKE ? OR songs LIKE ? OR release_date LIKE ?";

    $stmt = $db->prepare($sql);
    $searchTerm = "%$searchQuery%";
    // 现在总共有 7 个 ? 占位符，所以 execute 需要传入 7 个参数
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <div class="py-4 text-white-50">No match found</div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($results as $row):
                    $baseUrl = ($row['type'] === 'Idol') ? 'manage-idols.php?id=' : (($row['type'] === 'Group') ? 'manage-groups.php?id=' : 'manage-albums.php?id=');
                    $link = $baseUrl . $row['id'];
                ?>
                    <li class="list-group-item result-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-white ">
                                <strong class="text-success">[<?= $row['type'] ?>]</strong>
                                <?= htmlspecialchars($row['name']) ?>
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