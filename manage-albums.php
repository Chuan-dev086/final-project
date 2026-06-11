<?php
require 'header.php';

// 1. 检查登录
if (!isset($_SESSION['user_id'])) {
    header('Location: login-form.php');
    exit;
}

// htmlspecialchars function 
function h(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}


// 2. 查询：获取所有专辑及其关联的组合名称
$query = "SELECT a.*, g.group_name 
          FROM albums a 
          LEFT JOIN groups g ON a.group_id = g.id 
          ORDER BY a.release_date ASC ";
$albums = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Albums - KPOP HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="manage-albums.css">
</head>

<body>
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <a href="dashboard.php" class="btn-action-back small">
                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
            </a>
            <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager'): ?>
                <a href="add-album.php" class="btn btn-add-group small">
                    <i class="bi bi-disc-fill me-1"></i>Add New Album
                </a>
            <?php endif; ?>
        </div>

        <div class="glass-panel">
            <div class="mb-4">
                <h2 class="gradient-title mb-1"><i class="bi bi-disc me-2"></i>Albums List</h2>
            </div>

            <div class="table-responsive">
                <table class="table table-glass align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Album Name</th>
                            <th>Group</th>
                            <th>Release Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($albums)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-white-50 py-4">
                                    <i class="bi bi-folder-x fs-3 d-block mb-2"></i>No albums found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($albums as $row): ?>
                                <tr>
                                    <td><span class="custom-album-name"><i class="bi bi-disc-fill small me-2"></i><?= h($row['name']) ?></span></td>
                                    <td><span class="custom-group-row"><i class="bi bi-people-fill small me-2"></i><?= h($row['group_name']) ?? 'Unknown' ?></span></td>
                                    <td><span class="custom-date-row"><i class="bi bi-calendar3 small me-2"></i><?= h($row['release_date']) ?></span></td>
                                    <td class="text-center">

                                        <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager'): ?>
                                            <a href="edit-album.php?id=<?= $row['id'] ?>" class="btn-edit">
                                                <i class="bi bi-pencil-square me-1"></i>Edit
                                            </a>
                                        <?php else: ?>
                                            <a href="edit-album.php?id=<?= $row['id'] ?>" class="btn-edit">
                                                <i class="bi bi-eye-fill me-1"></i>View
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager'): ?>
                                            <a href="delete-album.php?id=<?= $row['id'] ?>"
                                                onclick="return confirm('Delete this album?');"
                                                class="btn-delete">
                                                <i class="bi bi-trash3-fill me-1"></i>Delete
                                            </a>
                                        <?php endif; ?>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>