<?php
/**
 * Copyright 2015 - endcore
 */

//Preis wählbar in der Config!
require_once ABSPATH . '/wp-load.php';
require_once dirname(__FILE__) . '/lib/bootstrap.php';
require_once dirname(__FILE__) . '/config.php';

use ApaiIO\ApaiIO;
use ApaiIO\Helper\DotDotText;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;
use ApaiIO\Zend\Service\Amazon;

$output = array();


echo json_encode($output);
exit();