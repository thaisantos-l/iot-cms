<?php
/** T11/T12/T16/T17 menu/identity coverage in isolated WordPress. */
$argv = array( __FILE__, 'create' );
ob_start();
require __DIR__ . '/navigation-fixture.php';
ob_end_clean();
$qa_fixture_state = get_option( 'iotnest_qa_navigation' );
try {
    $menu_html = wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false, 'echo' => false, 'walker' => new Iotnest\Theme\Primary_Menu() ) );
    iotnest_check( str_contains( $menu_html, 'Soluções' ) && str_contains( $menu_html, '#solucao' ), 'native menu resolves typed section URLs independent of labels' );
    iotnest_check( str_contains( $menu_html, 'Cases' ), 'public case enables conditional collection menu' );
    foreach ( array( 'solution', 'ai', 'assistant', 'defects', 'energy', 'reports' ) as $key ) update_post_meta( $qa_fixture_state['home'], '_iotnest_home_' . $key, array( 'enabled' => false, 'title' => 'Still hidden' ) );
    wp_update_post( array( 'ID' => $qa_fixture_state['case'], 'post_status' => 'draft' ) );
    $menu_html = wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'echo' => false, 'fallback_cb' => false, 'walker' => new Iotnest\Theme\Primary_Menu() ) );
    iotnest_check( ! str_contains( $menu_html, 'Soluções' ) && ! str_contains( $menu_html, 'Cases' ), 'empty group and unavailable collection are pruned together' );
    iotnest_check( str_contains( $menu_html, 'Calculadora' ), 'independent menu targets remain visible' );
    $items = wp_get_nav_menu_items( $qa_fixture_state['menus'][0] );
    $qa_item_id = $items[0]->ID;
    $before = get_post_meta( $qa_item_id, '_iotnest_menu_target', true );
    $_POST = array( 'iotnest_menu_target' => array( $qa_item_id => 'cases' ), 'iotnest_menu_nonce' => array( $qa_item_id => 'invalid' ) );
    Iotnest\Core\save_menu_target( 0, $qa_item_id );
    iotnest_check( $before === get_post_meta( $qa_item_id, '_iotnest_menu_target', true ), 'invalid menu nonce preserves association' );
    wp_set_current_user( 0 );
    $_POST['iotnest_menu_nonce'][$qa_item_id] = wp_create_nonce( 'iotnest_menu_' . $qa_item_id );
    Iotnest\Core\save_menu_target( 0, $qa_item_id );
    iotnest_check( $before === get_post_meta( $qa_item_id, '_iotnest_menu_target', true ), 'menu nonce without capability cannot modify association' );
    $_POST = array();
    wp_set_current_user( get_user_by( 'login', 'iotnest-qa' )->ID );
    iotnest_check( '' === Iotnest\Core\destination_url( array( 'kind' => 'url', 'url' => '#' ) ), 'placeholder URL is not a valid destination' );
    iotnest_check( '' === Iotnest\Core\destination_url( array( 'kind' => 'url', 'url' => 'javascript:alert(1)' ) ), 'unsafe URL cannot produce a CTA' );
    ob_start(); Iotnest\Theme\cta( array( 'label' => '', 'kind' => 'url', 'url' => 'https://example.com' ) ); $html = ob_get_clean();
    iotnest_check( '' === $html, 'missing CTA label omits the entire element' );
    ob_start(); Iotnest\Theme\media_image( $qa_fixture_state['home'] ); $html = ob_get_clean();
    iotnest_check( '' === $html, 'non-image ID does not generate an empty image request' );
    update_post_meta( $qa_fixture_state['home'], '_iotnest_home_order', array() );
    iotnest_check( array() === Iotnest\Core\public_sections(), 'intentional empty section order has no visible anchors' );
    wp_update_post( array( 'ID' => $qa_fixture_state['home'], 'post_status' => 'private' ) );
    iotnest_check( array() === Iotnest\Core\public_sections(), 'administrator does not expose private home anchors' );
} finally {
    $_POST = array();
    $argv = array( __FILE__, 'delete' );
    require __DIR__ . '/navigation-fixture.php';
}
