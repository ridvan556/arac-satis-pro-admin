<?php
require_once 'baglanti.php';

try {
    // araba tablomuza varsayılan değeri 0 (silinmedi) olan yeni bir sütun ekliyoruz
    $db->exec("ALTER TABLE araba ADD silindi_mi TINYINT(1) DEFAULT 0 AFTER resim");
    
    echo "<h3 style='color:green;'>Harika! Çöp kutusu altyapısı (silindi_mi sütunu) başarıyla kuruldu.</h3>";
    echo "<p>Artık index.php dosyasını güncelleyebilirsiniz. Bu dosyayı silebilirsiniz.</p>";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
         echo "<h3 style='color:blue;'>Sütun zaten mevcut! İşleme devam edebilirsiniz.</h3>";
    } else {
         echo "Veritabanı Hatası: " . $e->getMessage();
    }
}
?>