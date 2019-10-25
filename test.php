<?php
include_once('inc/config.php');
include('inc/xlsxwriter.class.php');

include_once('inc/yap.php');

if (!isset($_REQUEST['actid']))
//    if (!isset($_SESSION['actions']))
	$_SESSION['actions'] = array();

$actions = &$_SESSION['actions'];


$apages = array();
$bpages = array();

function  writeXSLX() {
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
      'book_dsc'=>'integer',
      'book_old-price'=>'price',
      'book_special-price'=>'price',
      'book_sitelink'=>'string',
    );
    //  'quantity'=>'#,##0',

    $writer = new XLSXWriter();
//    $sheet_name = 'yap_full';
    $sheet_name = 'yap_full';
    $filename = 'yap_'.date('ymd');

    $writer->writeSheetHeader($sheet_name, $header );

    foreach ($actions as $aid => $act) {
	if (isset($act['books']) && is_array($act['books']))
	foreach ($act['books'] as $book) {
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

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
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

$out = <<<EOT
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
	<style type="text/css">
	    table {font-size:12px;color:#333333;border-width: 1px;border-color: #729ea5;border-collapse: collapse;}
	    table th {text-align: center;font-size:12px;background-color:#acc8cc;border-width: 1px;padding: 8px;border-style: solid;border-color: #729ea5;text-align:left;}
	    table tr {background-color:#d4e3e5;}
	    table td {font-size:12px;border-width: 1px;padding: 4px 8px;border-style: solid;border-color: #729ea5;text-align: center;}
	    table td.left {text-align: left;}
	    table td.right {text-align: right;}
	    table tr:hover {background-color:#ffffff;}
	    button {
		cursor: pointer;
		padding: 4px 8px;
		background-color: #acc8cc;
		border-radius:5px;
		/* border: 3px solid #1b4869; */
		}
	    button:hover {background-color:#d4e3e5;}
	</style>
	<script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

    </head>
    <body>
    <script type="text/javascript" charset="utf-8">
	$(document).ready(function() {
	    $('#selectAll').click(function(e){
		var table= $(e.target).closest('table');
		$('td input:checkbox',table).prop('checked',this.checked);
	    });
	});
    </script>
	<h3>Список актульных акций на сайте</h3><br />

	<form action="" method="post" target="_blank">
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

    foreach ($actions as $id => $act) {
$out .= <<<EOT
		<tr id="$id]">
		    <td><INPUT type="checkbox" name="chk[]" value="$id"></td>
		    <td class="left"><a href="$act[href]">$act[title]</a></td>
		    <td>{$df($act["sdate"])}</td>
		    <td>{$df($act["edate"])}</td>
		    <td class="right">$act[dsc] %</td>
		    <td><button type="submit" name="submit" value="$id">load</button></td>
		</tr>
EOT;

    }
//		    <td><button type="submit" name="actid" value="$id">load</button></td>
//		    <td>&ensp;</td>


$out .= <<<EOT
	    </table>
	    <br />
	    <button type="submit" name="submit" value="selected">load selected</button>
	    <button type="submit" name="submit" value="all">load all</button>
	</form>
    </body>
</html>
EOT;

//	    <button type="submit" name="actid" value="selected">load selected</button>

//	    <input type="submit" name="submit" value="Submit"/>

//	    <button name="actid" value="" onclick="loadselected()">load selected</button>

echo $out;

}


if (is_array($_REQUEST) && count($_REQUEST)) {
//var_dump($_REQUEST);
        print_r($_REQUEST);
die();
}
error_log('$_REQUEST[actid] ['.$_REQUEST['actid'].']');

error_log('START count($actions) ['.count($actions).']');

if (isset($_REQUEST['submit'])) {
    $error = false;
    $errorstr = '';

    if (is_numeric($_REQUEST['submit'])) {
	if (isset($actions[$_REQUEST['submit']]) && is_array($actions[$_REQUEST['submit']])) {
//  Парсинг содержимого страницы акции с указанным ID
	    if (isset($html)) unset($html);
	    $html = readByLink($actions[$_REQUEST['submit']]['href']);
            parseActions($_REQUEST['actid'],$html);
	} else {
	    $error = true;
	    $errorstr .= '!!!! Can`t find actions with ID ['.$_REQUEST['submit'].']<br />\n';
	}
    } elseif ($_REQUEST['submit'] == 'all') {
//  Цикл по списку акций, парсинг содержимого страницы акции
	if (count($actions) > 0)
	    foreach ($actions as $id => $act) {
		if (isset($html)) unset($html);
		$html = readByLink($act['href']);
		parseActions($id,$html);
    //break;
	    }
    } elseif ($_REQUEST['submit'] == 'selected') {
	if (isset($actions[$_REQUEST['chk']])) {
	    if (is_array($actions[$_REQUEST['chk']]) && count($actions[$_REQUEST['chk']]) > 0) {

	    } else {	// Передан не массив или пустой массив
		$error = true;
		$errorstr .= '!!!! Wrong array chk[] ['.print_r($_REQUEST['chk']).']<br />\n';
	    }
	} else {    	// Вообще не передан массив
	    $error = true;
	    $errorstr .= '!!!! Missed param chk[] in $_REQUEST ['.print_r($_REQUEST).']<br />\n';
	}
    } else {		// Хз, что передали
	$error = true;
	$errorstr .= '!!!! Wrong $_REQUEST ['.print_r($_REQUEST).']<br />\n';
    }

//var_dump($actions);

error_log('END count($actions) ['.count($actions).']');

    if(!$error)
	writeXSLX();
    else {
        header('Content-Type: text/html; charset=utf-8');
	echo $errorstr;
    }
    
} else {
//function main() {
//    global $conf;
//    global $apages;
//    global $actions;

    $html = readByLink($conf['rootlnk'].$conf['abooks']);
//    $html = readByLink('tmp/alist.html');

// Формирование массива ссылок страниц списка акций
    makePageList($html,$apages);
//var_dump($apages);

// Заполнение массива акций данными Название/Скидка/Ссылка
    parseActListPage($html);
//var_dump($actions);

// Если список акций больше чем на одну страницу - цикл по страницам 2+
    if (count($apages) > 0)
	foreach ($apages as $curlnk) {
	    $html = readByLink($curlnk);
	    parseActListPage($html);
	}

//  Цикл по списку акций, парсинг содержимого страницы акции
    if (count($actions) > 0)
	foreach ($actions as $id => $act) {
	    $html = readByLink($act['href']);
	    parseActions($id,$html,false);
//break;
	}

//var_dump($actions);

//}
error_log('END count($actions) ['.count($actions).']');


    showActTbl();
}

?>