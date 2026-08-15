<?php
// DRY Principle
require '../includes/header.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = $_SESSION['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (!empty($username) && !empty($email)) {

        $query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $db->prepare($query);

        if ($stmt->execute([$username, $email, $user_id])) {

            // after renew the database ,renew the session username too
            $_SESSION['username'] = $username;

            // update sucessfully will redirect to dashboard.php
            header("Location:../dashboard.php");
            exit;
        } else {
            echo "Database update failed.";
        }
    } else {
        echo "Please fill in all fields.";
    }
} else {
    header("Location: ../dashboard.php");
    exit;
}
?> 