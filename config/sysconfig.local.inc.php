<?php
/**
 * KONFIGURASI PINTAR UNTUK BACKUP SYSTEM
 * File ini otomatis mendeteksi lingkungan (Local vs Docker Server)
 */

// Cek apakah ada Environment Variable khas Docker?
$is_docker = getenv('DB_HOST'); 

if ($is_docker) {
    // === KONDISI 1: SEDANG DI SERVER (DOCKER) ===
    // Ambil data rahasia dari Environment Variable Server
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
    define('DB_NAME', getenv('DB_DATABASE') ?: getenv('DB_NAME')); 
    define('DB_USERNAME', getenv('DB_USERNAME') ?: getenv('DB_USER'));
    define('DB_PASSWORD', getenv('DB_PASSWORD') ?: getenv('DB_PASS'));
} else {
    // === KONDISI 2: SEDANG DI LAPTOP (XAMPP/LOKAL) ===
    // Settingan standar XAMPP
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'libikram_2025'); // Pastikan ini nama DB di phpMyAdmin laptop Anda
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', ''); // Password XAMPP biasanya kosong
}

// Masukkan ke konfigurasi Global SLiMS
$sysconf['db_host'] = DB_HOST;
$sysconf['db_port'] = DB_PORT;
$sysconf['db_name'] = DB_NAME;
$sysconf['db_user'] = DB_USERNAME;
$sysconf['db_pass'] = DB_PASSWORD;