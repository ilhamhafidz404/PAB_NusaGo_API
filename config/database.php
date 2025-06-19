<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=nusago", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
// try {
//     $pdo = new PDO("mysql:host=localhost;dbname=alow9361_quizapp", "alow9361_xxhamz", "SpanzieAhaWaririt9.");
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die("Koneksi gagal: " . $e->getMessage());
// }
?>