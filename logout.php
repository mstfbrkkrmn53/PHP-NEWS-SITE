<?php
session_start();
session_destroy(); // Tüm oturum verilerini temizle
header("Location: /index.php"); // Ana sayfaya yönlendir
exit;