<?php
require_once 'config/database.php';

try {
    // Önce veritabanını oluştur
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Veritabanını oluştur
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Veritabanı başarıyla oluşturuldu.<br>";
    
    // Veritabanını seç
    $pdo->exec("USE " . DB_NAME);
    
    // Kategoriler tablosunu oluştur
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Kategoriler tablosu başarıyla oluşturuldu.<br>";
    
    // Yemekler tablosunu oluştur
    $pdo->exec("CREATE TABLE IF NOT EXISTS foods (
        id INT PRIMARY KEY AUTO_INCREMENT,
        category_id INT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image VARCHAR(255),
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )");
    echo "Yemekler tablosu başarıyla oluşturuldu.<br>";
    
    // Örnek kategorileri ekle
    $pdo->exec("INSERT INTO categories (name, description) VALUES
        ('Ana Yemekler', 'Birbirinden lezzetli ana yemekler'),
        ('Çorbalar', 'Sıcacık çorbalar'),
        ('Tatlılar', 'Enfes tatlılar')
    ");
    echo "Örnek kategoriler eklendi.<br>";
    
    // Örnek yemekleri ekle
    $pdo->exec("INSERT INTO foods (category_id, name, description, price, status) VALUES
        (1, 'Karnıyarık', 'Patlıcan, kıyma ve sebzelerle hazırlanan geleneksel Türk yemeği', 45.00, 1),
        (1, 'İskender', 'Döner, pide, domates sosu ve yoğurt ile servis edilir', 55.00, 1),
        (2, 'Mercimek Çorbası', 'Geleneksel Türk mercimek çorbası', 20.00, 1),
        (3, 'Künefe', 'Kadayıf ve peynir ile hazırlanan geleneksel tatlı', 35.00, 1)
    ");
    echo "Örnek yemekler eklendi.<br>";
    
    echo "<br>Kurulum başarıyla tamamlandı! Ana sayfaya dönmek için <a href='index.php'>tıklayınız</a>.";
    
} catch(PDOException $e) {
    echo "Hata oluştu: " . $e->getMessage();
    die();
}
?> 