<?php
include_once('inc/config.php');
include('inc/phpQuery/phpQuery.php');

//session_start();

//$actions = array();

$apages = array();
$bpages = array();

function readByLink($lnk) {
error_log('readByLink href ['.$lnk.']');

    $doc = phpQuery::newDocumentFile($lnk);

    return $doc;
}

function makePageList($inhtml,&$pg) {
    global $conf;

//    echo "makePageList<br />";
error_log('makePageList');

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

    $actions = &$_SESSION['actions'];
    $curact = array();

//    echo "parseActListPage<br />";
error_log('parseActListPage');


//    foreach($inhtml->find($conf['acontainer'].' li div.wrap-post') as $act) {
    foreach($inhtml[$conf['acontainer'].' li div.wrap-post'] as $act) {
	if (count(pq($act)->find('div.discount')) > 0 ) {
	    $curact['dsc'] = pq($act)->find('div.discount span.number')[0]->text();
	    $curact['href'] = pq($act)->find('div.image a')[0]->attr('href');
	    $curact['title'] = htmlspecialchars_decode(trim(pq($act)->find('div.content div.title a')[0]->text()));
	    $id = basename($curact['href']);

	    $actions[$id] = $curact;
	}
    }
error_log('parseActListPage count: ['.count($actions).']');
}

function _getDatesFromStr($s) {
    global $conf;
    $actions = &$_SESSION['actions'];

//  Период проведения акции с 15 октября по 22 октября 2019 года
    $months = ['января' => '01', 'февраля' => '02', 'марта' => '03', 'апреля' => '04', 'мая' => '05', 'июня' => '06', 'июля' => '07', 'августа' => '08', 'сентября' => '09', 'октября' => '10', 'ноября' => '11', 'декабря' => '12'];

    list( , , , , $sd, $sm, , $ed, $em, $y ) = explode(" ", $s);

    $sdate = "$sd ".strtolower($sm)." $y";
    $arr = explode(' ', $sdate);
    $sdate = $arr[2].'-'.$months[$arr[1]].'-'.$arr[0];
    $edate = "$ed ".strtolower($em)." $y";
    $arr = explode(' ', $edate);
    $edate = $arr[2].'-'.$months[$arr[1]].'-'.$arr[0];

    return array('sdate' => $sdate, 'edate' => $edate);
}

function parseActPage($id,$inhtml) {
    global $conf;
    $actions = &$_SESSION['actions'];

//    echo "parseActPage $id<br />";
error_log('parseActPage id ['.$id.']');


    foreach($inhtml[$conf['bcontainer'].' li.item'] as $b) {
//	    $book = array();

	    $bid = pq($b)->attr('data-product-id');

	    $actions[$id]['books'][$bid] = array();
	    $book = &$actions[$id]['books'][$bid];

	    if (count(pq($b)->find('div.discount')) == 0) {
		$book['dsc'] = '';
		$book['old-price'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.price_button .regular-price span.price')[0]->text()));
		$book['special-price'] = '';
	    } else {
		$book['dsc'] = pq($b)->find('div.discount')[0]->text();
		$book['old-price'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.price_button .old-price span.price')[0]->text()));
		$book['special-price'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.price_button p.special-price span.price')[0]->text()));
	    }

	    $book['href'] = pq($b)->find('div.content a')[0]->attr('href');
	    $book['code'] = pq($b)->find('div.label-sku span.code')[0]->text();
	    $book['name'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.name div.full-name')[0]->text()));
	    $book['autor'] = htmlspecialchars_decode(trim(pq($b)->find('div.caption tr.autor div.product-author')[0]->text()));
	    $book['publisher'] = pq($b)->find('div.additional-information div#attribute-book_publisher span.val')[0]->text();
	    $book['lang'] = pq($b)->find('div.additional-information div#attribute-book_lang span.val')[0]->text();
	    $book['year'] = pq($b)->find('div.additional-information div#attribute-book_year span.val')[0]->text();

//var_dump($book);
//	    $actions[$id]['books'][$bid] = $book;
	    unset($book);

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
error_log('parseActPage id ['.$id.'] book_count['.count($actions[$id]['books']).']');

}

function parseActions($id,$inhtml,$fullparse=true) {
    global $conf;
    $actions = &$_SESSION['actions'];

$start = microtime(true);

//    echo "parseActions $id<br />";
error_log('parseActions id ['.$id.'] fullparse ['.($fullparse ? 'true' : 'false').']');

//  Дополнение массива акций данными Начало/Коцец периода
    $tstr = trim($inhtml['div.blog-post div.content div.date-wrap'][0]->text());
    $actions[$id] = array_merge($actions[$id],_getDatesFromStr($tstr));

    if($fullparse) {
	$actions[$id]['bpages'] = array();
    // Формирование массива ссылок страниц списка книг в текущей акции
	makePageList($inhtml,$actions[$id]['bpages']);

    //
	$actions[$id]['books'] = array();

	parseActPage($id,$inhtml);

    // Если список книг больше чем на одну страницу - цикл по страницам 2+
	if (count($actions[$id]['bpages']) > 0)
	    foreach ($actions[$id]['bpages'] as $curlnk) {
		if (isset($html)) unset($html);
		
		$html = readByLink($curlnk);
		parseActPage($id,$html);
	    }

    //var_dump($actions[$id]['books']);
    error_log('parseActions id ['.$id.'] book_count['.count($actions[$id]['books']).']');

    }
error_log('TIME '.round(microtime(true) - $start, 4).' s');
}



?>