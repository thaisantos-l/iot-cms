<?php
/** Keep updater network checks outside the disposable application test suite. */
if ( ! defined( 'IOTNEST_TEST_ENV' ) || ! IOTNEST_TEST_ENV || ! is_file( ABSPATH . '.iotnest-test' ) ) {
    exit;
}
foreach ( array( 'themes', 'plugins' ) as $kind ) {
    add_filter( 'pre_site_transient_update_' . $kind, static function () use ( $kind ) {
        $checked = array();
        if ( 'themes' === $kind ) {
            foreach ( wp_get_themes() as $slug => $theme ) {
                $checked[ $slug ] = $theme->get( 'Version' );
            }
        } else {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            foreach ( get_plugins() as $slug => $plugin ) {
                $checked[ $slug ] = $plugin['Version'];
            }
        }
        return (object) array( 'last_checked' => time(), 'checked' => $checked, 'response' => array(), 'no_update' => array(), 'translations' => array() );
    } );
}
add_filter( 'pre_site_transient_update_core', static function () {
    return (object) array( 'last_checked' => time(), 'version_checked' => get_bloginfo( 'version' ), 'updates' => array() );
} );
