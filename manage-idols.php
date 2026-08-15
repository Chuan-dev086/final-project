<?php
// DRY principle 
require './includes/header.php';

// verify user login status 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// strtlower put all alphabet lowercase prevent case mismatch 
$current_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'user';
$isAdmin = ($_SESSION['role'] === 'Admin');

// read all idol data from database  
$query = "SELECT idols.*, GROUP_CONCAT(g.group_name SEPARATOR ', ') AS my_groups 
          FROM idols 
          LEFT JOIN idol_group ig ON idols.id = ig.idol_id
          LEFT JOIN `groups` g ON ig.group_id = g.id 
          GROUP BY idols.id
          ORDER BY idols.id ASC";
$stmt = $db->query($query);
$idols = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Idols - KPOP HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/manage-idols.css">
</head>

<body>
    <div class="container my-4">

        <div class="d-flex mb-4 px-2 justify-content-between align-items-center">
            <a href="dashboard.php" class="btn-action-back small">
                <i class="me-1 bi bi-box-arrow-in-left"></i>Back to Dashboard
            </a>

            <?php if ($isAdmin): ?>
                <a href="./pages/add-idols.php" class="btn-add-idol small">
                    <i class="me-1 bi bi-plus-circle"></i>Add New Idol
                </a>
            <?php endif; ?>
        </div>

        <div class="glass-panel">
            <div class="mb-4">
                <h2 class="mb-1 gradient-title"><i class="me-2 bi bi-person-badge-fill"></i>Idol Directory</h2>
            </div>

            <div class="table-responsive">
                <table class="mb-0 table table-glass align-middle">
                    <thead>
                        <tr>
                            <th style="min-width: 60px;">ID</th>
                            <th style="min-width: 140px;">Name</th>
                            <th style="min-width: 120px;">Stage Name</th>
                            <th style="min-width: 110px;">D.O.B</th>
                            <th style="min-width: 150px;">Groups</th> 
                            <?php if ($isAdmin): ?>
                                <th style="min-width: 120px; text-align: center;">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($idols as $idol): ?>
                            <tr>
                                <td><span class="custom-id"><?= $idol['id'] ?></span></td>
                                <td><span class="custom-name"><?= $idol['name'] ?></span></td>
                                <td><span class="custom-stage-name"><i class="me-1 bi bi-stars small"></i><?= $idol['stage_name'] ?></span></td>
                                <td><span class="custom-dob"><i class="me-1 bi bi-calendar-event small"></i><?= $idol['dob'] ?></span></td>
                                <td>
                                    <!-- status verify statement (if-else statement ) -->
                                    <?php if (!empty($idol['my_groups'])): ?>
                                        <span class="custom-groups-row"><i class="me-1 bi bi-people-fill small"></i><?= $idol['my_groups'] ?></span>
                                    <?php else: ?>
                                        <span class="text-white-50 small"><i class="me-1 bi bi-person small"></i>Soloist</span>
                                    <?php endif; ?>
                                </td>

                                <!-- for admin -->
                                <?php if ($isAdmin): ?>
                                    <td>
                                        <div class="d-flex flex-column flex-md-row gap-1 gap-md-2 justify-content-center align-items-center">
                                            <a href="./pages/edit-idols.php?id=<?= $idol['id'] ?>" class="btn-edit">
                                                <i class="bi bi-pencil-square "></i>
                                            </a>
                                            <a href="./actions/delete-idols.php?id=<?= $idol['id'] ?>"
                                                class="btn-delete"
                                                onclick="return confirm('Sure to Delete Idols?');">
                                                <i class="bi bi-trash3-fill "></i>
                                            </a>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>