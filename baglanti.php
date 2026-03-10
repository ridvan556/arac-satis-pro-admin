<?php
$host = 'localhost';
$dbname = 'ziyaretci_db';
$kullanici = 'root';
$sifre = 'mysql'; // Eğer AMPPS'te hata alırsan burayı boş '' bırakabilirsin.

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $kullanici, $sifre);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
    die();
}
?>