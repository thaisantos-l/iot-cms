<?php
/** Browser fixtures for the actual imported content, restricted to disposable QA. */
require __DIR__ . '/bootstrap.php';
wp_set_current_user( get_user_by( 'login', 'iotnest-qa' )->ID );
$action = $argv[1] ?? '';
$state = get_option( 'iotnest_qa_home', array() );
if ( 'create' === $action ) {
    if ( $state || get_option( 'iotnest_seed_registry' ) ) throw new RuntimeException( 'Fixture already exists.' );
    $state = array( 'mods' => get_theme_mods(), 'options' => array() );
    foreach ( array( 'show_on_front', 'page_on_front', 'page_for_posts', 'iotnest_settings' ) as $key ) $state['options'][$key] = get_option( $key, null );
    $report = Iotnest\Core\import_institutional();
    if ( is_wp_error( $report ) ) throw new RuntimeException( $report->get_error_message() );
    $result = Iotnest\Core\setup_institutional();
    if ( is_wp_error( $result ) ) throw new RuntimeException( $result->get_error_message() );
    $state['home'] = $report['pages']['home'];
    $state['defaults'] = array();
    foreach ( Iotnest\Core\meta_schemas( 'page' ) as $key => $schema ) $state['defaults'][$key] = get_post_meta( $state['home'], $key, true );
    update_option( 'iotnest_qa_home', $state );
    echo wp_json_encode( array( 'home' => $state['home'] ) );
} elseif ( 'restore' === $action ) {
    foreach ( $state['defaults'] as $key => $value ) update_post_meta( $state['home'], $key, $value );
} elseif ( 'mutate' === $action ) {
    $patch = json_decode( $argv[2], true, 32, JSON_THROW_ON_ERROR );
    foreach ( $patch as $key => $value ) update_post_meta( $state['home'], '_iotnest_home_' . $key, $value );
} elseif ( 'delete' === $action ) {
    foreach ( (array) get_option( 'iotnest_seed_menus', array() ) as $id ) wp_delete_nav_menu( $id );
    foreach ( (array) get_option( 'iotnest_seed_registry', array() ) as $key => $id ) {
        if ( str_starts_with( $key, 'media:' ) ) wp_delete_attachment( $id, true ); else wp_delete_post( $id, true );
    }
    foreach ( array( 'iotnest_seed_registry', 'iotnest_seed_menus', 'iotnest_seed_settings_done', 'iotnest_seed_lock', 'iotnest_qa_home' ) as $key ) delete_option( $key );
    foreach ( $state['options'] as $key => $value ) { if ( null === $value ) delete_option( $key ); else update_option( $key, $value ); }
    remove_theme_mods();
    foreach ( $state['mods'] ?: array() as $key => $value ) set_theme_mod( $key, $value );
} else { throw new RuntimeException( 'Unknown fixture action.' ); }
