<?php
include_once('inc/config.php');
include_once('inc/phpQuery/phpQuery.php');

//session_start();

//$actions = array();

$apages = array();
$bpages = array();

function readByLink($lnk) {
    global $conf;
$start = microtime(true);

if ($conf['debug']) error_log('readByLink href ['.$lnk.']');

    $doc = phpQuery::newDocumentFile($lnk);

if ($conf['debug']) error_log('readByLink TIME '.round(microtime(true) - $start, 4).' s');
    return $doc;
}

function makePageList($inhtml,&$pg) {
    global $conf;

//    echo "makePageList<br />";
if ($conf['debug']) error_log('makePageList');

// Если нет "разрывов" (...) в списке страниц
    if (count($inhtml->find($conf['pcontainer'].' ul li.next_jump_li')) == 0)
	foreach($inhtml[$conf['pcontainer'].' ul li.paginator_page'] as $li) {
	    if (!pq($li)->hasClass('active'))
		$pg[] = pq($li)->find('a')->attr('href');
	}
    else {
//	echo "Has next_jump_li!<br />\n";
//	echo "First:".$inhtml->find($conf['apcontainer'].' ul li:last a')->attr('href')."<br />\n";
//	echo "Last:".$inhtml->find($conf['apcontainer'].' ul li a.last')->attr('href')."<br />\n";
	$llnk = $inhtml->find($conf['pcontainer'].' ul li a.last')->attr('href');
	list($baselnk, $lnum ) = explode("=", $llnk);
	for ($i=2; $i <= $lnum; $i++)
	    $pg[] = $baselnk."=".$i;
    }
}


function parseActListPage($inhtml) {
    global $conf;
    global $actions;
//    $actions = &$_SESSION['actions'];
    global $st;

    $curact = array();

//    echo "parseActListPage<br />";
if ($conf['debug']) error_log('parseActListPage');


//    foreach($inhtml->find($conf['acontainer'].' li div.wrap-post') as $act) {
    foreach($inhtml[$conf['acontainer'].' li div.wrap-post'] as $act) {
	if (count(pq($act)->find('div.discount')) > 0 ) {
	    $curact['dsc'] = pq($act)->find('div.discount span.number')->eq(0)->text();
	    $curact['href'] = pq($act)->find('div.image a')->eq(0)->attr('href');
	    $curact['title'] = htmlspecialchars_decode(trim(pq($act)->find('div.content div.title a')->eq(0)->text()));
	    $id = basename($curact['href']);

	    $actions[$id] = $curact;
//	    $st->inc('aprocessed');
	    $st->set_aprocessed(count($actions));
	}
    }
if ($conf['debug']) error_log('parseActListPage count: ['.count($actions).']');
    $st->set_aprocessed(count($actions));
}

function _getDatesFromStr($s) {
    global $conf;
    global $actions;
//    $actions = &$_SESSION['actions'];

//  Период проведения акции с 15 октября по 22 октября 2019 года
    $months = array('января' => '01', 'февраля' => '02', 'марта' => '03', 'апреля' => '04', 'мая' => '05', 'июня' => '06', 'июля' => '07', 'августа' => '08', 'сентября' => '09', 'октября' => '10', 'ноября' => '11', 'декабря' => '12');
//    $months = ['января' => '01', 'февраля' => '02', 'марта' => '03', 'апреля' => '04', 'мая' => '05', 'июня' => '06', 'июля' => '07', 'августа' => '08', 'сентября' => '09', 'октября' => '10', 'ноября' => '11', 'декабря' => '12'];

    list( , , , , $sd, $sm, , $ed, $em, $y ) = explode(" ", $s);

    $sdate = "$sd ".mb_strtolower($sm)." $y";
    $arr = explode(' ', $sdate);
    $sdate = $arr[2].'-'.$months[$arr[1]].'-'.$arr[0];
    $edate = "$ed ".mb_strtolower($em)." $y";
    $arr = explode(' ', $edate);
    $edate = $arr[2].'-'.$months[$arr[1]].'-'.$arr[0];

    return array('sdate' => $sdate, 'edate' => $edate);
}

