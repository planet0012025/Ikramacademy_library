<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-06-29 08:15:05
 * @modify date 2021-06-29 08:15:05
 * @desc Helpers - Updated for PHP 8.2 Compatibility
 */

function httpQuery(array $query = [])
{
    // Menggabungkan GET params dengan query baru
    return http_build_query(array_merge($_GET, $query));
}

function isDirect()
{
    defined('INDEX_AUTH') OR die('Direct access not allowed!');
}

function pluginDirName()
{
    $dir = explode(DIRECTORY_SEPARATOR, trim(dirname(__FILE__), DIRECTORY_SEPARATOR));
    // Menggunakan array_key_last (PHP 7.3+) atau end() sebagai fallback
    if (function_exists('array_key_last')) {
        return $dir[array_key_last($dir)];
    }
    return end($dir);
}

function commaToDot($string)
{
    // PERBAIKAN: Cast ke string untuk mencegah error jika nilai null
    return str_replace(',', '.', (string)$string);
}

function loadFile($fileToLoad, $type = 'require')
{
    // PERBAIKAN: Pastikan variabel global didefinisikan agar tidak undefined di file include
    global $dbs, $max_print, $sysconf, $content, $settingsTemplate, $chunked_barcode_arrays, $html_str;

    $filePath = __DIR__ . '/' . $fileToLoad . '.php';

    if (file_exists($filePath)) 
    {
        switch ($type) {
            case 'include':
                include $filePath;
                break;
            
            default:
                require $filePath;
                break;
        }
    }
    else
    {
        // Ganti die() dengan pesan error yang lebih aman jika file optional
        error_log("File plugin tidak ditemukan: " . $filePath);
        echo "File tidak ada: " . htmlspecialchars($fileToLoad);
        die();
    }
}

function jsonProps($mix)
{
    $json = json_encode($mix);
    // PERBAIKAN: Cek jika json_encode gagal
    return $json ? str_replace('"', '\'', $json) : '{}';
}

function jsonResponse($mix)
{
    header('Content-Type: application/json');
    echo json_encode($mix);
    exit;
}

function jsonPost($key = '')
{
    $input = file_get_contents('php://input');
    $post = json_decode($input, TRUE);

    // PERBAIKAN: Pastikan $post adalah array sebelum mengakses index
    if (!is_array($post)) {
        return [];
    }

    return (!empty($key) && isset($post[$key])) ? $post[$key] : $post;
}

function sliceCallNumber($string)
{
    // PERBAIKAN: Handle jika string kosong/null
    if (empty($string))
    {
        return '<b style="color: red">Callnumber<br/>Invalid</b>';
    }

    // Regex split
    $split = preg_split('/(?<=\w)\s+(?=[A-Za-z])/m', (string)$string);
    
    // Safety check jika regex gagal
    if (!$split) {
        return $string;
    }

    $result = '';
    
    foreach ($split as $index => $stringSplit) {
        // PERBAIKAN: Cek index dan string length
        if ($index === 0 && strlen($stringSplit) > 0 && preg_match('/[A-Za-z]/i', substr($stringSplit, 0,1)))
        {
            $Plus = explode(' ', $stringSplit);
            // PERBAIKAN KRUSIAL: Mencegah Undefined Array Key 1
            $part1 = $Plus[0] ?? '';
            $part2 = $Plus[1] ?? '';
            
            $result .= $part1 . '<br/>' . $part2 . '<br/>';
        }
        else
        {
            $result .= $stringSplit . '<br/>';
        }
    }

    // Mencegah error substr_replace pada string kosong
    if (strlen($result) > 5) {
        return substr_replace($result, '', -5);
    }
    return $result;
}

function callNumberColor($string, $arrayColor)
{
    // PERBAIKAN: Cast string dan trim aman
    $strClean = trim((string)$string);
    if (empty($strClean)) return '#ffffff';

    $callNumber = substr($strClean, 0,1);

    if (!preg_match('/[0-9]/i', $callNumber))
    {
        $explodeCallnumber = explode(' ', $strClean);
        // PERBAIKAN: Null Coalescing Operator (??)
        $secondPart = $explodeCallnumber[1] ?? '?';
        $code = substr($secondPart, 0,1);
    }
    else
    {
        $code = $callNumber;
    }

    // PERBAIKAN: Cek ketersediaan key di array
    $key = $code . 'XX';
    return isset($arrayColor[$key]) ? $arrayColor[$key] : '#ffffff';
}