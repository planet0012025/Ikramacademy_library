<?php
/**
 * @Created by          : Drajat Hasan
 * @Date                : 2021-06-28 06:37:56
 * @File name           : index.php
 */

use SLiMS\DB;

defined('INDEX_AUTH') OR die('Direct access not allowed!');

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB . 'admin/default/session.inc.php';
// set dependency
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_UTILS/simbio_tokenizecql.inc.php';
require LIB.'biblio_list_model.inc.php';
require LIB.'biblio_list_index.inc.php';
// end dependency

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

// set page title
$page_title = 'Label Mixcode Color';

// set config
$sysconf['lbc_settings'] = ['chunk' => 2, 'template' => 'right', 'codeType' => 'Barcode', 'marginPage' => '5mm 5mm 5mm 5mm', 'pageBreakAt' => 6, 'autoprint' => 1];
$sysconf['lbc_leftCode'] = ['itemCode' => 'B00017', 'callNumberFontSize' => 'text-sm', 'callNumber' => '7965.555 919 Har n', 'widthBox' => 20, 'heightBox' => 10, 'widthBarcode' => 8,'heightBarcode' => 4, 'topBarcode' => 3.5, 'leftBarcode' => -5];
$sysconf['lbc_rightCode'] = ['itemCode' => 'B00018', 'callNumberFontSize' => 'text-sm', 'callNumber' => '7965.555 919 Har n', 'widthBox' => 20, 'heightBox' => 10, 'widthBarcode' => 8,'heightBarcode' => 4, 'topBarcode' => 3.5, 'leftBarcode' => -5];
$sysconf['lbc_bothCode'] = ['itemCode' => 'B00019', 'callNumberFontSize' => 'text-sm', 'callNumber' => '7965.555 919 Har n', 'widthBox' => 20, 'heightBox' => 10, 'widthBarcode' => 8,'heightBarcode' => 4, 'topBarcode' => 3.5, 'leftBarcode' => -5];
$sysconf['lbc_color'] = ['0XX' => '#ffffff', '1XX' => '#ffffff', '2XX' => '#ffffff', '3XX' => '#ffffff', '4XX' => '#ffffff', '5XX' => '#ffffff','6XX' => '#ffffff', '7XX' => '#ffffff','8XX' => '#ffffff','9XX' => '#ffffff'];

// load settings
utility::loadSettings($dbs);

// helpers
// PERBAIKAN PHP 8.2: Cek file sebelum require
if (file_exists(__DIR__.'/helpers.php')) {
    require __DIR__.'/helpers.php';
}

/* Action Area */
$max_print = 50;

/* RECORD OPERATION */
// Pastikan folder action ada
$actionPath = __DIR__ . '/action/';
if (file_exists($actionPath . 'generateSession.php')) include $actionPath . 'generateSession.php';

// clean print queue
if (file_exists($actionPath . 'clearSession.php')) include $actionPath . 'clearSession.php';

// print Label
// PENTING: Di sinilah biasanya error terjadi saat tombol Print diklik
if (file_exists($actionPath . 'generateLabel.php')) include $actionPath . 'generateLabel.php';

// settings page
if (file_exists(__DIR__ . '/pages/settings.php')) include __DIR__ . '/pages/settings.php';

