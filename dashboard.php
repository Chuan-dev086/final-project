<?php
require 'header.php';

// check login
if (!isset($_SESSION['user_id'])) {
    header('Location: login-form.php');
    exit;
}
$username = $_SESSION['username'] ?? 'User';
$current_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'user';

// select how many idols for database  
$stmtIdols = $db->query("SELECT COUNT(*) FROM idols");
$totalIdols = $stmtIdols->fetchColumn();

// search how many group from database 
$stmtGroups = $db->query("SELECT COUNT(*) FROM groups");
$totalGroups = $stmtGroups->fetchColumn();

// 3. search how many album from database 
$stmtAlbums = $db->query("SELECT COUNT(*) FROM albums");
$totalAlbums = $stmtAlbums->fetchColumn();

// --- 🌟 新增：如果是 Admin，顺便查询注册用户总数 🌟 ---
$totalUsers = 0;
if ($current_role === 'admin') {
    $stmtUsers = $db->query("SELECT COUNT(*) FROM users"); // 💡 假设你的用户表名叫 users
    $totalUsers = $stmtUsers->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPOP Idol Management Dashboard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- link CSS  -->
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <div class="container py-3 py-md-4">
        <!-- navbar -->
        <header class="row">
            <div class="col-12">
                <div class="glass-navbar d-flex  flex-column flex-sm-row justify-content-between align-items-center gap-3 gap-sm-0">
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-bold tracking-wider logo-text">K-POP SYSTEM</span>
                        <?php switch ($current_role):

                            case 'admin': ?>
                                <span class="role-badge badge-admin">
                                    <i class="bi bi-shield-fill-check me-1"></i>Admin
                                </span>
                                <?php break; ?>

                            <?php
                            case 'manager':
                            ?>
                                <span class="role-badge badge-manager">
                                    <i class="bi bi-person-workspace me-1"></i>Manager
                                </span>
                                <?php break; ?>

                            <?php
                            default:
                            ?>
                                <span class="role-badge badge-user">
                                    <i class="bi bi-person-fill me-1"></i>User
                                </span>
                                <?php break; ?>

                        <?php endswitch; ?>

                    </div>
                    <div class="d-flex align-items-center gap-3 justify-content-center ">
                        <a href="change-password.php" class="btn-nav-settings me-3">
                            <i class="bi bi-key me-1"></i>Change Password
                        </a>
                        <a href="logout.php" onclick="return confirm('Confirm logout?')" class="btn-nav-logout">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- welcome card  -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <div class="welcome-card">
                    <h1 class="welcome-title mb-2">
                        Welcome Back, <span class="gradient-text"><?= $username ?></span>
                    </h1>
                </div>
            </div>
        </div>

        <!-- content card  -->
        <div class="row g-3 g-md-4">

            <!-- idols -->
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="dashboard-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="card-icon mb-2">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <div class="card-number"><?= $totalIdols ?></div>
                        <div class="card-title">Total Idols</div>
                    </div>
                    <a href="manage-idols.php" class="action-link">
                        <span>Manage Idols</span> <i class="bi bi-arrow-right-short fs-5"></i>
                    </a>
                </div>
            </div>

            <!-- 卡片 2：Groups 控制 -->
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="dashboard-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="card-icon mb-2">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="card-number"><?= $totalGroups ?></div>
                        <div class="card-title">Active Groups</div>
                    </div>
                    <a href="manage-groups.php" class="action-link">
                        <span>Manage Groups</span> <i class="bi bi-arrow-right-short fs-5"></i>
                    </a>
                </div>
            </div>

            <!-- 卡片 3：Albums 控制 -->
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="dashboard-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="card-icon mb-2">
                            <i class="bi bi-disc-fill"></i>
                        </div>
                        <div class="card-number"><?= $totalAlbums ?></div>
                        <div class="card-title">Albums Released</div>
                    </div>
                    <a href="manage-albums.php" class="action-link">
                        <span>Manage Albums</span> <i class="bi bi-arrow-right-short fs-5"></i>
                    </a>
                </div>
            </div>

            <?php if ($current_role === 'admin'): ?>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="dashboard-card h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="card-icon mb-2">
                                <i class="bi bi-person-gear"></i>
                            </div>
                            <div class="card-number"><?= $totalUsers ?></div>
                            <div class="card-title">System Users</div>
                        </div>
                        <a href="manage-users.php" class="action-link">
                            <span>Manage Users</span> <i class="bi bi-arrow-right-short fs-5"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>