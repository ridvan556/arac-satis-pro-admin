<?php
require_once 'baglanti.php';

try {
    // Araba tablomuza 'resim' adında yeni bir sütun ekliyoruz
    $db->exec("ALTER TABLE araba ADD resim VARCHAR(255) NULL AFTER renk");
    echo "<h3 style='color:green;'>Harika! 'araba' tablosuna resim sütunu başarıyla eklendi.</h3>";
    echo "<p>Artık index.php dosyasını güncelleyebiliriz. Bu dosyayı (guncelleme.php) silebilirsiniz.</p>";
} catch (PDOException $e) {
    // Eğer sütun zaten varsa hata vermesini engelleyip bilgi veriyoruz
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
         echo "<h3 style='color:blue;'>Resim sütunu zaten mevcut! İşleme devam edebilirsiniz.</h3>";
    } else {
         echo "Veritabanı Hatası: " . $e->getMessage();
    }
}
?>