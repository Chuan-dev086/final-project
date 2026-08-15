<?php
require '../includes/header.php';

// role checking only admin can delete the user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo "<script>alert('Unauthorized Action! Admin access required.'); window.location.href='../dashboard.php';</script>";
    exit;
}

// get the user ID that want to delete 
$id = $_GET['id'] ?? null;

if ($id) {
    // if the id that want to delete is same as admin ID will be stop (means that admin cannot delete itself )
    if ($id == $_SESSION['user_id']) { 
        echo "<script>alert('Security Error: You cannot delete your own admin account while logged in!'); window.location.href='../manage-users.php';</script>";
        exit;
    }

    try {
        // perform the delete query
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        // if delete sucess will have alert to inform and redirect to manage-users.php
        echo "<script>alert('User permanently deleted successfully.'); window.location.href='../manage-users.php';</script>";
        exit;
        
    } catch (PDOException $e) {
        // the error if this user link with other data 
        echo "<script>alert('Error: Cannot delete this user. They might be linked to other data.'); window.location.href='../manage-users.php';</script>";
        exit;
    }
}

// if sucess will redirect to manage-users.php
header("Location: manage-users.php");
exit;