<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if(!defined('EXPT')) define('EXPT', 7200);  // Время хранения в memcache
if(!defined('ACTT')) define('ACTT', 1800);   // Время "актуальности" сохранённого

$db_conf = array(
    'type'      => 'firebird',
    'host'      => 'ss',
    'db'        => 'd:/fb/iscra_d.fdb',
//'db' => 'd:/fb/iscra.fdb',
    'user'      => 'SYSDBA',
    'pass'      => '',
    'charset'   => 'WIN1251',
    'dialect'   => '3',
    'ver'       => 'FB_1.5',
);


$conf = array(
    'rootlnk'	=> 'http://www.yakaboo.ua',
    'abooks'	=> '/promotions/index/index/id/4723/',
    'pcontainer'   => 'div.pagination',
    'acontainer'   => 'ul.blog-posts__list',
    'bcontainer'   => 'ul.products-grid',
    'debug'	    =>	false,
);

date_default_timezone_set('Europe/Kiev');
mb_internal_encoding("UTF-8");

?>