/* End Action Area */
?>
<div class="menuBox">
    <div class="menuBoxInner memberIcon">
        <div class="per_title">
            <h2><?= $page_title ?? 'Label Mixcode Color'; ?></h2>
        </div>
        <div class="sub_section">
            <div class="btn-group">
                <a target="blindSubmit" href="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(['action' => 'clear']) ?>" class="notAJAX btn btn-danger mx-1"><?= __('Clear Print Queue') ?></a>
                <a target="blindSubmit" href="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(['action' => 'print']) ?>" class="notAJAX btn btn-primary mx-1"><?= __('Print Barcodes for Selected Data'); ?></a>
                <a href="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(['action' => 'settings']) ?>" class="notAJAX btn btn-default openPopUp mx-1" width="780" height="500" title="<?= __('Change print barcode settings'); ?>"><?= __('Change print barcode settings'); ?></a>
            </div>
            <form name="search" action="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery() ?>" id="search" method="get" class="form-inline"><?= __('Search'); ?>
                <input type="text" name="keywords" class="form-control col-md-3"/>
                <input type="submit" id="doSearch" value="<?= __('Search'); ?>" class="s-btn btn btn-default"/>
            </form>
        </div>
        <div class="infoBox">
        <?php
        echo __('Maximum').' <strong class="text-danger">'.$max_print.'</strong> '.__('records can be printed at once. Currently there is').' ';
        
        // PERBAIKAN PHP 8.2: Gunakan is_array dan count dengan benar
        $queueCount = 0;
        if (isset($_SESSION['mix_barcodes']) && is_array($_SESSION['mix_barcodes'])) {
            $queueCount = count($_SESSION['mix_barcodes']);
        }
        
        echo '<strong id="queueCount" class="text-danger">'.$queueCount.'</strong>';
        echo ' '.__('in queue waiting to be printed.');
        ?>
        </div>
    </div>
</div>
<script>
    // set variable
    let popUp = document.querySelector('.openPopUp')
    if (popUp) {
        let w = parseInt(window.innerWidth) - 100
        let h = parseInt(window.innerHeight) - 100
        
        // set attribute
        popUp.setAttribute('width', w)
        popUp.setAttribute('height', h)
    }
</script>
<?php
/* Datagrid area */
$table_spec = 'item LEFT JOIN search_biblio AS `index` ON item.biblio_id=`index`.biblio_id';

// membuat datagrid
$datagrid = new simbio_datagrid();

$datagrid->setSQLColumn('item.item_code',
'item.item_code AS \''.__('Item Code').'\'',
'index.title AS \''.__('Title').'\'',
'item.call_number AS \'Nomor Panggil\'');

$datagrid->setSQLorder('item.last_update DESC');

if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $keywords = utility::filterData('keywords', 'get', true, true, true);
    $searchable_fields = array('title', 'author', 'subject', 'itemcode');
    $search_str = '';
    // if no qualifier in fields
    if (!preg_match('@[a-z]+\s*=\s*@i', $keywords)) {
      foreach ($searchable_fields as $search_field) {
        $search_str .= $search_field.'='.$keywords.' OR ';
      }
    } else {
      $search_str = $keywords;
    }
    // PERBAIKAN: Gunakan require_once agar biblio_list tidak double load
    if (!class_exists('biblio_list')) {
        require LIB.'biblio_list_model.inc.php';
    }
    $biblio_list = new biblio_list($dbs, 20);
    $criteria = $biblio_list->setSQLcriteria($search_str);
}

if (isset($criteria)) {
    $datagrid->setSQLcriteria('('.$criteria['sql_criteria'].')');
}

// set table and table header attributes
$datagrid->table_attr = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
// edit and checkbox property
$datagrid->edit_property = false;
$datagrid->chbox_property = array('itemID', __('Add'));
$datagrid->chbox_action_button = __('Add To Print Queue');
$datagrid->chbox_confirm_msg = __('Add to print queue?');
$datagrid->column_width = array('10%', '85%');
// set checkbox action URL
$datagrid->chbox_form_URL = $_SERVER['PHP_SELF'] . '?' . httpQuery();
// put the result into variables
$datagrid_result = $datagrid->createDataGrid(DB::getInstance('mysqli'), $table_spec, 20, true); 

if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords'));
    echo '<div class="infoBox">' . $msg . ' : "' . htmlspecialchars($_GET['keywords']) . '"<div>' . __('Query took') . ' <b>' . $datagrid->query_time . '</b> ' . __('second(s) to complete') . '</div></div>';
}
// menampilkan datagrid
echo $datagrid_result;
/* End datagrid */