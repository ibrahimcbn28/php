<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';

$order = null;
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    
    if($order_id && $phone) {
        $stmt = $db->prepare("
            SELECT o.*, 
                   GROUP_CONCAT(CONCAT(f.name, ' x', od.quantity) SEPARATOR ', ') as order_details
            FROM orders o
            LEFT JOIN order_details od ON o.id = od.order_id
            LEFT JOIN foods f ON od.food_id = f.id
            WHERE o.id = ? AND o.customer_phone = ?
            GROUP BY o.id
        ");
        $stmt->execute([$order_id, $phone]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$order) {
            $error = 'Sipariş bulunamadı. Lütfen bilgilerinizi kontrol edin.';
        }
    } else {
        $error = 'Lütfen tüm alanları doldurun.';
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Sipariş Takibi</h2>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if(!$order): ?>
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Sipariş Numarası</label>
                                <input type="number" name="order_id" class="form-control" required
                                       value="<?php echo $order_id; ?>"
                                       placeholder="Örn: 12345">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Telefon Numarası</label>
                                <input type="tel" name="phone" class="form-control" required
                                       value="<?php echo htmlspecialchars($phone); ?>"
                                       placeholder="Siparişte belirttiğiniz telefon">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Siparişi Sorgula</button>
                        </form>
                    <?php else: ?>
                        <div class="text-center mb-4">
                            <div class="h1 mb-3">
                                <?php
                                $icon = match($order['status']) {
                                    'pending' => '<i class="bi bi-clock text-warning"></i>',
                                    'preparing' => '<i class="bi bi-gear-fill text-info"></i>',
                                    'on_way' => '<i class="bi bi-truck text-primary"></i>',
                                    'delivered' => '<i class="bi bi-check-circle-fill text-success"></i>',
                                    'cancelled' => '<i class="bi bi-x-circle-fill text-danger"></i>',
                                    default => '<i class="bi bi-question-circle text-secondary"></i>'
                                };
                                echo $icon;
                                ?>
                            </div>
                            
                            <h3>
                                <?php
                                echo match($order['status']) {
                                    'pending' => 'Sipariş Alındı',
                                    'preparing' => 'Hazırlanıyor',
                                    'on_way' => 'Yolda',
                                    'delivered' => 'Teslim Edildi',
                                    'cancelled' => 'İptal Edildi',
                                    default => 'Bilinmiyor'
                                };
                                ?>
                            </h3>
                        </div>
                        
                        <!-- Sipariş Durumu Timeline -->
                        <div class="position-relative mb-4">
                            <div class="progress" style="height: 2px;">
                                <div class="progress-bar" role="progressbar" style="width: <?php
                                    echo match($order['status']) {
                                        'pending' => '25%',
                                        'preparing' => '50%',
                                        'on_way' => '75%',
                                        'delivered' => '100%',
                                        default => '0%'
                                    };
                                ?>"></div>
                            </div>
                            
                            <div class="position-absolute w-100" style="top: -10px;">
                                <div class="row text-center">
                                    <div class="col">
                                        <div class="rounded-circle bg-<?php echo $order['status'] == 'pending' ? 'primary' : ($order['status'] == 'cancelled' ? 'danger' : 'secondary'); ?>" 
                                             style="width: 20px; height: 20px; margin: 0 auto;"></div>
                                        <small class="d-block mt-1">Alındı</small>
                                    </div>
                                    <div class="col">
                                        <div class="rounded-circle bg-<?php echo $order['status'] == 'preparing' ? 'primary' : ($order['status'] == 'cancelled' ? 'danger' : 'secondary'); ?>" 
                                             style="width: 20px; height: 20px; margin: 0 auto;"></div>
                                        <small class="d-block mt-1">Hazırlanıyor</small>
                                    </div>
                                    <div class="col">
                                        <div class="rounded-circle bg-<?php echo $order['status'] == 'on_way' ? 'primary' : ($order['status'] == 'cancelled' ? 'danger' : 'secondary'); ?>" 
                                             style="width: 20px; height: 20px; margin: 0 auto;"></div>
                                        <small class="d-block mt-1">Yolda</small>
                                    </div>
                                    <div class="col">
                                        <div class="rounded-circle bg-<?php echo $order['status'] == 'delivered' ? 'success' : ($order['status'] == 'cancelled' ? 'danger' : 'secondary'); ?>" 
                                             style="width: 20px; height: 20px; margin: 0 auto;"></div>
                                        <small class="d-block mt-1">Teslim Edildi</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Sipariş Detayları</h5>
                                <p class="mb-1">
                                    <strong>Sipariş No:</strong> #<?php echo $order['id']; ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Tarih:</strong> <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Toplam Tutar:</strong> <?php echo number_format($order['total_amount'], 2); ?> ₺
                                </p>
                                <p class="mb-1">
                                    <strong>Ödeme Yöntemi:</strong> 
                                    <?php echo $order['payment_method'] == 'cash' ? 'Kapıda Ödeme' : 'Kredi Kartı'; ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Ürünler:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($order['order_details'])); ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Teslimat Bilgileri</h5>
                                <p class="mb-1">
                                    <strong>Ad Soyad:</strong> <?php echo htmlspecialchars($order['customer_name']); ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Telefon:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Adres:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="track-order.php" class="btn btn-outline-primary">Başka Sipariş Sorgula</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form doğrulama
(function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if(!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?> 