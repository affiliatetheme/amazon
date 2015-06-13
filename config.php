<?php
/**
 * config
 * Copyright 2015 - endcore
 */
require_once ABSPATH.'/wp-load.php';

define('AWS_COUNTRY', get_option('amazon_country'));
define('AWS_API_KEY', get_option('amazon_public_key'));
define('AWS_API_SECRET_KEY', get_option('amazon_secret_key'));
define('AWS_ASSOCIATE_TAG', get_option('amazon_partner_id'));
define('AWS_PRICE', 'default');
define('AWS_METAKEY_ID', 'amazon_asin');
define('AWS_CRON_HASH', md5(get_option('amazon_public_key') . get_option('amazon_secret_key')));