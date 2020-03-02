<?php
use Endcore\Grabber;

add_action('wp_ajax_at_aws_grab', 'at_aws_grab');
add_action('wp_ajax_nopriv_at_aws_grab', 'at_aws_grab');
add_action('wp_ajax_amazon_api_grab', 'at_aws_grab');
add_action('wp_ajax_nopriv_amazon_api_grab', 'at_aws_grab');
function at_aws_grab($url = '', $direct = false) {
	if(!$direct) $url = $_POST['url'];

	if($url == '' || $url == null) {
		die();
	}

	$asins = new Grabber($url);
	$output = array(
		'asins' => $asins->getAsins()
	);

	if($direct) return $output;
	echo json_encode($output);
	exit();
}
