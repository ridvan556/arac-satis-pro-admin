<?php
// Veritabanı bağlantımızı çağırıyoruz
require_once 'baglanti.php';

try {
    // 1. Kullanıcılar tablosunu oluştur (Eğer yoksa)
    $db->exec("CREATE TABLE IF NOT EXISTS kullanicilar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kullanici_adi VARCHAR(50) NOT NULL UNIQUE,
        sifre_hash VARCHAR(255) NOT NULL
    )");

    // 2. Admin kullanıcısı daha önce eklenmiş mi kontrol et
    $kontrol = $db->query("SELECT * FROM kullanicilar WHERE kullanici_adi = 'admin'")->fetch();

    if (!$kontrol) {
        // Kullanıcı yoksa, şifreyi kriptolayarak (hash) sisteme ekle
        $sifre = '123456';
        $kriptolu_sifre = password_hash($sifre, PASSWORD_DEFAULT);
        
        $ekle = $db->prepare("INSERT INTO kullanicilar (kullanici_adi, sifre_hash) VALUES (?, ?)");
        $ekle->execute(['admin', $kriptolu_sifre]);
        
        echo "<h3 style='color:green;'>Harika! 'kullanicilar' tablosu oluşturuldu ve admin başarıyla eklendi.</h3>";
        echo "<p>Kullanıcı Adı: <b>admin</b> <br> Şifre: <b>123456</b></p>";
        echo "<p>Artık bu kurulum.php dosyasını güvenliğiniz için silebilirsiniz.</p>";
    } else {
        echo "<h3 style='color:blue;'>Tablo zaten mevcut ve admin kullanıcısı sistemde kayıtlı.</h3>";
    }

} catch (PDOException $e) {
    echo "Veritabanı Hatası: " . $e->getMessage();
}
?>