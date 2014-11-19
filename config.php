<?php
require_once ABSPATH.'/wp-load.php';

define('AWS_COUNTRY', get_option('amazon_country'));
define('AWS_API_KEY', get_option('amazon_public_key'));
define('AWS_API_SECRET_KEY', get_option('amazon_secret_key'));
define('AWS_ASSOCIATE_TAG', get_option('amazon_partner_id'));
// valid types ['default', 'list', 'new', 'used', 'collect', 'refurbished']
define('AWS_PRICE', 'default');