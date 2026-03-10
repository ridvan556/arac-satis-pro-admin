# 🚗 Araç Satış PRO - Yönetim Paneli (Admin Dashboard)

Bu proje, araç alım-satım işlemleri için geliştirilmiş, güvenli, dinamik ve kullanıcı dostu bir arka yüz (backend) yönetim panelidir. Modern web standartlarına uygun olarak sıfırdan kodlanmıştır.

## 🚀 Kullanılan Teknolojiler
* **Backend:** PHP 8
* **Veritabanı:** MySQL (PDO - PHP Data Objects)
* **Frontend:** HTML5, Tailwind CSS
* **İkonlar:** FontAwesome

## 🛠️ Öne Çıkan Özellikler
* **Kriptolu Oturum Yönetimi:** Güvenli login/logout sistemi ve şifre hashleme.
* **İlişkisel Veritabanı:** Araçlar ve Markalar tabloları arasında dinamik bağlantı.
* **RESTful API Ucu:** Dış sistemler ve mobil uygulamalar için JSON formatında veri aktarımı (`api.php`).
* **Soft Delete (Yumuşak Silme):** Veri kaybını önleyen Çöp Kutusu / Geri Dönüşüm mimarisi.
* **Dinamik Filtreleme & Sayfalama (Pagination):** Minimum/Maksimum fiyat ve marka bazlı detaylı arama motoru.
* **Dosya Yönetimi:** Sunucuya güvenli araç görseli yükleme ve otomatik isimlendirme (`uniqid`).
* **Raporlama:** Mevcut araç listesini tek tıkla Excel (CSV) formatında dışa aktarma.

## ⚙️ Kurulum
1. Proje dosyalarını sunucunuzun (XAMPP, Ampps, MAMP vb.) kök dizinine kopyalayın.
2. `kurulum.php`, `marka_kurulum.php` ve `cop_kurulum.php` dosyalarını tarayıcıda sırayla çalıştırarak veritabanı tablolarını otomatik oluşturun.
3. `fiyat_duzelt.php` dosyasını çalıştırarak veritabanı tiplerini optimize edin.
4. `login.php` üzerinden belirlediğiniz şifre ile sisteme giriş yapın.
