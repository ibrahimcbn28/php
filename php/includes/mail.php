<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendOrderStatusEmail($order, $status) {
    try {
        $to = $order['customer_email'];
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Restoran Adı <noreply@example.com>\r\n";
        
        switch($status) {
            case 'preparing':
                $subject = 'Siparişiniz Hazırlanıyor!';
                $content = "
                    <h2>Siparişiniz Hazırlanıyor</h2>
                    <p>Sayın {$order['customer_name']},</p>
                    <p>#{$order['id']} numaralı siparişiniz mutfağımızda hazırlanmaya başladı.</p>
                ";
                break;
                
            case 'on_way':
                $subject = 'Siparişiniz Yola Çıktı!';
                $content = "
                    <h2>Siparişiniz Yola Çıktı</h2>
                    <p>Sayın {$order['customer_name']},</p>
                    <p>#{$order['id']} numaralı siparişiniz teslimat için yola çıktı.</p>
                    <p>Tahmini teslimat süresi: 30 dakika</p>
                ";
                break;
                
            case 'delivered':
                $subject = 'Siparişiniz Teslim Edildi';
                $content = "
                    <h2>Siparişiniz Teslim Edildi</h2>
                    <p>Sayın {$order['customer_name']},</p>
                    <p>#{$order['id']} numaralı siparişiniz teslim edildi.</p>
                    <p>Bizi tercih ettiğiniz için teşekkür ederiz.</p>
                ";
                break;
                
            case 'cancelled':
                $subject = 'Siparişiniz İptal Edildi';
                $content = "
                    <h2>Siparişiniz İptal Edildi</h2>
                    <p>Sayın {$order['customer_name']},</p>
                    <p>#{$order['id']} numaralı siparişiniz iptal edildi.</p>
                    <p>Özür dileriz, en kısa sürede sizinle iletişime geçeceğiz.</p>
                ";
                break;
        }
        
        // Sipariş detaylarını ekle
        $content .= "
            <h3>Sipariş Detayları</h3>
            <p>
                <strong>Sipariş No:</strong> #{$order['id']}<br>
                <strong>Tarih:</strong> " . date('d.m.Y H:i', strtotime($order['created_at'])) . "<br>
                <strong>Toplam Tutar:</strong> " . number_format($order['total_amount'], 2) . " ₺<br>
                <strong>Ödeme Yöntemi:</strong> " . ($order['payment_method'] == 'cash' ? 'Kapıda Ödeme' : 'Kredi Kartı') . "
            </p>
            
            <p>
                <strong>Teslimat Adresi:</strong><br>
                " . nl2br(htmlspecialchars($order['delivery_address'])) . "
            </p>
        ";
        
        // E-posta gönder
        return mail($to, $subject, $content, $headers);
        
    } catch (\Exception $e) {
        error_log("Mail Error: {$e->getMessage()}");
        return false;
    }
} 