<?php
require '../includes/header.php';

// check user status (login or not )
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: manage-albums.php');
    exit;
}

// get the full album data 
$stmt = $db->prepare("SELECT * FROM albums WHERE id = :id");
$stmt->execute([':id' => $id]);
$album = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$album) {
    echo "<script>alert('Album not found!'); window.location.href='../manage-albums.php';</script>";
    exit;
}

// fetch all group name for dropdown to select 
$groups_stmt = $db->query("SELECT id, group_name FROM `groups` ORDER BY group_name ASC");
$groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';

// format the song data in database 
// format and cleaning the song data 
$raw_songs = !empty($album['songs']) ? preg_split('/[\r\n,]+/', $album['songs']) : [];
// cut the spacing and filter the empty line for sogns
$tracklist = array_filter(array_map('trim', $raw_songs));

// convert to textarea and let one songs one line 
$songs_for_textarea = implode("\n", $tracklist);


// only admin and manager can edit album 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') {
        echo "<script>alert('Unauthorized action!'); window.location.href='../manage-albums.php';</script>";
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $group_id = $_POST['group_id'] ?? null;
    $release_date = trim($_POST['release_date'] ?? '');
    
    // get the songs data 
    $songs_input = $_POST['songs'] ?? '';
    // split the song input by preg_split
    $submitted_songs = preg_split('/[\r\n]+/', $songs_input);
    // clear the spacing 
    $cleaned_songs = array_filter(array_map('trim', $submitted_songs));
    // use comma to save the songs to prevent the damage of database structure 
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
            ':songs' => $songs_to_save, // write in the data after formatted 
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
    <link rel="stylesheet" href="../css/add-idols.css">
</head>
<body>
    <div class="form-container" style="max-width: 550px;">
        
        <h2 class="form-title" style="background: linear-gradient(to right, #a78bfa, #ff6b6b); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
            <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager'): ?>
                <i class="me-2 bi bi-pencil-square"></i>Edit Album
            <?php else: ?>
                <i class="me-2 bi bi-disc-fill"></i>Album Details
            <?php endif; ?>
        </h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="border-radius: 12px; background-color: #ef444422; color: #f87171; border: 1px solid #ef444444;">
                <i class="me-2 bi bi-exclamation-triangle-fill"></i><?= $error ?>
            </div>
        <?php endif; ?>

        <form action="edit-album.php?id=<?= $id ?>" method="POST">
            
            <!-- album name  -->
            <div class="mb-4">
                <label for="name" class="form-label">Album Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?= $album['name'] ?>" 
                       <?= ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') ? 'readonly' : '' ?> required>
            </div>

            <!-- group  -->
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

            <!-- release date -->
            <div class="mb-4">
                <label for="release_date" class="form-label">Release Date</label>
                <input type="date" class="form-control" id="release_date" name="release_date" 
                       value="<?= $album['release_date'] ?>" 
                       <?= ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') ? 'readonly' : '' ?> required>
            </div>

            <!-- songs input  -->
            <div class="mb-4">
                <label for="songs" class="form-label"><i class="me-1 text-info bi bi-music-note-list"></i> Songs / Tracklist</label>
                
                <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager'): ?>
                    <!-- for admin see is textarea which can edit the songs  -->
                    <textarea class="form-control" id="songs" name="songs" rows="6" 
                              placeholder="Type one song per line...&#10;Example:&#10;Song A&#10;Song B&#10;Song C" 
                              style="line-height: 1.6; resize: vertical; min-height: 120px;"><?= $songs_for_textarea ?></textarea>
                    <div class="mt-1 form-text text-white-50 small">
                        <i class="me-1 bi bi-info-circle"></i>Press <b>Enter</b> to start a new song line.
                    </div>
                <?php else: ?>
                    <!-- for user is only can see the list of song which cannot edit -->
                    <div class="p-3" style="background: rgba(17, 24, 39, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px;">
                        <?php if (empty($tracklist)): ?>
                            <div class="py-2 text-white-50 text-center small">No tracks in this album.</div>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-2">
                                <?php $index = 1; foreach ($tracklist as $song_title): ?>
                                    <div class="d-flex py-2 px-3 align-items-center justify-content-between" 
                                         style="background: rgba(255, 255, 255, 0.03); border-radius: 10px; ">
                                        <div class="d-flex gap-3 align-items-center">
                                            <span class="text-white-50 small" style="font-family: monospace; width: 20px;">
                                                <?= str_pad($index++, 2, '0', STR_PAD_LEFT) ?>
                                            </span>
                                            <span class="text-white" style="font-size: 15px; font-weight: 500;">
                                                <?= $song_title ?>
                                            </span>
                                        </div>
                                        <i class="text-white-50 bi bi-play-circle-fill" style="font-size: 14px;"></i>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- only admin and manager have save button  -->
            <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager'): ?>
                <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #725ac1, #a78bfa); color: white; margin-top: 10px;">
                    <i class="me-2 bi bi-save-fill"></i>Save Changes
                </button>
            <?php endif; ?>
        </form>
        
        <a href="../manage-albums.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Albums
        </a>
    </div>
</body>
</html>