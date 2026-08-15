<?php
//DRY Principle 
require '../includes/header.php';

// role validation and checking (only admin can delete )
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

// get the group ID 
$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $db->beginTransaction(); 

        // delete the idol_group with group_id 
        $clearStmt = $db->prepare("DELETE FROM idol_group WHERE group_id = :id");
        $clearStmt->execute([':id' => $id]);

        // delete the groups ID 
        $stmt = $db->prepare("DELETE FROM `groups` WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $db->commit(); // commit the delete changes when everything is success 
    } catch (PDOException $e) {
        $db->rollBack(); // if one got error eveything will rollback 
    }
}

// after delete redirect to manage-groups.php
header("Location: ../manage-groups.php");
exit; 

?>