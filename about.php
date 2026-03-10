<?php
session_start();

// Oturum Kontrolü (Hakkımızda sayfasını da sadece yöneticiler görsün)
if (!isset($_SESSION['oturum_acik']) || $_SESSION['oturum_acik'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hakkımızda | Araç Satış PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-800">

    <nav class="bg-blue-900 text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="text-2xl font-bold flex items-center gap-2">
                    <i class="fa-solid fa-car"></i>
                    <span>AraçSatış<span class="text-blue-400 text-sm align-top">PRO</span></span>
                </div>
                
                <div class="flex items-center space-x-6">
                    <a href="index.php" class="hover:text-blue-300 font-medium transition flex items-center gap-1">
                        <i class="fa-solid fa-house"></i> Anasayfa
                    </a>
                    <a href="about.php" class="text-blue-300 font-medium transition flex items-center gap-1">
                        <i class="fa-solid fa-circle-info"></i> Hakkımızda
                    </a>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-md flex items-center gap-1">
                        <i class="fa-solid fa-power-off"></i> Çıkış Yap
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="relative bg-gradient-to-r from-blue-900 to-cyan-800 py-24 text-white overflow-hidden shadow-inner">
        <div class="absolute inset-0 opacity-10 bg-[url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?q=80&w=1920&auto=format&fit=crop')] bg-cover bg-center"></div>
        
        <div class="relative z-10 container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4 tracking-tight">Otomotivde Dijital Dönüşüm</h1>
            <p class="text-lg md:text-xl opacity-90 max-w-2xl mx-auto font-light">
                Modern, hızlı ve güvenilir yönetim altyapımız ile araç satış süreçlerini yeniden tanımlıyoruz.
            </p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-16">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            
            <div>
                <div class="inline-block bg-blue-100 text-blue-800 px-4 py-1 rounded-full text-sm font-bold mb-6 uppercase tracking-wider">
                    Biz Kimiz?
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6 leading-tight">Yazılımın Gücünü <br><span class="text-blue-600">Otomotiv Sektörüyle</span> Buluşturuyoruz</h2>
                <p class="text-gray-600 mb-6 leading-relaxed text-lg">
                    AraçSatış PRO, karmaşık araç alım-satım süreçlerini tek bir ekrandan, en yüksek performansla yönetebilmeniz için geliştirilmiş bir sistemdir. Her bir kod satırı, işinizi hızlandırmak ve verilerinizi güvende tutmak için özenle yazıldı.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="bg-blue-100 p-3 rounded-lg text-blue-600 mt-1">
                            <i class="fa-solid fa-check text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-800">Hızlı ve Dinamik</h4>
                            <p class="text-gray-600 text-sm">Gelişmiş veritabanı mimarisi sayesinde binlerce araç verisi içinde saniyeler içinde arama yapın.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="bg-blue-100 p-3 rounded-lg text-blue-600 mt-1">
                            <i class="fa-solid fa-shield-halved text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-800">Üst Düzey Güvenlik</h4>
                            <p class="text-gray-600 text-sm">Oturum kontrolleri ve veri doğrulama sistemleri ile platformunuz her zaman güvende.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="absolute inset-0 bg-blue-600 rounded-2xl transform translate-x-4 translate-y-4 opacity-20"></div>
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=800&auto=format&fit=crop" alt="Ekip Çalışması" class="relative z-10 rounded-2xl shadow-xl w-full object-cover h-[400px]">
                
                <div class="absolute -bottom-6 -left-6 z-20 bg-white p-6 rounded-xl shadow-2xl border border-gray-100 flex items-center gap-4">
                    <div class="bg-green-100 p-4 rounded-full text-green-600">
                        <i class="fa-solid fa-code text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-extrabold text-gray-900">100%</div>
                        <div class="text-sm text-gray-500 font-medium">Temiz ve Modern Kod</div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <div class="bg-gray-100 py-16 mt-8 border-t border-gray-200">
        <div class="container mx-auto px-4 text-center">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Sistemi Geliştirmek İçin Buradayız</h3>
            <p class="text-gray-600 mb-8 max-w-xl mx-auto">
                Yönetim panelinizde yeni modüllere (müşteri yönetimi, fatura kesimi vb.) ihtiyacınız olduğunda altyapımız her türlü geliştirmeye hazırdır.
            </p>
            <a href="index.php" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition transform hover:-translate-y-1">
                <i class="fa-solid fa-arrow-left"></i> Panele Geri Dön
            </a>
        </div>
    </div>

    <footer class="bg-white py-6 text-center text-gray-500 text-sm border-t">
        <p>&copy; <?php echo date("Y"); ?> AraçSatış PRO. Tüm Hakları Saklıdır.</p>
    </footer>
<?php include 'footer.php'; ?>
    
</body>
</html>
</body>
</html>