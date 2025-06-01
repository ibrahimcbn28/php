-- Ödeme durumu sütunu ekle
ALTER TABLE orders ADD COLUMN payment_status ENUM('pending', 'waiting', 'paid', 'failed') NOT NULL DEFAULT 'pending' AFTER status; 