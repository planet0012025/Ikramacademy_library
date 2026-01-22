<?php
/**
 * OVERRIDE DATABASE CONFIGURATION
 * File ini akan menimpa semua settingan default SLiMS
 */

// 1. Definisikan Konstanta Database (Hardcode)
define('DB_HOST', 'db');
define('DB_PORT', '3306');
define('DB_NAME', 'senayan');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Pass123');

// 2. Paksa Masuk ke Variabel Utama Sistem ($sysconf)
// Variabel inilah yang dibaca oleh Modul Backup untuk membuat DSN
$sysconf['db_host'] = DB_HOST;
$sysconf['db_port'] = DB_PORT;
$sysconf['db_name'] = DB_NAME;
$sysconf['db_user'] = DB_USERNAME;
$sysconf['db_pass'] = DB_PASSWORD;

// 3. Matikan Debugging (Opsional, biar bersih)
$sysconf['debug'] = false;