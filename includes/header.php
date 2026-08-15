<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // start the session in all pages 
}

// connect to database with PDO connection 
$db = new PDO("mysql:host=localhost;dbname=kpop_management", "root", "");
?>
<!DOCTYPE html>
<html>
<head>
    <title>K Pop Idol Management System </title>
    <style>
        .form-container { max-width: 300px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; }
        .input-group { margin-bottom: 15px; }
        .input-group input { width: 100%; padding: 8px; box-sizing: border-box; }
        .error { color: red; font-size: 14px; }
    </style>
</head>
<body>