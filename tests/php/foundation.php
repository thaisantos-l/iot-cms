<?php
/** T01: activation is non-destructive and assets/hooks are usable without the plugin. */
require __DIR__ . '/bootstrap.php';
global $wpdb;
$before = $wpdb->get_results( "SELECT ID, post_content, post_status, post_type FROM {$wpdb->posts} ORDER BY ID", ARRAY_A );
$theme = wp_get_theme( 'iotnest' );
iotnest_check( ! $theme->errors(), 'theme recognized by WordPress' );
$result = activate_plugin( 'iotnest-core/iotnest-core.php' );
iotnest_check( ! is_wp_error( $result ), 'plugin activates without error' );
switch_theme( 'iotnest' );
deactivate_plugins( 'iotnest-core/iotnest-core.php' );
$result = activate_plugin( 'iotnest-core/iotnest-core.php' );
iotnest_check( ! is_wp_error( $result ), 'reactivation succeeds' );
$after = $wpdb->get_results( "SELECT ID, post_content, post_status, post_type FROM {$wpdb->posts} ORDER BY ID", ARRAY_A );
iotnest_check( $before === $after, 'activation, theme switch and reactivation preserve content' );
iotnest_check( 0 === count( $after ), 'zero institutional pages, blog posts or cases created' );
iotnest_check( ! get_option( 'page_on_front' ), 'no home is assigned implicitly' );
iotnest_check( ! get_option( 'iotnest_settings' ), 'no editorial defaults are written on activation' );
$fixture = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Edição preservada', 'post_content' => 'Conteúdo editorial de QA' ) );
update_post_meta( $fixture, '_iotnest_test_editorial', array( 'title' => '0', 'items' => array() ) );
$saved = get_post( $fixture, ARRAY_A );
deactivate_plugins( 'iotnest-core/iotnest-core.php' );
activate_plugin( 'iotnest-core/iotnest-core.php' );
iotnest_check( $saved === get_post( $fixture, ARRAY_A ), 'reactivation preserves an existing editorial draft' );
iotnest_check( array( 'title' => '0', 'items' => array() ) === get_post_meta( $fixture, '_iotnest_test_editorial', true ), 'reactivation preserves existing complex metadata' );
wp_delete_post( $fixture, true );
