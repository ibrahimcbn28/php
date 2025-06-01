<?php
require_once 'config/database.php';
require_once 'includes/header.php';

// Filtreleme parametreleri
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// SQL sorgusu oluştur
$sql = "
    SELECT f.*, c.name as category_name 
    FROM foods f 
    LEFT JOIN categories c ON f.category_id = c.id 
    WHERE f.status = 1
";

$params = [];

if($category_id > 0) {
    $sql .= " AND f.category_id = ?";
    $params[] = $category_id;
}

if($search) {
    $sql .= " AND (f.name LIKE ? OR f.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY f.name";

// Yemekleri getir
$stmt = $db->prepare($sql);
$stmt->execute($params);
$foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kategorileri getir
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Arama ve Filtreleme -->
<div class="bg-light py-3">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" 
                           placeholder="Yemek ara..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Ara</button>
                </form>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-md-end mt-3 mt-md-0">
                    <a href="menu.php" class="btn btn-outline-primary me-2">Tümü</a>
                    <?php foreach($categories as $category): ?>
                        <a href="menu.php?category=<?php echo $category['id']; ?>" 
                           class="btn <?php echo $category_id == $category['id'] ? 'btn-primary' : 'btn-outline-primary'; ?> me-2">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Menü Listesi -->
<div class="container py-5">
    <h2 class="mb-4">Menümüz</h2>
    
    <?php if(empty($foods)): ?>
        <div class="alert alert-info">
            Aradığınız kriterlere uygun yemek bulunamadı.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($foods as $food): ?>
            <div class="col-md-4 mb-4">
                <div class="card food-card h-100">
                    <?php if($food['image']): ?>
                        <img src="assets/img/<?php echo $food['image']; ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($food['name']); ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <img src="assets/img/no-image.jpg" class="card-img-top" alt="No Image"
                             style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($food['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($food['category_name']); ?></p>
                        <p class="card-text"><?php echo htmlspecialchars($food['description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0"><?php echo number_format($food['price'], 2); ?> ₺</span>
                            <div class="input-group" style="width: 150px;">
                                <input type="number" class="form-control" id="quantity_<?php echo $food['id']; ?>" 
                                       value="1" min="1" max="10">
                                <button class="btn btn-primary" onclick="addToCart(<?php echo $food['id']; ?>)">
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function addToCart(foodId) {
    const quantity = document.getElementById('quantity_' + foodId).value;
    
    fetch('ajax/add_to_cart.php', {
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
            const cartCount = document.querySelector('.cart-count');
            if(cartCount) {
                cartCount.textContent = data.total_items;
                cartCount.style.display = data.total_items > 0 ? 'inline' : 'none';
            }
            
            alert('Ürün sepete eklendi!');
        } else {
            alert(data.message || 'Bir hata oluştu');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?> 