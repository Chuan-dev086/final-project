<?php
//DRY principle
require 'header.php';

// verify status if no login redirect to login form 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// if not admin redirect to manage idol page 
if ($_SESSION['role'] !== 'Admin') {
    echo "<script>alert('Unauthorized access!'); window.location.href='manage-idols.php';</script>";
    exit;
}
// if ID exist get ID  if not put id as null string 
$id = $_GET['id'] ?? '';

//  sql delete query  
if (!empty($id)) {
    $query = 'DELETE FROM idols WHERE id = :id';
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id]);
}

header('Location: manage-idols.php');
exit;
?>