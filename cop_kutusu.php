<?php
session_start();

// Oturum Kontrolü
if (!isset($_SESSION['oturum_acik']) || $_SESSION['oturum_acik'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'baglanti.php';
$mesaj = "";

// 1. GERİ YÜKLEME İŞLEMİ (RESTORE)
if (isset($_GET['kurtar'])) {
    $kurtar_id = $_GET['kurtar'];
    // silindi_mi durumunu tekrar 0 yapıyoruz ki anasayfaya (vitrine) dönsün
    $kurtar_sorgu = $db->prepare("UPDATE araba SET silindi_mi = 0 WHERE ID = :id");
    $kurtar_sorgu->execute(['id' => $kurtar_id]);
    $mesaj = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm'>Araç başarıyla geri yüklendi ve ana listeye eklendi!</div>";
}

// 2. KALICI OLARAK SİLME İŞLEMİ (HARD DELETE)
if (isset($_GET['kalici_sil'])) {
    $silinecek_id = $_GET['kalici_sil'];
    
    // Önce sunucudaki fotoğrafı bul ve sil (Çöp dosya kalmasın)
    $resim_sorgu = $db->prepare("SELECT resim FROM araba WHERE ID = :id");
    $resim_sorgu->execute(['id' => $silinecek_id]);
    $silinecek_resim = $resim_sorgu->fetch()['resim'];

    if (!empty($silinecek_resim) && file_exists($silinecek_resim)) { 
        unlink($silinecek_resim); 
    }

    // Sonra veritabanından sonsuza dek sil
    $sil_sorgu = $db->prepare("DELETE FROM araba WHERE ID = :id");
    $sil_sorgu->execute(['id' => $silinecek_id]);
    $mesaj = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm'>Araç sistemden KALICI olarak silindi!</div>";
}

// Çöpteki (silindi_mi = 1) araçları listele
$sorgu = $db->query("SELECT * FROM araba WHERE silindi_mi = 1 ORDER BY ID DESC");
$copteki_arabalar = $sorgu->fetchAll(PDO::FETCH_ASSOC);
$copteki_arac_sayisi = count($copteki_arabalar);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Çöp Kutusu | Araç Satış PRO</title>
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
                    <a href="index.php" class="hover:text-blue-300 font-medium transition flex items-center gap-1"><i class="fa-solid fa-house"></i> Anasayfa</a>
                    <a href="markalar.php" class="hover:text-blue-300 font-medium transition flex items-center gap-1"><i class="fa-solid fa-tags"></i> Markalar</a>
                    <a href="cop_kutusu.php" class="text-blue-300 border-b-2 border-blue-300 pb-1 font-medium transition flex items-center gap-1"><i class="fa-solid fa-trash-can"></i> Çöp Kutusu</a>
                    <a href="about.php" class="hover:text-blue-300 font-medium transition flex items-center gap-1"><i class="fa-solid fa-circle-info"></i> Hakkımızda</a>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-md flex items-center gap-1"><i class="fa-solid fa-power-off"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800"><i class="fa-solid fa-trash-can-arrow-up mr-3 text-red-500"></i>Geri Dönüşüm Kutusu</h1>
            <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-arrow-left"></i> Panele Dön
            </a>
        </div>

        <?php echo $mesaj; ?>

        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <h2 class="text-xl font-bold text-red-900">
                    <i class="fa-solid fa-recycle text-red-600 mr-2"></i>Silinen Araçlar (<?php echo $copteki_arac_sayisi; ?>)
                </h2>
                <span class="text-sm text-gray-500"><i class="fa-solid fa-circle-info"></i> Buradan silinen araçlar kalıcı olarak yok olur.</span>
            </div>

            <div class="overflow-x-auto">
                <?php if(empty($copteki_arabalar)): ?>
                    <div class="text-center py-12 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        <i class="fa-solid fa-box-open text-4xl mb-3 text-gray-300 block"></i>
                        Çöp kutusu şu an boş.
                    </div>
                <?php else: ?>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-red-50 text-red-800 uppercase text-xs leading-normal border-b border-red-200">
                                <th class="py-3 px-4 font-semibold">Görsel</th>
                                <th class="py-3 px-4 font-semibold">Araç / Model</th>
                                <th class="py-3 px-4 font-semibold">Fiyat</th>
                                <th class="py-3 px-4 font-semibold text-right">Kurtarma İşlemleri</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            <?php foreach($copteki_arabalar as $araba): ?>
                            <tr class="border-b border-gray-100 hover:bg-red-50 transition items-center">
                                <td class="py-2 px-4">
                                    <?php if(!empty($araba['resim']) && file_exists($araba['resim'])): ?>
                                        <img src="<?php echo htmlspecialchars($araba['resim']); ?>" class="w-16 h-12 object-cover rounded shadow-sm border border-gray-300 opacity-75">
                                    <?php else: ?>
                                        <div class="w-16 h-12 bg-gray-200 rounded flex items-center justify-center text-gray-400 border border-gray-300"><i class="fa-solid fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 font-bold text-gray-800">
                                    <?php echo htmlspecialchars($araba['araba_ismi']); ?> 
                                    <span class="text-gray-500 font-normal ml-1">(<?php echo htmlspecialchars($araba['model']); ?>)</span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-gray-600"><?php echo number_format($araba['fiyat'], 0, ',', '.'); ?> ₺</td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="cop_kutusu.php?kurtar=<?php echo $araba['ID']; ?>" class="inline-flex items-center gap-1 bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1 rounded transition text-xs font-bold" title="Vitrine Geri Al">
                                        <i class="fa-solid fa-rotate-left"></i> Geri Getir
                                    </a>
                                    <a href="cop_kutusu.php?kalici_sil=<?php echo $araba['ID']; ?>" onclick="return confirm('Bu aracı sonsuza dek silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');" class="inline-flex items-center gap-1 bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded transition text-xs font-bold" title="Kalıcı Sil">
                                        <i class="fa-solid fa-xmark"></i> Tamamen Sil
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>