<?php
include_once('inc/config.php');
include_once('inc/xlsxwriter.class.php');
include_once('inc/state.php');
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
    include_once('inc/m2md_win.php');
        

if (!isset($_SESSION))
    session_start();

//if (class_exists('Memcached'))
    $m = new Memcached();
//else
//    $m = new Memcache();

$m->addServer('127.0.0.1', 11211);// or die(«Could not connect»);

$st = new State();
//if (!isset($_SESSION['st']))
////    if (!isset($_SESSION['actions']))
//	$_SESSION['st'] = new State();
//
//$st = &$_SESSION['st'];

include_once('inc/yap.php');


$actions = $m->get('actions');
//if (class_exists('Memcached')) {
if (method_exists($m,'getResultCode')) {
    if ($m->getResultCode() !== Memcached::RES_SUCCESS) // item does not exist ($item is probably false)
        $actions = array();
} else {
   if (!$actions) $actions = array();
}


// if (!isset($_REQUEST['submit'])) { // главная страница без параметров


function  writeXSLX($rst) {
    global $actions;

    $header = array(
      'promo_name'=>'string',
      'promo_start'=>'DD.MM.YYYY',
      'promo_end'=>'DD.MM.YYYY',
      'promo_discont'=>'integer',
      'book_name'=>'string',
      'book_autor'=>'string',
      'book_code'=>'string',
      'book_publisher'=>'string',
      'book_lang'=>'string',
      'book_year'=>'string',
      'book_dsc'=>'string',
      'book_old-price'=>'string',
      'book_special-price'=>'string',
      'book_sitelink'=>'string',
    );
    //  'quantity'=>'#,##0',

    $writer = new XLSXWriter();
//    $sheet_name = 'yap_full';
    $sheet_name = 'yap_full';
    $filename = 'yap_'.date('ymd');

    $writer->writeSheetHeader($sheet_name, $header );

    foreach ($actions as $aid => $act) {
	if ($aid == "_time") continue;	    //	"Служебное поле" - время актуализации данных
	if (count($rst))
	    if (!in_array($aid, $rst)) continue;
	if (isset($act['books']) && is_array($act['books']))
	foreach ($act['books'] as $bid => $book) {
	    if ($bid == "_time") continue;	    //	"Служебное поле" - время актуализации данных
	    $writer->writeSheetRow($sheet_name, array(
		$act['title'],
		$act['sdate'],
		$act['edate'],
		$act['dsc'],
		$book['name'],
		$book['autor'],
		$book['code'],
		$book['publisher'],
		$book['lang'],
		$book['year'],
		$book['dsc'],
		$book['old-price'],
		$book['special-price'],
		$book['href'],
	    ) );
	}
    }



    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Transfer-Encoding: binary");
    header("Content-Type: binary/octet-stream");

//    header("Content-Type: application/force-download");
//    header("Content-Type: application/octet-stream");
//    header("Content-Type: application/download");

    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Transfer-Encoding: binary");
    header("Content-Type: binary/octet-stream");
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
//    echo $writer->writeToString();
    $writer->writeToStdOut();
}


function showActTbl() {
    global $conf;
    global $actions;

    header('Content-Type: text/html; charset=utf-8');

// 	    table {font-size:12px;color:#333333;width:100%;border-width: 1px;border-color: #729ea5;border-collapse: collapse;}

function df($d) { return date('d.m.Y',strtotime($d)); }
$df = 'df';

//<!DOCTYPE html>
//<html>
//    <head>
//        <meta charset="UTF-8">
//        <title></title>
//	<script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
//        <link href="inc/main.css" rel="stylesheet" type="text/css" />
//
//    </head>
//    <body>
//	<form id="action_form" action="test.php" method="post" target="_blank">


$out = <<<EOT
	<h3>Список актульных акций на сайте</h3><br />

	<form id="action_form" action="test.php" method="post">
	<table>
	    <tr>
		<th><INPUT type="checkbox" id="selectAll" name="chk[]" /></th>
		<th>Название акции</th>
		<th>Дата начала</th>
		<th>Дата окончания</th>
		<th>Заявленная скидка</th>
		<th>&ensp;</th>
	    </tr>
	
EOT;

//		$.ajax({
//		    type: form.attr('method'),
//		    url: form.attr('action'),
//		    data: data
//		}).done(function() {
//		    console.log('success');
//		}).fail(function() {
//		    console.log('fail');
//		});


    foreach ($actions as $id => $act) {
	if ($id == "_time") continue;	    //	"Служебное поле" - время актуализации данных
	
$out .= <<<EOT
	    <tr id="$id" class="data_row">
		<td class="ch_outer"><INPUT type="checkbox" name="chk[]" value="$id"></td>
		<td class="left"><a href="$act[href]">$act[title]</a></td>
		<td>{$df($act["sdate"])}</td>
		<td>{$df($act["edate"])}</td>
		<td class="right">$act[dsc] %</td>
		<td><button type="submit" name="submit" value="$id">load</button></td>
	    </tr>
EOT;

    }

$out .= <<<EOT
	    <tr>
		<td colspan="6"></td>
	    </tr>
	    <tr style="background-color:#d4e3e5;">
		<td><input type="checkbox" name="skip" checked="true"></td>
		<td class="left" colspan="5">Игнорировать книги без скидок</td>
	    </tr>
	</table>
	    <br />
	    <button type="submit" name="submit" value="selected">load selected</button>
	    <button type="submit" name="submit" value="all">load all</button>
	</form>
EOT;

//    </body>
//</html>

echo $out;

}

