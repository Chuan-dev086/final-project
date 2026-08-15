<?php
//DRY principle 
require '../includes/header.php';

// check the user status if don't login redirect to login.php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// role validation (only admin & manager can perform CRUD)
if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') {
    echo "<script>alert('Unauthorized access!'); window.location.href='../manage-albums.php';</script>";
    exit;
}

// get the album ID from the form 
$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // perform the delete query 
        $stmt = $db->prepare("DELETE FROM albums WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        // catch the error 
    }
}

// after delete process redirect to manage-albums.php
header("Location:../manage-albums.php");
exit; 

?> 
