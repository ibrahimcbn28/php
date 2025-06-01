<?php
require_once 'config/database.php';
require_once 'includes/header.php';

// Popüler yemekleri getir
$stmt = $db->query("
    SELECT f.*, c.name as category_name 
    FROM foods f 
    LEFT JOIN categories c ON f.category_id = c.id 
    WHERE f.status = 1 
    ORDER BY RAND() 
    LIMIT 6
");
$popular_foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kategorileri getir
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4">Lezzetli Yemekler</h1>
        <p class="lead">En sevdiğiniz yemekler kapınıza kadar gelsin!</p>
        <a href="menu.php" class="btn btn-primary btn-lg">Menüyü Görüntüle</a>
    </div>
</section>

<!-- Popüler Yemekler -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Popüler Yemekler</h2>
        <div class="row">
            <?php foreach($popular_foods as $food): ?>
            <div class="col-md-4 mb-4">
                <div class="card food-card h-100">
                    <?php if($food['image']): ?>
                        <img src="uploads/foods/<?php echo $food['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($food['name']); ?>" style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <img src="assets/img/no-image.jpg" class="card-img-top" alt="No Image" style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($food['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($food['category_name']); ?></p>
                        <p class="card-text"><?php echo htmlspecialchars(substr($food['description'], 0, 100)) . '...'; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0"><?php echo number_format($food['price'], 2); ?> ₺</span>
                            <button onclick="addToCart(<?php echo $food['id']; ?>)" class="btn btn-primary">
                                <i class="bi bi-cart-plus"></i> Sepete Ekle
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Kategoriler -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Kategoriler</h2>
        <div class="row">
            <?php foreach($categories as $category): ?>
            <div class="col-md-4 mb-4">
                <a href="menu.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                    <div class="card food-card h-100">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($category['description']); ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Neden Biz -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Neden Bizi Tercih Etmelisiniz?</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="bi bi-clock display-4 text-primary"></i>
                    <h4 class="mt-3">Hızlı Teslimat</h4>
                    <p>30 dakika içinde kapınızda!</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="bi bi-heart display-4 text-primary"></i>
                    <h4 class="mt-3">Taze Malzemeler</h4>
                    <p>Her gün taze ve kaliteli malzemeler</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="bi bi-shield-check display-4 text-primary"></i>
                    <h4 class="mt-3">Hijyenik Ortam</h4>
                    <p>En yüksek hijyen standartları</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function addToCart(foodId) {
    // AJAX ile sepete ekleme işlemi yapılacak
    alert('Sepete ekleme özelliği yakında eklenecek!');
}
</script>

<?php require_once 'includes/footer.php'; ?> 