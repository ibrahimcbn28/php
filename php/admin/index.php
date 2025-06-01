<?php
session_start();
require_once '../config/database.php';

// Oturum kontrolü
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// İstatistikleri al
$stats = [];

// Toplam sipariş sayısı
$stmt = $db->query("SELECT COUNT(*) as total FROM orders");
$stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Bekleyen sipariş sayısı
$stmt = $db->query("SELECT COUNT(*) as pending FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];

// Toplam yemek sayısı
$stmt = $db->query("SELECT COUNT(*) as total FROM foods");
$stats['total_foods'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Yemek Sipariş Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Admin Paneli</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="foods.php">Yemekler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">Kategoriler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Siparişler</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Hoş Geldiniz, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></h2>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Toplam Sipariş</h5>
                        <p class="card-text display-4"><?php echo $stats['total_orders']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Bekleyen Sipariş</h5>
                        <p class="card-text display-4"><?php echo $stats['pending_orders']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Toplam Yemek</h5>
                        <p class="card-text display-4"><?php echo $stats['total_foods']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 