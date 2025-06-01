    </div> <!-- content-wrapper end -->
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>Lezzet Durağı</h5>
                    <p>En lezzetli yemekler, en hızlı teslimat.</p>
                    <div class="social-links">
                        <a href="#" class="me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="me-3"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Hızlı Linkler</h5>
                    <ul class="list-unstyled">
                        <li><a href="menu.php" class="text-white">Menü</a></li>
                        <li><a href="#" class="text-white">Hakkımızda</a></li>
                        <li><a href="#" class="text-white">İletişim</a></li>
                        <li><a href="#" class="text-white">Gizlilik Politikası</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>İletişim</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-geo-alt"></i> Örnek Mahallesi, Örnek Sokak No:1</li>
                        <li><i class="bi bi-telephone"></i> +90 555 123 4567</li>
                        <li><i class="bi bi-envelope"></i> info@lezzetduragi.com</li>
                    </ul>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p class="mb-0">&copy; 2024 Lezzet Durağı. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS ve diğer gerekli scriptler -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Sepete ekleme fonksiyonu
        function addToCart(foodId) {
            $.ajax({
                url: 'ajax/add_to_cart.php',
                type: 'POST',
                data: {
                    food_id: foodId
                },
                success: function(response) {
                    alert('Ürün sepete eklendi!');
                },
                error: function() {
                    alert('Bir hata oluştu!');
                }
            });
        }
    </script>
</body>
</html> 