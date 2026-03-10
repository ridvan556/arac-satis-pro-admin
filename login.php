<?php
session_start();
// Artık veritabanı bağlantısına ihtiyacımız var
require_once 'baglanti.php'; 

// Eğer kullanıcı zaten giriş yapmışsa direkt anasayfaya yönlendir
if (isset($_SESSION['oturum_acik']) && $_SESSION['oturum_acik'] === true) {
    header("Location: index.php");
    exit;
}

$hata = "";

// Form gönderildiğinde çalışacak blok
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = $_POST['username'];
    $sifre = $_POST['password'];

    // 1. Veritabanından kullanıcıyı bul
    $sorgu = $db->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi = :kullanici_adi");
    $sorgu->execute(['kullanici_adi' => $kullanici_adi]);
    $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

    // 2. Kullanıcı var mı ve GİRİLEN ŞİFRE HASH İLE EŞLEŞİYOR MU kontrol et
    if ($kullanici && password_verify($sifre, $kullanici['sifre_hash'])) {
        
        // Şifre doğruysa oturumu başlat
        $_SESSION['oturum_acik'] = true;
        $_SESSION['kullanici'] = $kullanici['kullanici_adi'];
        $_SESSION['kullanici_id'] = $kullanici['id']; // Güvenlik için ID'yi de tutuyoruz
        
        header("Location: index.php");
        exit;
    } else {
        $hata = "Hatalı kullanıcı adı veya şifre!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap | Araç Satış PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-cover bg-center bg-no-repeat" style="background-image: url('https://images.unsplash.com/photo-1603584173870-7f23fdae1b7a?q=80&w=1920&auto=format&fit=crop');">

    <div class="absolute inset-0 bg-black/50 z-0"></div>

    <div class="relative z-10 bg-white/10 backdrop-blur-md border border-white/20 p-8 rounded-2xl shadow-2xl w-96 text-white">
        <h2 class="text-3xl font-bold text-center mb-6 tracking-wide">Yönetim Girişi</h2>
        
        <?php if($hata != ""): ?>
            <div class="bg-red-500/80 border border-red-200 text-white px-4 py-3 rounded mb-4 text-sm backdrop-blur-sm">
                <?php echo $hata; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-200 text-sm font-semibold mb-2">Kullanıcı Adı</label>
                <input type="text" name="username" required class="w-full px-3 py-2 bg-white/20 border border-gray-300/30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
            </div>
            <div class="mb-6">
                <label class="block text-gray-200 text-sm font-semibold mb-2">Şifre</label>
                <input type="password" name="password" required class="w-full px-3 py-2 bg-white/20 border border-gray-300/30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-4 rounded-lg shadow-lg transform transition hover:-translate-y-0.5">
                Sisteme Gir
            </button>
        </form>
    </div>

</body>
</html>