<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-06-29 08:12:31
 * @modify date 2021-06-29 08:12:31
 * @desc Right Template - Updated for PHP 8.2
 */

isDirect();

// set path
$assetPath = SWB . 'plugins/' . pluginDirName();

// PERBAIKAN: Definisikan variabel default agar tidak error Undefined Variable/Array Key
// Ini sangat penting untuk PHP 8.2
$title = $title ?? 'Label Print';
$globalSettings = $sysconf['lbc_settings'] ?? [];
$settingsTemplate = $sysconf['lbc_' . ($globalSettings['template'] ?? 'right') . 'Code'] ?? [];
$chunked_barcode_arrays = $chunked_barcode_arrays ?? [];
$palletColor = $sysconf['lbc_color'] ?? [];
$sysconf['library_name'] = $sysconf['library_name'] ?? 'Perpustakaan';

// Default values untuk ukuran (Mencegah error saat matematika pengurangan/penjumlahan)
$s_widthBox = (float)($settingsTemplate['widthBox'] ?? 20);
$s_heightBox = (float)($settingsTemplate['heightBox'] ?? 10);
$s_widthBarcode = (float)($settingsTemplate['widthBarcode'] ?? 8);
$s_heightBarcode = (float)($settingsTemplate['heightBarcode'] ?? 4);
$s_topBarcode = (float)($settingsTemplate['topBarcode'] ?? 3.5);
$s_leftBarcode = (float)($settingsTemplate['leftBarcode'] ?? -5);
$s_fontSize = $settingsTemplate['callNumberFontSize'] ?? 'text-sm';
$s_marginPage = $globalSettings['marginPage'] ?? '5mm';
$s_pageBreakAt = (int)($globalSettings['pageBreakAt'] ?? 6);
$codeType = $globalSettings['codeType'] ?? 'Barcode';

