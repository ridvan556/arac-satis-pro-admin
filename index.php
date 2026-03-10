<?php
session_start();

if (!isset($_SESSION['oturum_acik']) || $_SESSION['oturum_acik'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'baglanti.php';
$hata = "";

// Markaları çek
$marka_sorgu = $db->query("SELECT * FROM markalar ORDER BY marka_adi ASC");
$kayitli_markalar = $marka_sorgu->fetchAll(PDO::FETCH_ASSOC);

// 1. VERİ EKLEME İŞLEMİ (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kaydet'])) {
    $araba_ismi = $_POST['araba_ismi']; 
    $fiyat = $_POST['fiyat'];
    $model = $_POST['model'];
    $renk = $_POST['renk'];
    $resim_yolu = NULL;

    if (isset($_FILES['resim']) && $_FILES['resim']['error'] == 0) {
        $dosya_uzantisi = strtolower(pathinfo($_FILES['resim']['name'], PATHINFO_EXTENSION));
        $izin_verilenler = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($dosya_uzantisi, $izin_verilenler)) {
            if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
            $yeni_ad = uniqid('arac_') . '.' . $dosya_uzantisi;
            $hedef = 'uploads/' . $yeni_ad;
            if (move_uploaded_file($_FILES['resim']['tmp_name'], $hedef)) {
                $resim_yolu = $hedef;
            }
        } else {
            $hata = "Sadece JPG, PNG ve WEBP formatında resim yükleyebilirsiniz.";
        }
    }

    if (empty($hata) && !empty($araba_ismi) && !empty($model)) {
        $sorgu = $db->prepare("INSERT INTO araba (araba_ismi, fiyat, model, renk, resim) VALUES (:araba_ismi, :fiyat, :model, :renk, :resim)");
        $sorgu->execute(['araba_ismi' => $araba_ismi, 'fiyat' => $fiyat, 'model' => $model, 'renk' => $renk, 'resim' => $resim_yolu]);
        header("Location: index.php");
        exit();
    } elseif(empty($hata)) {
        $hata = "Marka ve model alanları zorunludur!";
    }
}

// ==========================================
// 2. YENİ SİLME İŞLEMİ (SOFT DELETE)
// ==========================================
if (isset($_GET['sil'])) {
    $silinecek_id = $_GET['sil'];
    // Artık DELETE yapmıyoruz, durumu 1 (silindi) olarak güncelliyoruz.
    $sil_sorgu = $db->prepare("UPDATE araba SET silindi_mi = 1 WHERE ID = :id");
    $sil_sorgu->execute(['id' => $silinecek_id]);
    header("Location: index.php");
    exit();
}

// 3. İSTATİSTİK SORGULARI (Sadece silinmemiş olanları dahil et)
$istatistik_toplam_arac = $db->query("SELECT COUNT(*) as toplam FROM araba WHERE silindi_mi = 0")->fetch()['toplam'];
$istatistik_toplam_deger = $db->query("SELECT SUM(fiyat) as deger FROM araba WHERE silindi_mi = 0")->fetch()['deger'];
$istatistik_en_pahali = $db->query("SELECT MAX(fiyat) as max_fiyat FROM araba WHERE silindi_mi = 0")->fetch()['max_fiyat'];

// 4. GELİŞMİŞ FİLTRELEME VE SAYFALAMA ALGORİTMASI
$arama_kelimesi = isset($_GET['kelime']) ? trim($_GET['kelime']) : '';
$min_fiyat = isset($_GET['min_fiyat']) && is_numeric($_GET['min_fiyat']) ? $_GET['min_fiyat'] : '';
$max_fiyat = isset($_GET['max_fiyat']) && is_numeric($_GET['max_fiyat']) ? $_GET['max_fiyat'] : '';
$marka_filtre = isset($_GET['marka_filtre']) ? trim($_GET['marka_filtre']) : '';

// DİKKAT: Varsayılan şartımız artık sadece silinmemiş olanları (0) getirmek
$where_sql = "silindi_mi = 0"; 
$params = [];

if (!empty($arama_kelimesi)) {
    $where_sql .= " AND (araba_ismi LIKE :kelime OR model LIKE :kelime OR renk LIKE :kelime)";
    $params[':kelime'] = '%' . $arama_kelimesi . '%';
}
if (!empty($min_fiyat)) {
    $where_sql .= " AND fiyat >= :min_fiyat";
    $params[':min_fiyat'] = $min_fiyat;
}
if (!empty($max_fiyat)) {
    $where_sql .= " AND fiyat <= :max_fiyat";
    $params[':max_fiyat'] = $max_fiyat;
}
if (!empty($marka_filtre)) {
    $where_sql .= " AND araba_ismi = :marka_filtre";
    $params[':marka_filtre'] = $marka_filtre;
}

