<?php
require_once 'config/database.php';
require_once 'includes/header.php';

// Sepet boşsa yönlendir
if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: menu.php');
    exit;
}
?>

<div class="container py-5">
    <h2 class="mb-4">Sepetim</h2>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Fiyat</th>
                                    <th>Miktar</th>
                                    <th>Toplam</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                foreach($_SESSION['cart'] as $item): 
                                    $subtotal = $item['price'] * $item['quantity'];
                                    $total += $subtotal;
                                ?>
                                <tr id="cart-row-<?php echo $item['id']; ?>">
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo number_format($item['price'], 2); ?> ₺</td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm quantity-input" 
                                               value="<?php echo $item['quantity']; ?>" min="1" max="10"
                                               onchange="updateCart(<?php echo $item['id']; ?>, this.value)"
                                               style="width: 80px">
                                    </td>
                                    <td class="subtotal"><?php echo number_format($subtotal, 2); ?> ₺</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sipariş Özeti</h5>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Toplam Tutar:</span>
                        <span class="h5" id="cart-total"><?php echo number_format($total, 2); ?> ₺</span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary w-100">Siparişi Tamamla</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateCart(foodId, quantity) {
    fetch('ajax/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'food_id=' + foodId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Sepet sayısını güncelle
            updateCartCount(data.total_items);
            // Toplam tutarı güncelle
            document.getElementById('cart-total').textContent = data.total + ' ₺';
        } else {
            alert(data.message || 'Bir hata oluştu');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
}

function removeFromCart(foodId) {
    if(!confirm('Bu ürünü sepetten silmek istediğinize emin misiniz?')) {
        return;
    }
    
    fetch('ajax/remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'food_id=' + foodId
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Ürün satırını kaldır
            document.getElementById('cart-row-' + foodId).remove();
            // Sepet sayısını güncelle
            updateCartCount(data.total_items);
            // Toplam tutarı güncelle
            document.getElementById('cart-total').textContent = data.total + ' ₺';
            
            // Sepet boşsa sayfayı yenile
            if(data.total_items === 0) {
                window.location.reload();
            }
        } else {
            alert(data.message || 'Bir hata oluştu');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
}

function updateCartCount(count) {
    const cartCount = document.querySelector('.cart-count');
    if(cartCount) {
        cartCount.textContent = count;
        cartCount.style.display = count > 0 ? 'inline' : 'none';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?> 