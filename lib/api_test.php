<?php
/**
 * Smoke-Test für die neue Amazon Creators API.
 *
 * Nicht produktiv eingebunden — nur für Developer-Debugging.
 * Aufruf via WP-AJAX: /wp-admin/admin-ajax.php?action=at_aws_test&asin=B00EXHLKVY
 */

add_action( 'wp_ajax_amazon_api_test', 'at_aws_test' );
add_action( 'wp_ajax_at_aws_test', 'at_aws_test' );

function at_aws_test() {
    if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG && current_user_can( 'manage_options' ) ) ) {
        wp_die( '-1', 403 );
    }

    if ( function_exists( 'set_time_limit' ) ) {
        @set_time_limit( 180 );
    }

    $asin_raw = isset( $_GET['asin'] ) ? (string) $_GET['asin'] : 'B00EXHLKVY';
    $asin     = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $asin_raw ) );
    if ( ! preg_match( '/^[A-Z0-9]{10}$/', $asin ) ) {
        wp_die( 'Invalid ASIN', '', array( 'response' => 400 ) );
    }

    if ( ! class_exists( '\\Endcore\\AmazonApi' ) ) {
        echo "AmazonApi class not found. Make sure the Creators API client is loaded.\n";
        die;
    }

    try {
        $api = \Endcore\AmazonApi::fromWpOptions();
        $response = $api->getItems( array( $asin ) );

        echo "<pre>";
        var_dump( $response );
        echo "</pre>";
    } catch ( \Throwable $e ) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "File:  " . $e->getFile() . ':' . $e->getLine() . "\n";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }

    die;
}
