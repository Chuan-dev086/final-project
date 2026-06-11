<?php
require 'header.php';

// verify login status 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// verify role (user cannot add album )
if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') {
    echo "<script>alert('Unauthorized access!'); window.location.href='manage-albums.php';</script>";
    exit;
}

// get group detail from DB 
$groups_stmt = $db->query("SELECT id, group_name FROM groups ORDER BY group_name ASC");
$groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);

// let error become a variable (easy for debug )
$error = '';

// post the form detail
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');     // trim can cut the spacing that user type in 
    $group_id = $_POST['group_id'] ?? '';
    $songs = trim($_POST['songs'] ?? ''); 

    // the date detail 
    $year = trim($_POST['release_year'] ?? '');
    $month = trim($_POST['release_month'] ?? '');
    $day = trim($_POST['release_day'] ?? '');

    // verify all the input if empty print out the error 
    if (empty($name) || empty($group_id) || empty($songs) || empty($year) || empty($month) || empty($day)) {
        $error = 'All fields are required! Please select a group and fill in the songs.';
    } else {
        // add the ‘0’ ensure the format of date 
        $month = (int)$month < 10 ? '0' . (int)$month : $month;
        $day   = (int)$day   < 10 ? '0' . (int)$day   : $day;
        $release_date = "$year-$month-$day";

        // get the detail who add album and store it in created_by variable 
        $created_by = $_SESSION['user_id'];

        try {
            // insert the info from the form 
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

            // if success redirect to manage album page
            header('Location: manage-albums.php');
            exit;
        } catch (PDOException $e) {
            // if not success print out the DB error 
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
                <textarea class="form-control" id="songs" name="songs" rows="6" 
                    placeholder="Type one song per line...&#10;Example:&#10;Song A&#10;Song B&#10;Song C"  
                    style="line-height: 1.6; resize: vertical; min-height: 120px;" required></textarea>
                <div class="form-text text-white-50 small mt-1">
                    <i class="bi bi-info-circle me-1"></i>Press <b>Enter</b> to start a new song line.
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Release Date</label>
                <div class="row g-2">
                    <div class="col-4">
                        <!-- date (internal function of PHP ) will get the current year of server 
                         prevent user type year not exist  -->
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