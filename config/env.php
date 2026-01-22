<?php
/**
 * Supported mode list:
 * - development : all errors will appear
 * - production : silent error
 */
$env = 'production'; // Ubah jadi production biar pesan error tidak muncul di layar user

/**
 * Environment mode if
 * incoming ip has registered at
 * $ip_range and $based_on_ip = true
 */
$conditional_environment = 'development';

/**
 * Show error only for some ip
 * based on ip range
 */
$based_on_ip = false;

/**
 * Range IP will be impacted with
 * environment mode
 */
$range_ip = [''];

if ($based_on_ip) {
    // For load balancing or Reverse Proxy
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && in_array($_SERVER['HTTP_X_FORWARDED_FOR'], $range_ip)) {
        $env = $conditional_environment;
    } else if (in_array($_SERVER['REMOTE_ADDR']??'', $range_ip)) {
        $env = $conditional_environment;
    }
}

/**
 * Cli environment
 */
if (php_sapi_name() === 'cli') {
    $env = $conditional_environment !== $env ? $conditional_environment : $env;
}

// =================================================================
// TAMBAHAN FIX BACKUP DATABASE (DOCKER) - MAS FATUL
// Menambahkan kredensial database manual agar terbaca modul Backup
// =================================================================

// 1. Konstanta Database (Sesuai docker-compose.yml Anda)
define('DB_HOST', 'db');
define('DB_PORT', '3306');
define('DB_NAME', 'senayan');      // Database Anda namanya 'senayan', bukan 'slims'
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Pass123');  // Password asli dari file yaml Anda

// 2. Inject ke Variabel Global SLiMS (Wajib ada untuk fitur Backup)
global $sysconf;
$sysconf['db_host'] = 'db';
$sysconf['db_port'] = '3306';
$sysconf['db_name'] = 'senayan';
$sysconf['db_user'] = 'root';
$sysconf['db_pass'] = 'Pass123';