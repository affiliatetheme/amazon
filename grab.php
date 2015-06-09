<?php
/**
 * Copyright 2015 - endcore
 */

//Preis wählbar in der Config!
require_once ABSPATH . '/wp-load.php';
require_once dirname(__FILE__) . '/lib/bootstrap.php';
require_once dirname(__FILE__) . '/config.php';

use ApaiIO\Endcore\Grabber;

$url = $_POST['url'];

if($url == '' || $url == null) {
    die();
}

$asins = new Grabber($url);
$output = array(
    'asins' => $asins->getAsins()
);

echo json_encode($output);
exit();