ob_start();
?>
<!DOCTYPE Html>
<html>
    <head>
        <title><?= $title ?></title>
        <link href="<?= $assetPath . '/assets/css/tailwind.min.css'?>" rel="stylesheet"/>
        <style>

            .rot90 {
                transform: rotate(90deg) !important;
            }
            .rot270 {
                transform: rotate(270deg) !important;
            }

            @media print {
                /* Kode Baru: Memaksa warna background muncul */
                * {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    color-adjust: exact !important;
                }
                
                button {
                    display: none !important
                }
                .pagebreak {
                    clear: both;
                    page-break-after: always;
                }
            }

            @page  
            { 
                size: auto;   /* auto is the initial value */ 

                /* this affects the margin in the printer settings */ 
                margin: <?= $s_marginPage ?>;  
            } 
        </style>
    </head>
    <body>
        <div class="w-full h-screen">
            <button class="bg-green-500 p-1 text-white" onClick="self.print()">Print</button>
            <?php
            // set row
            $row = 0;
            $rowData = 0;
            
            // PERBAIKAN: Cek apakah data ada dan array
            if (!empty($chunked_barcode_arrays) && is_array($chunked_barcode_arrays)):

                // loop chunked barcode
                foreach ($chunked_barcode_arrays as $barcode_array):
                    // set flex wrap
                    echo '<div class="flex flex-wrap">';
                    
                    if (is_array($barcode_array)): // Safety check inner loop
                        foreach ($barcode_array as $barcode):
                            // slicing number
                            // Gunakan ?? '' untuk mencegah error jika key tidak ada
                            $rawCallNumber = $barcode['call_number'] ?? '';
                            $callNumber = sliceCallNumber($rawCallNumber);
                            
                            // shorting and slice it
                            $rawTitle = $barcode['title'] ?? '';
                            $titleSlice = substr($rawTitle, 0, 5);
                            
                            // get color
                            $color = callNumberColor($rawCallNumber, $palletColor);
                            
                            // item code
                            $itemCode = $barcode['item_code'] ?? '';

                            // set template
                            // Barcode
                            if ($codeType === 'Barcode'):
                                // convert comma to dot & lakukan matematika aman
                                $responsiveWidth = commaToDot($s_widthBox - 5.4);
                                
                                echo <<<HTML
                                    <div style="width:{$s_widthBox}em; height: {$s_heightBox}em; border: 1px solid black; margin-left: 8px; margin-top: 10px">
                                        <div class="inline-block" style="width: {$responsiveWidth}em ;height: {$s_heightBox}em; border-right: 1px solid black">
                                            <span class="w-full block text-center text-sm" style="border-bottom: 1px solid black; background-color:{$color}">{$sysconf['library_name']}</span>
                                            <span class="w-full block text-center text-md mt-8 font-bold {$s_fontSize}"> {$callNumber}</span>
                                        </div>
                                        <div class="inline-block float-right mr-2" style="width: 75px;">
                                            <small class="pl-2 pt-1">{$titleSlice} ...</small>
                                            <img class="inline-block rot270 barcode" jsbarcode-format="CODE128" jsbarcode-value="{$itemCode}" style="width: {$s_widthBarcode}em; height: {$s_heightBarcode}em; margin-top: {$s_topBarcode}em; margin-left: {$s_leftBarcode}em; position: absolute;"/>
                                        </div>
                                    </div>
                                HTML;
                            
                            // Qrcode
                            elseif ($codeType === 'Qrcode'):
                                // set image and div selector id based row data
                                $rowId = ($rowData+1); 
                                // convert comma to dot
                                $responsiveWidth1 = commaToDot($s_widthBox + 4);
                                $responsiveWidth2 = commaToDot($s_widthBox - 5.4);
                                $widthQrcode = commaToDot($s_widthBarcode - 1);
                                $heightQrcode = commaToDot($s_heightBarcode + 2);
                                $marginTop = commaToDot($s_topBarcode - 1);
                                
                                // special measurement
                                // PERBAIKAN: $allData harus didefinisikan di global scope generateLabel.php
                                $totalData = $allData ?? 0; 
                                $num = ($rowId === $totalData) ? (0.2) : (-0.4);
                                $marginLeft = commaToDot($s_leftBarcode + $num);
                                
                                echo <<<HTML
                                    <div style="width:{$responsiveWidth1}em; height: {$s_heightBox}em; border: 1px solid black; margin-left: 8px; margin-top: 10px">
                                        <div class="inline-block" style="width: {$responsiveWidth2}em ;height: {$s_heightBox}em; border-right: 1px solid black">
                                            <span class="w-full block text-center text-sm" style="border-bottom: 1px solid black; background-color:{$color}">{$sysconf['library_name']}</span>
                                            <span class="w-full block text-center text-md mt-8 font-bold {$s_fontSize}"> {$callNumber}</span>
                                        </div>
                                        <div class="inline-block float-right mr-2" style="width: 100px;">
                                            <small class="pl-2 pt-1">{$titleSlice} ...</small>
                                            <img id="img{$rowId}" data-code="{$itemCode}" class="inline-block qrcode" style="width: {$widthQrcode}em; height: {$heightQrcode}em; margin-top: {$marginTop}em; margin-left: {$marginLeft}em; position: absolute;"/>
                                            <div id="qrcode{$rowId}"></div>
                                        </div>
                                    </div>
                                HTML;
                            endif;
                            $rowData++; // counting data
                        endforeach; 
                    endif;

                    // increment row
                    $row++;
                    echo '</div>';
                    // set page break
                    // PERBAIKAN: Pastikan tidak division by zero
                    $pb = ($s_pageBreakAt > 0) ? $s_pageBreakAt : 6;
                    echo (($row % $pb) === 0) ? '<div class="pagebreak"></div>' : null;
                endforeach; 
            
            else:
                echo '<div class="p-4 text-red-500 font-bold">Tidak ada data barcode yang valid atau terjadi kesalahan format data.</div>';
            endif;
            ?>
        </div>
        <?php  if ($codeType === 'Barcode'): ?>
            <script src="<?= $assetPath . '/assets/js/JsBarcode.all.min.js'?>"></script>
            <script>JsBarcode(".barcode").init();</script>
        <?php  elseif ($codeType === 'Qrcode'): ?>
            <script src="<?= $assetPath . '/assets/js/qrcode.min.js'?>"></script>
            <script>
                // set doc
                let doc = document
                // QRcode instance
                function creatQr(imgSelector,divSelector)
                {
                    if (!divSelector) return; // safety check
                    
                    new QRCode(divSelector, {
                        text: imgSelector.dataset.code,
                        render: "canvas",  //Rendering mode specifies canvas mode
                    })

                    if (divSelector.children.length > 0) {
                        let canvas = divSelector.children[0];
                        imgSelector.setAttribute('src', canvas.toDataURL("image/png"))
                        divSelector.classList.add('hidden')
                    }
                }

                // setup for qrcode
                <?php for ($r = 1; $r <= $rowData; $r++): ?>
                creatQr(doc.querySelector('#img<?= $r ?>'), doc.querySelector('#qrcode<?= $r ?>'))
                <?php endfor; ?>
            </script>
        <?php  endif;?>
        <?php if (!empty($globalSettings['autoprint'])): ?>
            <script>self.print()</script>
        <?php endif; ?>
    </body>
</html>
<?php
// get buffer
$html_str = ob_get_clean();
?>