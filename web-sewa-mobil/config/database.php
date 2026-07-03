<?php
// Pengaturan Koneksi Database Lokal (XAMPP / Localhost)
$host    = 'localhost'; 
$port    = '3307';                    // Sesuaikan jika port MySQL XAMPP Anda diubah (misal: 3307)
$db      = 'driveease_db';            // Nama database lokal Anda di phpMyAdmin local
$user    = 'root';                    // Username default XAMPP selalu root
$pass    = '';                        // Password default XAMPP dikosongkan ''
$charset = 'utf8mb4';

// String DSN untuk PDO
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Connection failed: " . $e->getMessage());
}
?>