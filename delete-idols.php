<?php
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'Admin') {
    echo "<script>alert('Unauthorized access!'); window.location.href='manage-idols.php';</script>";
    exit;
}

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    $query = 'DELETE FROM idols WHERE id = :id';
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id]);
}

header('Location: manage-idols.php');
exit;
?>