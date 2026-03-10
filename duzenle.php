<?php
session_start();

// Oturum Kontrolü
if (!isset($_SESSION['oturum_acik']) || $_SESSION['oturum_acik'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'baglanti.php';

// 1. URL'den gelen ID değerini al ve aracı bul
if (!isset($_GET['id'])) {
    // ID yoksa anasayfaya geri gönder
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// Aracı veritabanından çek (Forma mevcut verileri yazdırmak için)
$sorgu = $db->prepare("SELECT * FROM araba WHERE ID = :id");
$sorgu->execute(['id' => $id]);
$araba = $sorgu->fetch(PDO::FETCH_ASSOC);

// Eğer veritabanında böyle bir araç yoksa
if (!$araba) {
    header("Location: index.php");
    exit;
}

$hata = "";

// 2. VERİ GÜNCELLEME İŞLEMİ (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guncelle'])) {
    $araba_ismi = $_POST['araba_ismi'];
    $fiyat = $_POST['fiyat'];
    $model = $_POST['model'];
    $renk = $_POST['renk'];

    if (!empty($araba_ismi) && !empty($model)) {
        // UPDATE Sorgusu
        $guncelle_sorgu = $db->prepare("UPDATE araba SET araba_ismi = :araba_ismi, fiyat = :fiyat, model = :model, renk = :renk WHERE ID = :id");
        $guncelle_sorgu->execute([
            'araba_ismi' => $araba_ismi,
            'fiyat' => $fiyat,
            'model' => $model,
            'renk' => $renk,
            'id' => $id
        ]);
        
        // İşlem başarılıysa anasayfaya dön
        header("Location: index.php");
        exit();
    } else {
        $hata = "Araba ismi ve model alanları zorunludur!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Araç Düzenle | Araç Satış PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <nav class="bg-blue-900 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="text-2xl font-bold flex items-center gap-2">
                    <i class="fa-solid fa-car"></i>
                    <span>AraçSatış<span class="text-blue-400 text-sm align-top">PRO</span></span>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="index.php" class="hover:text-blue-300 font-medium transition flex items-center gap-1">
                        <i class="fa-solid fa-arrow-left"></i> Panele Dön
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-12 flex justify-center">
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 w-full max-w-2xl">
            <div class="flex items-center justify-between mb-6 border-b pb-4">
                <h2 class="text-2xl font-bold text-blue-900">
                    <i class="fa-solid fa-pen-to-square text-blue-600 mr-2"></i>Araç Bilgilerini Güncelle
                </h2>
                <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded text-sm font-semibold">
                    Kayıt ID: #<?php echo htmlspecialchars($araba['ID']); ?>
                </span>
            </div>

            <?php if(!empty($hata)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <?php echo $hata; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-5">
                    <label class="block text-gray-700 font-bold mb-2">Marka / İsim</label>
                    <input type="text" name="araba_ismi" value="<?php echo htmlspecialchars($araba['araba_ismi']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                </div>
                
                <div class="grid grid-cols-2 gap-6 mb-5">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Model Yılı</label>
                        <input type="number" name="model" value="<?php echo htmlspecialchars($araba['model']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Renk</label>
                        <input type="text" name="renk" value="<?php echo htmlspecialchars($araba['renk']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-gray-700 font-bold mb-2">Fiyat (₺)</label>
                    <input type="number" name="fiyat" value="<?php echo htmlspecialchars($araba['fiyat']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>

                <div class="flex gap-4">
                    <button type="submit" name="guncelle" class="w-2/3 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition flex justify-center items-center gap-2 shadow-md">
                        <i class="fa-solid fa-check"></i> Değişiklikleri Kaydet
                    </button>
                    <a href="index.php" class="w-1/3 bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded-lg transition flex justify-center items-center gap-2 shadow-md text-center">
                        İptal Et
                    </a>
                </div>
            </form>
        </div>

    </div>
    <?php include 'footer.php'; ?>
    
</body>
</html>
</body>
</html>