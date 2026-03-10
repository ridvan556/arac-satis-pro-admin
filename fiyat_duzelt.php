<?php
require_once 'baglanti.php';

try {
    // Fiyat sütununun veri tipini INT (Sayısal) olarak güncelliyoruz
    $db->exec("ALTER TABLE araba MODIFY fiyat INT NOT NULL");
    
    echo "<h3 style='color:green;'>Harika! Fiyat sütunu başarıyla 'Sayı' (INT) tipine dönüştürüldü.</h3>";
    echo "<p>Artık index.php sayfanızı yenilediğinizde En Pahalı Araç algoritması matematiksel olarak doğru çalışacaktır.</p>";
    echo "<p>Bu dosyayı (fiyat_duzelt.php) silebilirsiniz.</p>";

} catch (PDOException $e) {
    echo "Veritabanı Hatası: " . $e->getMessage();
}
?>