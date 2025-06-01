<?php
session_start();
require_once '../config/database.php';

// Oturum kontrolü
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $description = trim($_POST['description']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Resim yükleme işlemi
    $image_name = null;
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            // Uploads klasörünü oluştur
            if(!file_exists('../uploads/foods')) {
                mkdir('../uploads/foods', 0777, true);
            }
            
            $image_name = uniqid() . '.' . $ext;
            $target_path = '../uploads/foods/' . $image_name;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Resim başarıyla yüklendi
            } else {
                header('Location: foods.php?message=image_error');
                exit();
            }
        }
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO foods (name, category_id, price, description, image, status) 
            VALUES (:name, :category_id, :price, :description, :image, :status)
        ");
        
        $stmt->execute([
            'name' => $name,
            'category_id' => $category_id,
            'price' => $price,
            'description' => $description,
            'image' => $image_name,
            'status' => $status
        ]);
        
        header('Location: foods.php?message=added');
        exit();
    } catch(PDOException $e) {
        header('Location: foods.php?message=error');
        exit();
    }
}

header('Location: foods.php');
exit(); 