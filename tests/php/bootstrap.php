<?php
/** Real WordPress integration tests are allowed only in our disposable installation. */
$wp_root = getenv( 'IOTNEST_TEST_WP_ROOT' );
if ( ! $wp_root || ! is_file( $wp_root . '/.iotnest-test' ) || realpath( $wp_root ) === realpath( __DIR__ . '/../..' ) ) {
    fwrite( STDERR, "Use npm run test:integration; refusing to test against an editorial database.\n" );
    exit( 1 );
}
require_once $wp_root . '/wp-load.php';
if ( ! defined( 'IOTNEST_TEST_ENV' ) || ! IOTNEST_TEST_ENV || 'iotnest_qa_' !== $GLOBALS['table_prefix'] ) {
    throw new RuntimeException( 'Disposable database guard failed.' );
}
require_once ABSPATH . 'wp-admin/includes/plugin.php';

function iotnest_check( $condition, $message ) {
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
    echo "PASS: $message\n";
}
