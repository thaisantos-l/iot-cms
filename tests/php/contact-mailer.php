<?php
/** Test-only mail transport. Never installed in the package or editorial WordPress. */
if ( ! defined( 'IOTNEST_TEST_ENV' ) || ! IOTNEST_TEST_ENV || ! is_file( ABSPATH . '.iotnest-test' ) ) return;
add_filter( 'pre_wp_mail', function ( $return ) {
    $mode = get_option( 'iotnest_qa_mail_mode', '' );
    if ( ! in_array( $mode, array( 'success', 'failure' ), true ) ) return $return;
    update_option( 'iotnest_qa_mail_count', (int) get_option( 'iotnest_qa_mail_count', 0 ) + 1, false );
    return 'success' === $mode;
} );
add_filter( 'iotnest_consent_adapter_ready', function () { return (bool) get_option( 'iotnest_qa_cmp', false ); } );
