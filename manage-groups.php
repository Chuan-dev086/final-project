<?php
// DRY principle 
require './includes/header.php';

// verify login status 
if (!isset($_SESSION['user_id'])) {
    header('Location: login-form.php');
    exit;
}

// 安全转义快捷函数(prevent XSS attack )
function h(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}


// role verify 
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';

// get all the count of idol，album and group from DB 
$query = "SELECT 
            g.id, 
            g.group_name, 
            (SELECT COUNT(ig.idol_id) FROM idol_group ig WHERE ig.group_id = g.id) AS dynamic_members_count,
            COUNT(a.id) AS albums_count
          FROM `groups` g
          LEFT JOIN albums a ON g.id = a.group_id
          GROUP BY g.id
          ORDER BY g.id ASC";

$stmt = $db->prepare($query);
$stmt->execute();
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Groups - KPOP HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/manage-groups.css">
</head>

<body>
    <div class="container my-4">
        <div class="d-flex mb-4 px-2 justify-content-between align-items-center">
            <a href="dashboard.php" class="btn-action-back small">
                <i class="me-1 bi bi-arrow-left"></i>Back to Dashboard
            </a>
            <?php if ($is_admin): ?>
                <a href="./pages/add-group.php" class="btn btn-add-group small">
                    <i class="me-1 bi bi-plus-circle"></i>Add New Group
                </a>
            <?php endif; ?>
        </div>

        <div class="glass-panel">
            <div class="mb-4">
                <h2 class="mb-1 gradient-title"><i class="me-2 bi bi-people-fill"></i>Groups List</h2>
            </div>

            <div class="table-responsive">
                <table class="mb-0 table table-glass align-middle">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th class="col-group-name">Group Name</th>
                            <th class="col-members">Members Count</th>
                            <th class="col-albums">Albums Count</th>
                            <th class="col-actions text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($groups)): ?>
                            <tr>
                                <td colspan="5" class="py-5 text-center text-white-50">
                                    <i class="d-block mb-2 fs-3 bi bi-folder-x"></i>No groups found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($groups as $row): ?>
                                <tr>
                                    <td><span class="custom-id"><?= h($row['id']) ?></span></td>
                                    <td><span class="fw-semibold custom-group-name"><?= h($row['group_name']) ?></span></td>
                                    <td>
                                        <span class="custom-members">
                                            <i class="me-1 bi bi-person-fill small"></i><?= h($row['dynamic_members_count']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="custom-albums">
                                            <i class="me-1 bi bi-journal-album small"></i><?= h($row['albums_count']) ?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">
                                            <?php if ($is_admin): ?>
                                                <!-- urlencode is function to prevent URL damage if your id change  -->
                                                <a href="./pages/edit-group.php?id=<?= urlencode($row['id']) ?>" class="btn-edit">
                                                    <i class="me-1 bi bi-pencil-square"></i>Edit
                                                </a>
                                                <a href="./actions/delete-group.php?id=<?= urlencode($row['id']) ?>"
                                                    onclick="return confirm('Are you sure you want to delete this group?');"
                                                    class="btn-delete">
                                                    <i class="me-1 bi bi-trash3-fill"></i>Delete
                                                </a>
                                            <?php else: ?>
                                                <a href="./pages/edit-group.php?id=<?= urlencode($row['id']) ?>" class="btn-edit">
                                                    <i class="me-1 bi bi-eye-fill"></i>View
                                                </a>
                                            <?php endif; ?>
                                        </div>
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