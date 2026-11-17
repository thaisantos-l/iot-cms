<?php
/** Change only the disposable test plugin state. */
require __DIR__ . '/bootstrap.php';
if ( 'off' === ( $argv[1] ?? '' ) ) {
    deactivate_plugins( 'iotnest-core/iotnest-core.php' );
} elseif ( 'on' === ( $argv[1] ?? '' ) ) {
    $result = activate_plugin( 'iotnest-core/iotnest-core.php' );
    iotnest_check( ! is_wp_error( $result ), 'plugin enabled' );
} else {
    throw new RuntimeException( 'Expected on or off.' );
}
