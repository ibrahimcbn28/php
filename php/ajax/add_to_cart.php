<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

$food_id = isset($_POST['food_id']) ? (int)$_POST['food_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if($food_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ürün']);
    exit;
}

try {
    // Yemeği veritabanından al
    $stmt = $db->prepare("SELECT * FROM foods WHERE id = ? AND status = 1");
    $stmt->execute([$food_id]);
    $food = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$food) {
        echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı']);
        exit;
    }

    // Sepeti başlat
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Ürün zaten sepette var mı kontrol et
    if(isset($_SESSION['cart'][$food_id])) {
        $_SESSION['cart'][$food_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$food_id] = [
            'id' => $food_id,
            'name' => $food['name'],
            'price' => $food['price'],
            'quantity' => $quantity
        ];
    }

    // Sepetteki toplam ürün sayısını hesapla
    $total_items = 0;
    foreach($_SESSION['cart'] as $item) {
        $total_items += $item['quantity'];
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Ürün sepete eklendi',
        'total_items' => $total_items
    ]);

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Bir hata oluştu']);
} 