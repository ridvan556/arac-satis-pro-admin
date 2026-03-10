<?php
session_start();

// Güvenlik: Sadece giriş yapanlar indirebilir
if (!isset($_SESSION['oturum_acik']) || $_SESSION['oturum_acik'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'baglanti.php';

// Tarayıcıya bunun bir web sayfası değil, indirilecek bir Excel (CSV) dosyası olduğunu söylüyoruz
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=arac_raporu_' . date('Y-m-d_H-i') . '.csv');

// Çıktı tamponunu açıyoruz
$output = fopen('php://output', 'w');

// Türkçe karakter sorunu olmaması için (UTF-8 BOM) ekliyoruz
fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// 1. Satır: Excel'in Başlık Sütunları (Noktalı virgül ';' kullanıyoruz ki hücreler düzgün ayrılsın)
fputcsv($output, array('Kayıt ID', 'Marka', 'Model Yılı', 'Renk', 'Fiyat (TL)'), ';');

// 2. Satır ve sonrası: Veritabanından araçları çekip satır satır yazdırıyoruz
$sorgu = $db->query("SELECT ID, araba_ismi, model, renk, fiyat FROM araba WHERE silindi_mi = 0 ORDER BY ID DESC");

while ($araba = $sorgu->fetch(PDO::FETCH_ASSOC)) {
    // Rakamların düzgün görünmesi için formatlayabiliriz (İsteğe bağlı)
    $araba['fiyat'] = number_format($araba['fiyat'], 0, ',', '.'); 
    
    // Veriyi Excel satırı olarak yaz
    fputcsv($output, $araba, ';');
}

fclose($output);
exit;
?>