<?php
session_start();
require_once 'config/database.php';
require_once 'includes/header.php';

// Sepet kontrolü
if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: menu.php');
    exit;
}

// Sipariş ID kontrolü
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if(!$order_id) {
    header('Location: checkout.php');
    exit;
}

try {
    // iyzipay yapılandırmasını yükle
    $options = require_once 'config/iyzipay.php';
    
    // POST işlemi kontrolü
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Form verilerini al
        $cardHolderName = $_POST['cardHolderName'] ?? '';
        $cardNumber = $_POST['cardNumber'] ?? '';
        $expireMonth = $_POST['expireMonth'] ?? '';
        $expireYear = $_POST['expireYear'] ?? '';
        $cvc = $_POST['cvc'] ?? '';
        
        // Form doğrulama
        if(empty($cardHolderName) || empty($cardNumber) || empty($expireMonth) || 
           empty($expireYear) || empty($cvc)) {
            throw new Exception('Lütfen tüm kart bilgilerini doldurun.');
        }
        
        // Kart numarası formatı kontrolü
        if(!preg_match('/^[0-9]{16}$/', $cardNumber)) {
            throw new Exception('Geçersiz kart numarası formatı.');
        }
        
        // Son kullanma tarihi kontrolü
        if(!preg_match('/^[0-9]{2}$/', $expireMonth) || !preg_match('/^[0-9]{2}$/', $expireYear)) {
            throw new Exception('Geçersiz son kullanma tarihi formatı.');
        }
        
        // CVC kontrolü
        if(!preg_match('/^[0-9]{3}$/', $cvc)) {
            throw new Exception('Geçersiz CVC formatı.');
        }
        
        // Sipariş bilgilerini al
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$order) {
            throw new Exception('Sipariş bulunamadı.');
        }

        // Test modu kontrolü
        if($options->getApiKey() === null) {
            // Test modu - ödemeyi başarılı kabul et
            $payment_successful = true;
        } else {
            // Gerçek ödeme işlemi burada yapılacak
            $payment_successful = true; // Şimdilik hep başarılı kabul ediyoruz
        }

        if($payment_successful) {
            // Siparişi güncelle
            $stmt = $db->prepare("UPDATE orders SET payment_status = 'paid', status = 'preparing' WHERE id = ?");
            $stmt->execute([$order_id]);
            
            // Sepeti temizle
            unset($_SESSION['cart']);
            
            // Başarılı sayfasına yönlendir
            $_SESSION['success_message'] = 'Ödeme başarıyla tamamlandı! Siparişiniz hazırlanıyor.';
            header('Location: order-success.php?order_id=' . $order_id);
            exit;
        } else {
            throw new Exception('Ödeme işlemi başarısız oldu. Lütfen tekrar deneyin.');
        }
    }
    
} catch(Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Ödeme Bilgileri</h2>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="payment-form" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Kart Üzerindeki İsim</label>
                                <input type="text" name="cardHolderName" class="form-control" required
                                       pattern="[A-Za-z\s]+" title="Sadece harf ve boşluk kullanın">
                                <div class="invalid-feedback">Lütfen kartın üzerindeki ismi girin</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Kart Numarası</label>
                                <input type="text" name="cardNumber" class="form-control" required
                                       pattern="[0-9]{16}" maxlength="16" placeholder="1234 5678 9012 3456">
                                <div class="invalid-feedback">Geçerli bir kart numarası girin</div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Son Kullanma Ay</label>
                                    <input type="text" name="expireMonth" class="form-control" required
                                           pattern="[0-9]{2}" maxlength="2" placeholder="MM">
                                    <div class="invalid-feedback">Geçerli bir ay girin (01-12)</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Son Kullanma Yıl</label>
                                    <input type="text" name="expireYear" class="form-control" required
                                           pattern="[0-9]{2}" maxlength="2" placeholder="YY">
                                    <div class="invalid-feedback">Geçerli bir yıl girin</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">CVC</label>
                                    <input type="text" name="cvc" class="form-control" required
                                           pattern="[0-9]{3}" maxlength="3" placeholder="123">
                                    <div class="invalid-feedback">Geçerli bir CVC girin</div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-credit-card"></i> Ödemeyi Tamamla
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="checkout.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Sipariş Bilgilerine Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Form doğrulama
    (function() {
        'use strict';
        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            if(!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    })();

    // Kart numarası formatı
    document.querySelector('[name="cardNumber"]').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
    });

    // Son kullanma tarihi formatı
    document.querySelectorAll('[name="expireMonth"], [name="expireYear"]').forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });
    });

    // CVC formatı
    document.querySelector('[name="cvc"]').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
    });
    </script>
</body>
</html> 