function prepareActions() {
    global $conf;
    global $st;
    global $m;
    global $actions;

if ($conf['debug']) error_log('! START action "prepare"');
    header('Content-Type: application/json');

    $exp = time() - $actions['_time'];
if ($conf['debug']) error_log('! prepare $action[_time] : ['.$actions['_time'].']');
if ($conf['debug']) error_log('! prepare $exp : ['.$exp.']');
if ($conf['debug']) error_log('! prepare ACTT : ['.ACTT.']');
//    if ((time() - $action['_time']) < ACTT) {
    if (($exp) < ACTT) {
//	echo json_encode(array('action' => "Данные актуальны..."));
	echo json_encode(array('action' => "Данные актуальны...",'exp' => $exp));
    } else {

	$st->start('Составление списка акций');

	prepareActionList();

	$actions['_time'] = time();
	$m->set('actions', $actions, EXPT);

if ($conf['debug']) error_log('! END prepare count($actions) ['.count($actions).']');

	$st->stop();

	echo json_encode(array('action' => "Завершено"));
    }
}

function checkState() {
    global $conf;
    global $st;
//if ($conf['debug']) error_log('? check $_SESSION ['.print_r($_SESSION,true).']');
if ($conf['debug']) error_log('? check >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
    header('Content-Type: application/json');

//    $state = $m->get('state');
//    if (!isset($state['preparing']) || $state['preparing'] == false)
//	echo json_encode(array('error' => "Ошибка: Парсинг не выполняется"));
//    else {
//	echo json_encode($state);
//    }
    $tmp = $st->get();
if ($conf['debug']) error_log('? check state ['.print_r($tmp,true).']');

    echo json_encode($tmp);
//    echo json_encode($st->get());
if ($conf['debug']) error_log('? check <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
}

// ================================================================================
//if ($conf['debug'] && is_array($_REQUEST) && count($_REQUEST)) {
//var_dump($_REQUEST);
//print_r($_REQUEST);
error_log('IN $_REQUEST ['.print_r($_REQUEST,true).']');
//die();
//}


if (isset($_REQUEST['check'])) {
    checkState();
} elseif (isset($_REQUEST['prepare'])) {
    prepareActions();
} elseif (isset($_REQUEST['showprepared']) && count($actions)) {
    showActTbl();
} elseif (isset($_REQUEST['submit'])) {
    $error = false;
    $errorstr = '';
    $rst = array();
    $skip = isset($_REQUEST['skip']);
//    $st->start('Составление списка книг');


    if (is_numeric($_REQUEST['submit'])) {
	if (isset($actions[$_REQUEST['submit']]) && is_array($actions[$_REQUEST['submit']])) {
	    $rst[] = $_REQUEST['submit'];
//  Парсинг содержимого страницы акции с указанным ID
	    $st->start("Парсинг содержимого акции с указанным ID");
	    if (isset($html)) unset($html);
	    $html = readByLink($actions[$_REQUEST['submit']]['href']);
            parseActions($_REQUEST['submit'],$html,$skip);
	} else {
	    $error = true;
	    $errorstr .= '! Не найдена акция ID с указанным ID ['.$_REQUEST['submit'].']';
	}
    } elseif ($_REQUEST['submit'] == 'all') {
//  Цикл по списку акций, парсинг содержимого страницы акции
	$st->start("Парсинг содержимого всех акций");
	if (count($actions) > 0)
	    foreach ($actions as $id => $act) {
	    if ($id == "_time") continue;	    //	"Служебное поле" - время актуализации данных
		if (isset($html)) unset($html);
		$html = readByLink($act['href']);
		parseActions($id,$html,$skip);
    //break;
	    }
    } elseif ($_REQUEST['submit'] == 'selected') {
	if (isset($_REQUEST['chk'])) {			// Параметр chk вообще присутствует
	    if (is_array($_REQUEST['chk']) && count($_REQUEST['chk']) > 0) { // ...он массив, и не пустой
		$st->start("Парсинг содержимого указанных акций");
		foreach($_REQUEST['chk'] as $aid) {
		    if (array_key_exists($aid,$actions)) {
			$rst[] = $aid;
			if (isset($html)) unset($html);
			$html = readByLink($actions[$aid]['href']);
			parseActions($aid,$html,$skip);
		    }
		}
	    } else {	// Передан не массив или пустой массив
		$error = true;
		$errorstr .= "!!!! Wrong array chk[] [".print_r($_REQUEST['chk'],true)."]<br />";
	    }
	} else {    	// Вообще не передан массив
	    $error = true;
	    $errorstr .= '!!!! Missed param chk[] in $_REQUEST ['.print_r($_REQUEST,true)."]<br />\n";
	}
    } else {		// Хз, что передали
	$error = true;
	$errorstr .= '!!!! Wrong $_REQUEST ['.print_r($_REQUEST,true)."]<br />\n";
    }

//var_dump($actions);
    $action['_time'] = time();
    $m->set('actions', $actions, EXPT);

if ($conf['debug']) error_log('END count($actions) ['.count($actions).']');

    if(!$error)
	writeXSLX($rst);
    else {
	$st->error($errorstr);

        header('Content-Type: application/json');
	echo json_encode($st->get());
    }

    $st->stop();

}

?>
