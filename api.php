<?php
// 1. API BAŞLIKLARI (HEADERS) - Tarayıcıya bunun bir HTML değil, JSON dosyası olduğunu söyleriz.
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Dışarıdan gelen isteklere (Mobil uygulama vb.) izin ver

// Veritabanı bağlantımızı çağırıyoruz
require_once 'baglanti.php';

// Çıktı vereceğimiz ana dizi (Array)
$response = [];

try {
    // İsteğe bağlı filtreleme: URL sonuna ?marka=Ford yazılırsa sadece o markayı getirir
    if (isset($_GET['marka']) && !empty($_GET['marka'])) {
        $marka = $_GET['marka'];
        $sorgu = $db->prepare("SELECT ID, araba_ismi, model, renk, fiyat, resim FROM araba WHERE araba_ismi = :marka ORDER BY ID DESC");
        $sorgu->execute(['marka' => $marka]);
    } else {
        // Filtre yoksa tüm araçları getir
        $sorgu = $db->query("SELECT ID, araba_ismi, model, renk, fiyat, resim FROM araba ORDER BY ID DESC");
    }

    $arabalar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

    // Eğer veritabanından araç döndüyse
    if ($arabalar) {
        $response['durum'] = 'basarili';
        $response['mesaj'] = count($arabalar) . " adet arac bulundu.";
        $response['veri'] = $arabalar;
    } else {
        // Araç bulunamadıysa
        $response['durum'] = 'hata';
        $response['mesaj'] = "Sistemde kayitli arac bulunamadi veya bu markaya ait arac yok.";
        $response['veri'] = [];
    }

    // 2. JSON ÇIKTISI - PHP dizisini JSON formatına dönüştürüp ekrana basıyoruz
    // JSON_UNESCAPED_UNICODE: Türkçe karakterlerin bozulmasını engeller
    // JSON_PRETTY_PRINT: Kodların yan yana değil, alt alta okunaklı dizilmesini sağlar
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // Veritabanı çökmesi durumunda verilecek hata
    $response['durum'] = 'kritik_hata';
    $response['mesaj'] = "Sunucu Hatasi: " . $e->getMessage();
    echo json_encode($response);
}
?>