$sayfa = isset($_GET['sayfa']) && is_numeric($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$sayfa_basina_kayit = 5; 
$offset = ($sayfa - 1) * $sayfa_basina_kayit;

$toplam_sorgu = $db->prepare("SELECT COUNT(*) FROM araba WHERE $where_sql");
$toplam_sorgu->execute($params);
$toplam_kayit = $toplam_sorgu->fetchColumn();
$toplam_sayfa = ceil($toplam_kayit / $sayfa_basina_kayit);

$sorgu_metni = "SELECT * FROM araba WHERE $where_sql ORDER BY ID DESC LIMIT :limit OFFSET :offset";
$sorgu = $db->prepare($sorgu_metni);

foreach($params as $key => $value) {
    $sorgu->bindValue($key, $value);
}
$sorgu->bindValue(':limit', $sayfa_basina_kayit, PDO::PARAM_INT);
$sorgu->bindValue(':offset', $offset, PDO::PARAM_INT);
$sorgu->execute();
$arabalar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

$query_string = "";
if(!empty($arama_kelimesi)) $query_string .= "&kelime=" . urlencode($arama_kelimesi);
if(!empty($min_fiyat)) $query_string .= "&min_fiyat=" . $min_fiyat;
if(!empty($max_fiyat)) $query_string .= "&max_fiyat=" . $max_fiyat;
if(!empty($marka_filtre)) $query_string .= "&marka_filtre=" . urlencode($marka_filtre);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetim Paneli | Araç Satış PRO</title>
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
                    <a href="index.php" class="text-blue-300 font-medium transition flex items-center gap-1"><i class="fa-solid fa-house"></i> Anasayfa</a>
                    <a href="markalar.php" class="hover:text-blue-300 font-medium transition flex items-center gap-1"><i class="fa-solid fa-tags"></i> Markalar</a>
                    
                    <a href="cop_kutusu.php" class="hover:text-blue-300 font-medium transition flex items-center gap-1">
                        <i class="fa-solid fa-trash-can"></i> Çöp Kutusu
                    </a>
                    
                    <a href="about.php" class="hover:text-blue-300 font-medium transition flex items-center gap-1"><i class="fa-solid fa-circle-info"></i> Hakkımızda</a>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-md flex items-center gap-1"><i class="fa-solid fa-power-off"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-6 flex items-center justify-between">
                <div><p class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-1">Toplam Araç</p><h3 class="text-3xl font-extrabold text-gray-800"><?php echo $istatistik_toplam_arac; ?></h3></div>
                <div class="bg-blue-100 p-4 rounded-full text-blue-600"><i class="fa-solid fa-car-side text-2xl"></i></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-6 flex items-center justify-between">
                <div><p class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-1">Portföy Değeri</p><h3 class="text-3xl font-extrabold text-gray-800"><?php echo number_format((float)$istatistik_toplam_deger, 0, ',', '.'); ?> ₺</h3></div>
                <div class="bg-green-100 p-4 rounded-full text-green-600"><i class="fa-solid fa-wallet text-2xl"></i></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-purple-500 p-6 flex items-center justify-between">
                <div><p class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-1">En Pahalı Araç</p><h3 class="text-3xl font-extrabold text-gray-800"><?php echo number_format((float)$istatistik_en_pahali, 0, ',', '.'); ?> ₺</h3></div>
                <div class="bg-purple-100 p-4 rounded-full text-purple-600"><i class="fa-solid fa-arrow-trend-up text-2xl"></i></div>
            </div>
        </div>

        <?php if(!empty($hata)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm"><?php echo $hata; ?></div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200 sticky top-24">
                    <h2 class="text-xl font-bold text-blue-900 mb-4 border-b pb-2"><i class="fa-solid fa-plus-circle text-blue-600 mr-2"></i>Yeni Araç Ekle</h2>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Marka</label>
                            <select name="araba_ismi" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 bg-white" required>
                                <option value="">-- Marka Seçiniz --</option>
                                <?php foreach($kayitli_markalar as $marka): ?>
                                    <option value="<?php echo htmlspecialchars($marka['marka_adi']); ?>"><?php echo htmlspecialchars($marka['marka_adi']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div><label class="block text-gray-700 text-sm font-bold mb-2">Model Yılı</label><input type="number" name="model" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" required></div>
                            <div><label class="block text-gray-700 text-sm font-bold mb-2">Renk</label><input type="text" name="renk" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"></div>
                        </div>
                        <div class="mb-4"><label class="block text-gray-700 text-sm font-bold mb-2">Fiyat (₺)</label><input type="number" name="fiyat" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"></div>
                        <div class="mb-6"><label class="block text-gray-700 text-sm font-bold mb-2">Araç Görseli</label><input type="file" name="resim" accept=".jpg, .jpeg, .png, .webp" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 bg-gray-50 text-sm"></div>
                        <button type="submit" name="kaydet" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition flex justify-center items-center gap-2"><i class="fa-solid fa-floppy-disk"></i> Sisteme Kaydet</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200 mb-6">
                    <h2 class="text-lg font-bold text-gray-700 mb-4"><i class="fa-solid fa-filter text-gray-500 mr-2"></i>Detaylı Arama</h2>
                    <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div><label class="block text-xs font-bold text-gray-600 mb-1">Kelime Ara</label><input type="text" name="kelime" placeholder="Araç, model, renk..." value="<?php echo htmlspecialchars($arama_kelimesi); ?>" class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-500"></div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Marka</label>
                            <select name="marka_filtre" class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-500 bg-white">
                                <option value="">Tümü</option>
                                <?php foreach($kayitli_markalar as $marka): ?>
                                    <option value="<?php echo htmlspecialchars($marka['marka_adi']); ?>" <?php echo ($marka_filtre == $marka['marka_adi']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($marka['marka_adi']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div><label class="block text-xs font-bold text-gray-600 mb-1">Min. ₺</label><input type="number" name="min_fiyat" placeholder="Min" value="<?php echo htmlspecialchars($min_fiyat); ?>" class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-500"></div>
                            <div><label class="block text-xs font-bold text-gray-600 mb-1">Max. ₺</label><input type="number" name="max_fiyat" placeholder="Max" value="<?php echo htmlspecialchars($max_fiyat); ?>" class="w-full px-2 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-500"></div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="w-full bg-gray-800 text-white font-bold py-2 rounded hover:bg-gray-700 transition text-sm">Filtrele</button>
                            <?php if(!empty($arama_kelimesi) || !empty($min_fiyat) || !empty($max_fiyat) || !empty($marka_filtre)): ?><a href="index.php" class="w-full bg-red-100 text-red-600 font-bold py-2 rounded hover:bg-red-200 transition text-sm text-center flex items-center justify-center">Temizle</a><?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
                   <div class="flex flex-col md:flex-row justify-between items-center mb-4 border-b pb-4 gap-4">
    <h2 class="text-xl font-bold text-blue-900">
        <i class="fa-solid fa-list text-blue-600 mr-2"></i>Kayıtlı Araçlar (<?php echo $toplam_kayit; ?> Bulundu)
    </h2>
    
    <a href="excel_indir.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition flex items-center gap-2 text-sm shadow-sm">
        <i class="fa-solid fa-file-excel"></i> Excel (CSV) İndir
    </a>
</div>
                    
                    <div class="overflow-x-auto mb-6">
                        <?php if(empty($arabalar)): ?>
                            <div class="text-center py-8 text-gray-500"><i class="fa-solid fa-magnifying-glass text-3xl mb-3 block text-gray-300"></i>Aradığınız kriterlere uygun araç bulunamadı.</div>
                        <?php else: ?>
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-600 uppercase text-xs leading-normal">
                                        <th class="py-3 px-4 font-semibold">Görsel</th>
                                        <th class="py-3 px-4 font-semibold">Araç</th>
                                        <th class="py-3 px-4 font-semibold">Model/Renk</th>
                                        <th class="py-3 px-4 font-semibold">Fiyat</th>
                                        <th class="py-3 px-4 font-semibold text-right">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700 text-sm">
                                    <?php foreach($arabalar as $araba): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition items-center">
                                        <td class="py-2 px-4">
                                            <?php if(!empty($araba['resim']) && file_exists($araba['resim'])): ?>
                                                <img src="<?php echo htmlspecialchars($araba['resim']); ?>" class="w-16 h-12 object-cover rounded shadow-sm border border-gray-300">
                                            <?php else: ?>
                                                <div class="w-16 h-12 bg-gray-200 rounded flex items-center justify-center text-gray-400 border border-gray-300"><i class="fa-solid fa-image"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 font-bold text-blue-900"><?php echo htmlspecialchars($araba['araba_ismi']); ?></td>
                                        <td class="py-3 px-4">
                                            <span class="bg-gray-200 text-gray-700 py-1 px-2 rounded text-xs mr-1"><?php echo htmlspecialchars($araba['model']); ?></span>
                                            <span class="text-gray-500 text-xs"><?php echo htmlspecialchars($araba['renk']); ?></span>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-green-600"><?php echo number_format($araba['fiyat'], 0, ',', '.'); ?> ₺</td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="duzenle.php?id=<?php echo $araba['ID']; ?>" class="text-blue-500 hover:text-blue-700 mr-3" title="Düzenle"><i class="fa-solid fa-pen-to-square text-lg"></i></a>
                                            <a href="index.php?sil=<?php echo $araba['ID']; ?>" onclick="return confirm('Bu aracı çöp kutusuna göndermek istediğinize emin misiniz?');" class="text-red-500 hover:text-red-700" title="Çöpe Gönder"><i class="fa-solid fa-trash text-lg"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <?php if($toplam_sayfa > 1): ?>
                    <div class="flex justify-center items-center gap-2 border-t pt-4">
                        <?php for($i = 1; $i <= $toplam_sayfa; $i++): ?>
                            <a href="?sayfa=<?php echo $i; ?><?php echo $query_string; ?>" class="px-4 py-2 rounded-md font-medium transition <?php echo ($sayfa == $i) ? 'bg-blue-600 text-white shadow-md' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>