<?php
session_start();
require_once '../config/database.php';

// Oturum kontrolü
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_category'])) {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    try {
        $stmt = $db->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $category_id]);
        header('Location: categories.php?message=updated');
        exit();
    } catch(PDOException $e) {
        header('Location: categories.php?message=error');
        exit();
    }
}

header('Location: categories.php');
exit(); 