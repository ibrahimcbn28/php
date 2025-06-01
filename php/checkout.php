<?php
session_start();
require_once 'config/database.php';
require_once 'includes/header.php';

// Sepet boşsa yönlendir
if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: menu.php');
    exit;
}

$errors = [];
$success = '';

// Form gönderildi mi kontrol et
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form verilerini al ve doğrula
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';
    
    if(empty($name)) $errors[] = "Ad Soyad alanı zorunludur";
    if(empty($phone)) $errors[] = "Telefon alanı zorunludur";
    if(empty($email)) $errors[] = "E-posta alanı zorunludur";
    if(empty($address)) $errors[] = "Adres alanı zorunludur";
    if(empty($payment_method)) $errors[] = "Ödeme yöntemi seçiniz";
    
    if(empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Toplam tutarı hesapla
            $total = 0;
            foreach($_SESSION['cart'] as $item) {
                $total += $item['price'] * $item['quantity'];
            }
            
            // Siparişi kaydet
            $stmt = $db->prepare("
                INSERT INTO orders (customer_name, customer_phone, customer_email, 
                                  delivery_address, total_amount, payment_status, status, 
                                  created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', 'new', NOW())
            ");
            
            $stmt->execute([
                $name, $phone, $email, $address, $total
            ]);
            
            $order_id = $db->lastInsertId();
            
            // Sipariş detaylarını kaydet
            $stmt = $db->prepare("
                INSERT INTO order_details (order_id, food_id, quantity, unit_price)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach($_SESSION['cart'] as $food_id => $item) {
                $stmt->execute([
                    $order_id, $food_id, $item['quantity'], $item['price']
                ]);
            }
            
            $db->commit();
            
            // Ödeme yöntemine göre yönlendir
            if($payment_method == 'credit_card') {
                header("Location: process-payment.php?order_id=" . $order_id);
                exit;
            } else {
                // Kapıda ödeme için
                unset($_SESSION['cart']);
                $_SESSION['success_message'] = 'Siparişiniz başarıyla alındı!';
                header("Location: order-success.php?order_id=" . $order_id);
                exit;
            }
            
        } catch(Exception $e) {
            $db->rollBack();
            $errors[] = "Bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Hata mesajını kontrol et
if(isset($_SESSION['error_message'])) {
    $errors[] = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Başarı mesajını kontrol et
if(isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Tamamla</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Sipariş Bilgileri</h2>
        
        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="name" class="form-label">Ad Soyad</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">Telefon</label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">E-posta</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="address" class="form-label">Teslimat Adresi</label>
                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Ödeme Yöntemi</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" required>
                    <label class="form-check-label" for="credit_card">
                        Kredi Kartı
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash">
                    <label class="form-check-label" for="cash">
                        Kapıda Ödeme
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Siparişi Tamamla</button>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
<?php require_once 'includes/footer.php'; ?> 