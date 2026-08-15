<?php
require './includes/header.php';

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
    <link rel="stylesheet" href="./css/manage-albums.css">
</head>

<body>
    <div class="container my-4">
        <div class="d-flex mb-4 px-2 justify-content-between align-items-center">
            <a href="dashboard.php" class="btn-action-back small">
                <i class="me-1 bi bi-arrow-left"></i>Back to Dashboard
            </a>
            <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager'): ?>
                <a href="./pages/add-album.php" class="btn btn-add-group small">
                    <i class="me-1 bi bi-disc-fill"></i>Add New Album
                </a>
            <?php endif; ?>
        </div>

        <div class="glass-panel">
            <div class="mb-4">
                <h2 class="mb-1 gradient-title"><i class="me-2 bi bi-disc"></i>Albums List</h2>
            </div>

            <div class="table-responsive">
                <table class="mb-0 table table-glass align-middle">
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
                                <td colspan="4" class="py-4 text-center text-white-50">
                                    <i class="d-block mb-2 fs-3 bi bi-folder-x"></i>No albums found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($albums as $row): ?>
                                <tr>
                                    <td><span class="custom-album-name"><i class="me-2 bi bi-disc-fill small"></i><?= h($row['name']) ?></span></td>
                                    <td><span class="custom-group-row"><i class="me-2 bi bi-people-fill small"></i><?= h($row['group_name']) ?? 'Unknown' ?></span></td>
                                    <td><span class="custom-date-row"><i class="me-2 bi bi-calendar3 small"></i><?= h($row['release_date']) ?></span></td>
                                    <td class="text-center">

                                        <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager'): ?>
                                            <a href="./pages/edit-album.php?id=<?= $row['id'] ?>" class="btn-edit">
                                                <i class="me-1 bi bi-pencil-square"></i>Edit
                                            </a>
                                        <?php else: ?>
                                            <a href="./pages/edit-album.php?id=<?= $row['id'] ?>" class="btn-edit">
                                                <i class="me-1 bi bi-eye-fill"></i>View
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager'): ?>
                                            <a href="./actions/delete-album.php?id=<?= $row['id'] ?>"
                                                onclick="return confirm('Delete this album?');"
                                                class="btn-delete">
                                                <i class="me-1 bi bi-trash3-fill"></i>Delete
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