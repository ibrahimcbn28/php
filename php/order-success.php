<?php
session_start();
require_once 'config/database.php';
require_once 'includes/header.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if(!$order_id) {
    header('Location: menu.php');
    exit;
}

// Siparişi getir
$stmt = $db->prepare("
    SELECT o.*, 
           GROUP_CONCAT(CONCAT(f.name, ' x', od.quantity) SEPARATOR ', ') as order_details
    FROM orders o
    LEFT JOIN order_details od ON o.id = od.order_id
    LEFT JOIN foods f ON od.food_id = f.id
    WHERE o.id = ?
    GROUP BY o.id
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    header('Location: menu.php');
    exit;
}

// Başarı mesajını kontrol et
$success_message = $_SESSION['success_message'] ?? 'Siparişiniz başarıyla alındı!';
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Başarılı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <h1 class="card-title text-success mb-4">
                            <i class="bi bi-check-circle-fill"></i> Teşekkürler!
                        </h1>
                        
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                        
                        <h5 class="mb-4">Sipariş Numaranız: #<?php echo $order_id; ?></h5>
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Sipariş Detayları</h5>
                                <p class="card-text"><?php echo htmlspecialchars($order['order_details']); ?></p>
                                <p class="card-text">
                                    <strong>Toplam Tutar:</strong> 
                                    <?php echo number_format($order['total_amount'], 2); ?> ₺
                                </p>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Teslimat Bilgileri</h5>
                                <p class="card-text">
                                    <strong>Ad Soyad:</strong> <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                    <strong>Telefon:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?><br>
                                    <strong>E-posta:</strong> <?php echo htmlspecialchars($order['customer_email']); ?><br>
                                    <strong>Adres:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?>
                                </p>
                            </div>
                        </div>
                        
                        <a href="track-order.php?order_id=<?php echo $order_id; ?>" class="btn btn-primary">
                            Siparişimi Takip Et
                        </a>
                        
                        <a href="menu.php" class="btn btn-outline-primary">
                            Menüye Dön
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 