<?php
session_start();
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

$food_id = isset($_POST['food_id']) ? (int)$_POST['food_id'] : 0;

if($food_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ürün']);
    exit;
}

if(!isset($_SESSION['cart'][$food_id])) {
    echo json_encode(['success' => false, 'message' => 'Ürün sepette bulunamadı']);
    exit;
}

// Ürünü sepetten sil
unset($_SESSION['cart'][$food_id]);

// Toplam tutarı hesapla
$total = 0;
$total_items = 0;
foreach($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
    $total_items += $item['quantity'];
}

echo json_encode([
    'success' => true,
    'message' => 'Ürün sepetten silindi',
    'total' => number_format($total, 2),
    'total_items' => $total_items
]); 