<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-06-29 08:15:59
 * @modify date 2021-06-29 08:15:59
 * @desc [description]
 */

isDirect();

if (isset($_GET['action']) AND $_GET['action'] == 'print') {
  // 1. Cek Session dengan aman
  if (empty($_SESSION['mix_barcodes']) || !is_array($_SESSION['mix_barcodes'])) {
    utility::jsToastr('Item Barcode', __('There is no data to print!'), 'error');
    die();
  }

  // global settings
  $globalSettings = $sysconf['lbc_settings'] ?? []; // Tambahan ?? agar tidak error jika kosong
  
  // Validasi template setting
  if (empty($globalSettings['template'])) {
      utility::jsToastr('Item Barcode', 'Konfigurasi Template Error', 'error');
      die();
  }

  // set settings per Template
  $templateName = $globalSettings['template'];
  $settingsTemplate = $sysconf['lbc_' . $templateName . 'Code'] ?? null;
  // color pallet
  $palletColor = $sysconf['lbc_color'] ?? [];
  // set Template dir
  $templateDir = __DIR__.'/../template/'.$templateName . 'Code_template.php';

  if (!$settingsTemplate) {
    utility::jsToastr('Item Barcode', 'Template tidak tersedia!', 'error');
    die();
  }

  if (!file_exists($templateDir)) {
    utility::jsToastr('Item Barcode', 'File template tidak tersedia!', 'error');
    die();
  }

  // 2. PERBAIKAN: Menggunakan Implode agar lebih aman & cepat daripada loop manual
  $safe_ids = [];
  foreach ($_SESSION['mix_barcodes'] as $id) {
      // Escape string untuk keamanan database
      $safe_ids[] = "'" . $dbs->escape_string($id) . "'";
  }
  
  if (empty($safe_ids)) {
       utility::jsToastr('Item Barcode', 'Data ID tidak valid', 'error');
       die();
  }
  
  $id_string = implode(',', $safe_ids);

  // send query to database
  $item_q = $dbs->query("SELECT b.title, i.item_code, i.call_number, b.biblio_id FROM item AS i
    LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
    WHERE i.item_code IN($id_string)");
    
  $item_data_array = array();
  while ($item_d = $item_q->fetch_assoc()) {
    if (!empty($item_d['item_code'])) {
      $item_data_array[] = $item_d;
    }
  }

  // all data
  $allData = count($item_data_array);

  // 3. PERBAIKAN: Pastikan Chunk bernilai angka valid untuk mencegah division by zero
  $chunkSize = (int)($globalSettings['chunk'] ?? 2);
  if ($chunkSize < 1) $chunkSize = 2;

  // chunk barcode array
  $chunked_barcode_arrays = array_chunk($item_data_array, $chunkSize);
  
  // 4. PERBAIKAN UTAMA: Output Buffering
  // Kita mulai rekam output. Ini mencegah error jika template langsung 'echo' HTML
  // atau jika variabel $html_str lupa didefinisikan di template.
  ob_start();
  
  // Definisi awal variabel agar tidak Undefined di PHP 8.2
  $html_str = ''; 
  
  // include main template
  include $templateDir;
  
  // Ambil isi buffer
  $output_buffer = ob_get_clean();
  
  // Jika $html_str masih kosong tapi ada output dari buffer, pakai buffer
  if (empty($html_str) && !empty($output_buffer)) {
      $html_str = $output_buffer;
  }

  // unset the session
  unset($_SESSION['mix_barcodes']);
  
  // 5. PERBAIKAN: Handle nama file jika session uname kosong
  $uname = $_SESSION['uname'] ?? 'system';
  $clean_uname = strtolower(str_replace(' ', '_', $uname));
  
  // write to file
  $print_file_name = 'label_mixcode_warna_gen_print_result_'.$clean_uname.'.html';
  
  // Pastikan $html_str ada isinya sebelum ditulis
  if (empty($html_str)) {
      $html_str = '<html><body>Error: Template tidak menghasilkan output apapun.</body></html>';
  }

  $file_write = @file_put_contents(UPLOAD.$print_file_name, $html_str);
  
  if ($file_write) {
    // update print queue count object
    echo '<script type="text/javascript">top.document.querySelector(\'#queueCount\').innerHTML = 0</script>';
    // open result in window (Gunakan jQuery 'top.$' atau vanilla JS jika perlu)
    echo '<script type="text/javascript">
            if(typeof top.jQuery !== "undefined") {
                top.jQuery.colorbox({href: "'.SWB.FLS.'/'.$print_file_name.'", iframe: true, width: 1341, height: 597, title: "Label Barcode Warna"});
            } else {
                window.open("'.SWB.FLS.'/'.$print_file_name.'", "_blank");
            }
          </script>';
  } else { 
      utility::jsToastr('Item Barcode', str_replace('{directory}', SB.FLS, __('ERROR! Item barcodes failed to generate, possibly because {directory} directory is not writable')), 'error'); 
  }
  exit();
}