function parseActPage($id,$inhtml,$skip=false) {
    global $conf;
    global $actions;
//    $actions = &$_SESSION['actions'];
    global $st;
    global $m;

//    echo "parseActPage $id<br />";
if ($conf['debug']) error_log('parseActPage id ['.$id.'] skip ['.($skip ? 'true' : 'false').']');


    foreach($inhtml[$conf['bcontainer'].' li.item'] as $b) {
//	    $book = array();

// При установленном флаге skip пропускаем книги без скидок
	    if ((count(pq($b)->find('div.discount')) == 0) && $skip) continue;

	    $bid = pq($b)->attr('data-product-id');

	    $actions[$id]['books'][$bid] = array();
	    $book = &$actions[$id]['books'][$bid];

	    if (count(pq($b)->find('div.discount')) == 0) {
		$book['dsc'] = '';
		$book['old-price'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.price_button .regular-price span.price')->eq(0)->text()));
		$book['special-price'] = '';
	    } else {
		$book['dsc'] = pq($b)->find('div.discount')->eq(0)->text();
		$book['old-price'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.price_button .old-price span.price')->eq(0)->text()));
		$book['special-price'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.price_button p.special-price span.price')->eq(0)->text()));
	    }

	    $book['href'] = pq($b)->find('div.content a')->eq(0)->attr('href');
	    $book['code'] = pq($b)->find('div.label-sku span.code')->eq(0)->text();
	    $book['name'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.name div.full-name')->eq(0)->text()));
	    $book['autor'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.autor div.product-author')->eq(0)->text()));
	    $book['publisher'] = pq($b)->find('div.additional-information div#attribute-book_publisher span.val')->eq(0)->text();
	    $book['lang'] = pq($b)->find('div.additional-information div#attribute-book_lang span.val')->eq(0)->text();
	    $book['year'] = pq($b)->find('div.additional-information div#attribute-book_year span.val')->eq(0)->text();

//var_dump($book);
//	    $actions[$id]['books'][$bid] = $book;
	    unset($book);

	    $st->inc('bprocessed');
//	    $book['dsc'] = pq($b)->find('div.discount')[0]->text();
//	    $book['href'] = pq($b)->find('div.content a')[0]->attr('href');
//	    $book['code'] = pq($b)->find('div.label-sku span.code')[0]->text();
//	    $book['name'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.name div.full-name')[0]->text()));
//	    $book['autor'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.autor div.product-author')[0]->text()));
//	    $book['old-price'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.price_button p.old-price span.price')[0]->text()));
//	    $book['special-price'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.price_button p.special-price span.price')[0]->text()));
//	    $book['publisher'] = pq($b)->find('div.additional-information div#attribute-book_publisher span.val')[0]->text();
//	    $book['lang'] = pq($b)->find('div.additional-information div#attribute-book_lang span.val')[0]->text();
//	    $book['year'] = pq($b)->find('div.additional-information div#attribute-book_year span.val')[0]->text();
//
//var_dump($book);
//	    $actions[$id]['books'][$bid] = $book;
//	    unset($book);

//break;
    }
    $st->set_bprocessed(count($actions[$id]['books']));
if ($conf['debug']) error_log('parseActPage id ['.$id.'] book_count['.count($actions[$id]['books']).']');
    $m->set('actions', $actions, EXPT);
}

function parseActions($id,$inhtml,$skip=false) {
    global $conf;
    global $actions;
    global $st;
    global $m;
//    $actions = &$_SESSION['actions'];

$start = microtime(true);

    $exp = time() - $actions[$id]['books']['_time'];
if ($conf['debug']) error_log('! prepareB $actions['.$id.'][books][_time] : ['.$actions[$id]['books']['_time'].']');
if ($conf['debug']) error_log('! prepareB $exp : ['.$exp.']');
if ($conf['debug']) error_log('! prepareB ACTT : ['.ACTT.']');
//    if ((time() - $action['_time']) < ACTT) {
    if (($exp) < ACTT) {
//	echo json_encode(array('action' => "Данные актуальны..."));
	$st->set_action("Данные по акции актуальны...");
    } else {

if ($conf['debug']) error_log('parseActions id ['.$id.'] skip ['.($skip ? 'true' : 'false').']');

//  Дополнение массива акций данными Начало/Коцец периода
	$tstr = trim($inhtml['div.blog-post div.content div.date-wrap'][0]->text());
	$actions[$id] = array_merge($actions[$id],_getDatesFromStr($tstr));

	$actions[$id]['bpages'] = array();
// Формирование массива ссылок страниц списка книг в текущей акции
	$st->set_action("Формирование списка страниц книг");
	makePageList($inhtml,$actions[$id]['bpages']);

	$actions[$id]['books'] = array();

	$st->set_action("Парсинг книг на первой странице акции");
	parseActPage($id,$inhtml,$skip);

// Если список книг больше чем на одну страницу - цикл по страницам 2+
	$cnt = 1;
	if (count($actions[$id]['bpages']) > 0)
	    foreach ($actions[$id]['bpages'] as $curlnk) {
		if (isset($html)) unset($html);
		$cnt++;
		$st->set_action("Парсинг книг на ".$cnt."й странице акции");
		$html = readByLink($curlnk);
		parseActPage($id,$html,$skip);
	    }

	$actions[$id]['books']['_time'] = time();

if ($conf['debug']) error_log('parseActions id ['.$id.'] book_count['.count($actions[$id]['books']).']');

	$action['_time'] = time();
	$m->set('actions', $actions, EXPT);

    }
if ($conf['debug']) error_log('TIME '.round(microtime(true) - $start, 4).' s');
}

function parseActionPeriod($id,$inhtml) {
    global $conf;
    global $actions;
//    $actions = &$_SESSION['actions'];
    global $st;

$start = microtime(true);

if ($conf['debug']) error_log('parseActionPeriod id ['.$id.']');

//  Дополнение массива акций данными Начало/Коцец периода
    $tstr = trim($inhtml['div.blog-post div.content div.date-wrap'][0]->text());
    $actions[$id] = array_merge($actions[$id],_getDatesFromStr($tstr));

if ($conf['debug']) error_log('parseActionPeriod TIME '.round(microtime(true) - $start, 4).' s');
}

function prepareActionList() {
    global $conf;
    global $actions;
    global $st;
//    $actions = &$_SESSION['actions'];

    $html = readByLink($conf['rootlnk'].$conf['abooks']);
//    $html = readByLink('tmp/alist.html');

// Формирование массива ссылок страниц списка акций
    $st->set_action("Формирование списка страниц акций");
    makePageList($html,$apages);
//var_dump($apages);

// Заполнение массива акций данными Название/Скидка/Ссылка
    $st->set_action("Заполнение списка акций");
    parseActListPage($html);
//var_dump($actions);

// Если список акций больше чем на одну страницу - цикл по страницам 2+
    if (count($apages) > 0)
	foreach ($apages as $curlnk) {
	    $html = readByLink($curlnk);
	    parseActListPage($html);
	}

//  Цикл по списку акций, парсинг содержимого страницы акции
    $st->set_action("Получения периода действия акций");
    $st->set('aprocessed',0);
    if (count($actions) > 0)
	foreach ($actions as $id => $act) {
	    if ($id == "_time") continue;	    //	"Служебное поле" - время актуализации данных
	    $html = readByLink($act['href']);
	    parseActionPeriod($id,$html);
	    $st->inc('aprocessed');
	}
    $st->set_aprocessed(count($actions));
}

?>
