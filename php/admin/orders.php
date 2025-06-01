<?php
require_once '../config/database.php';
require_once 'includes/auth_check.php';
require_once 'includes/header.php';
require_once '../includes/mail.php';

// Filtreleme parametreleri
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// SQL sorgusu oluştur
$sql = "
    SELECT o.*, 
           COUNT(od.id) as total_items,
           GROUP_CONCAT(CONCAT(f.name, ' x', od.quantity) SEPARATOR ', ') as order_details
    FROM orders o
    LEFT JOIN order_details od ON o.id = od.order_id
    LEFT JOIN foods f ON od.food_id = f.id
";

$where = [];
$params = [];

if($status) {
    $where[] = "o.status = ?";
    $params[] = $status;
}

if($search) {
    $where[] = "(o.customer_name LIKE ? OR o.customer_phone LIKE ? OR o.id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if(!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sipariş durumu güncelleme
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    try {
        $db->beginTransaction();
        
        // Siparişi güncelle
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        
        // Sipariş bilgilerini al
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // E-posta gönder
        if($order && in_array($new_status, ['preparing', 'on_way', 'delivered', 'cancelled'])) {
            sendOrderStatusEmail($order, $new_status);
        }
        
        $db->commit();
        
        header("Location: orders.php" . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
        exit;
        
    } catch(PDOException $e) {
        $db->rollBack();
        $error = "Bir hata oluştu, lütfen tekrar deneyin.";
    }
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Siparişler</h2>
        
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" 
                   placeholder="Sipariş ara..." value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="status" class="form-select" style="width: 150px;">
                <option value="">Tüm Durumlar</option>
                <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Bekliyor</option>
                <option value="preparing" <?php echo $status == 'preparing' ? 'selected' : ''; ?>>Hazırlanıyor</option>
                <option value="on_way" <?php echo $status == 'on_way' ? 'selected' : ''; ?>>Yolda</option>
                <option value="delivered" <?php echo $status == 'delivered' ? 'selected' : ''; ?>>Teslim Edildi</option>
                <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>İptal Edildi</option>
            </select>
            
            <button type="submit" class="btn btn-primary">Filtrele</button>
            <?php if($search || $status): ?>
                <a href="orders.php" class="btn btn-secondary">Sıfırla</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Sipariş No</th>
                            <th>Müşteri</th>
                            <th>İletişim</th>
                            <th>Ürünler</th>
                            <th>Toplam</th>
                            <th>Tarih</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>
                                    Tel: <?php echo htmlspecialchars($order['customer_phone']); ?><br>
                                    E-posta: <?php echo htmlspecialchars($order['customer_email']); ?>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($order['order_details']); ?></small>
                                </td>
                                <td><?php echo number_format($order['total_amount'], 2); ?> ₺</td>
                                <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo match($order['status']) {
                                            'pending' => 'warning',
                                            'preparing' => 'info',
                                            'on_way' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php 
                                        echo match($order['status']) {
                                            'pending' => 'Bekliyor',
                                            'preparing' => 'Hazırlanıyor',
                                            'on_way' => 'Yolda',
                                            'delivered' => 'Teslim Edildi',
                                            'cancelled' => 'İptal Edildi',
                                            default => 'Bilinmiyor'
                                        };
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                            Detay
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split"
                                                data-bs-toggle="dropdown">
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php if($order['status'] != 'delivered' && $order['status'] != 'cancelled'): ?>
                                                <li>
                                                    <form method="POST" class="dropdown-item">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <?php if($order['status'] == 'pending'): ?>
                                                            <input type="hidden" name="status" value="preparing">
                                                            <button type="submit" class="btn btn-link text-success p-0">
                                                                Hazırlanıyor
                                                            </button>
                                                        <?php elseif($order['status'] == 'preparing'): ?>
                                                            <input type="hidden" name="status" value="on_way">
                                                            <button type="submit" class="btn btn-link text-info p-0">
                                                                Yola Çıktı
                                                            </button>
                                                        <?php elseif($order['status'] == 'on_way'): ?>
                                                            <input type="hidden" name="status" value="delivered">
                                                            <button type="submit" class="btn btn-link text-success p-0">
                                                                Teslim Edildi
                                                            </button>
                                                        <?php endif; ?>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" class="dropdown-item" 
                                                          onsubmit="return confirm('Siparişi iptal etmek istediğinize emin misiniz?')">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" class="btn btn-link text-danger p-0">
                                                            İptal Et
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Sipariş Detay Modal -->
                            <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Sipariş #<?php echo $order['id']; ?> Detayı</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <strong>Müşteri Bilgileri:</strong>
                                                    <p>
                                                        Ad Soyad: <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                                        Telefon: <?php echo htmlspecialchars($order['customer_phone']); ?><br>
                                                        E-posta: <?php echo htmlspecialchars($order['customer_email']); ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Sipariş Bilgileri:</strong>
                                                    <p>
                                                        Tarih: <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?><br>
                                                        Ödeme: <?php echo $order['payment_method'] == 'cash' ? 'Kapıda Ödeme' : 'Kredi Kartı'; ?><br>
                                                        Tutar: <?php echo number_format($order['total_amount'], 2); ?> ₺
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <strong>Teslimat Adresi:</strong>
                                                <p><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                                            </div>
                                            
                                            <strong>Sipariş Detayı:</strong>
                                            <p><?php echo htmlspecialchars($order['order_details']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if(empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-0">Sipariş bulunamadı</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 