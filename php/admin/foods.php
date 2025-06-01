<?php
session_start();
require_once '../config/database.php';

// Oturum kontrolü
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Yemek silme işlemi
if(isset($_POST['delete_food'])) {
    $food_id = (int)$_POST['food_id'];
    $stmt = $db->prepare("DELETE FROM foods WHERE id = ?");
    $stmt->execute([$food_id]);
    header('Location: foods.php?message=deleted');
    exit();
}

// Yemekleri listele
$stmt = $db->query("
    SELECT f.*, c.name as category_name 
    FROM foods f 
    LEFT JOIN categories c ON f.category_id = c.id 
    ORDER BY f.id DESC
");
$foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kategorileri al (yeni yemek eklerken kullanılacak)
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yemek Yönetimi - Admin Paneli</title>
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
                        <a class="nav-link active" href="foods.php">Yemekler</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Yemek Yönetimi</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFoodModal">
                Yeni Yemek Ekle
            </button>
        </div>

        <?php if(isset($_GET['message'])): ?>
            <?php if($_GET['message'] == 'added'): ?>
                <div class="alert alert-success">Yemek başarıyla eklendi.</div>
            <?php elseif($_GET['message'] == 'deleted'): ?>
                <div class="alert alert-success">Yemek başarıyla silindi.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Resim</th>
                        <th>Ad</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($foods as $food): ?>
                    <tr>
                        <td><?php echo $food['id']; ?></td>
                        <td>
                            <?php if($food['image']): ?>
                                <img src="../uploads/foods/<?php echo $food['image']; ?>" width="50" height="50" alt="">
                            <?php else: ?>
                                <img src="../assets/img/no-image.jpg" width="50" height="50" alt="">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($food['name']); ?></td>
                        <td><?php echo htmlspecialchars($food['category_name']); ?></td>
                        <td><?php echo number_format($food['price'], 2); ?> ₺</td>
                        <td>
                            <?php if($food['status']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editFood(<?php echo $food['id']; ?>)">
                                Düzenle
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Bu yemeği silmek istediğinizden emin misiniz?')">
                                <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                                <button type="submit" name="delete_food" class="btn btn-sm btn-danger">Sil</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Yeni Yemek Ekleme Modal -->
    <div class="modal fade" id="addFoodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Yemek Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_food.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Yemek Adı</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Fiyat</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Resim</label>
                            <input type="file" class="form-control" id="image" name="image">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="status" name="status" value="1" checked>
                                <label class="form-check-label" for="status">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editFood(id) {
        // Bu fonksiyon daha sonra implement edilecek
        alert('Düzenleme özelliği yakında eklenecek!');
    }
    </script>
</body>
</html> 