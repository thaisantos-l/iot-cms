<?php
/** Browser fixtures; guarded by the disposable installation marker. */
require __DIR__ . '/bootstrap.php';
$admin = get_user_by( 'login', 'iotnest-qa' );
wp_set_current_user( $admin->ID );
$action = $argv[1] ?? '';
$fixture_id = absint( $argv[2] ?? 0 );
if ( 'create' === $action ) {
    $fixture_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'QA editor', 'post_author' => $admin->ID, 'page_template' => 'page-templates/institutional.php' ) );
    update_post_meta( $fixture_id, '_iotnest_home_hero', array( 'enabled' => true, 'title' => 'Título público', 'description' => 'Descrição preservada' ) );
    echo wp_json_encode( array( 'id' => $fixture_id ) );
} elseif ( 'read' === $action ) {
    $values = array();
    foreach ( Iotnest\Core\meta_schemas( 'page' ) as $key => $schema ) $values[$key] = get_post_meta( $fixture_id, $key, true );
    $autosave = wp_get_post_autosave( $fixture_id, $admin->ID );
    echo wp_json_encode( array( 'meta' => $values, 'autosave' => $autosave ? get_post_meta( $autosave->ID, '_iotnest_home_hero', true ) : null ) );
} elseif ( 'delete' === $action ) {
    wp_delete_post( $fixture_id, true );
} else {
    throw new RuntimeException( 'Unknown fixture action.' );
}
