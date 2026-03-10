<?php
session_start();

// Tüm session değişkenlerini temizle
$_SESSION = array();

// Session'ı tamamen yok et
session_destroy();

// Çıkış yaptıktan sonra giriş sayfasına yönlendir
header("Location: login.php");
exit;
?>