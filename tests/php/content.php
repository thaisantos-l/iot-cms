<?php
/** T04/T05/T11/T14/T17 on a real, disposable WordPress database. */
require __DIR__ . '/bootstrap.php';
require_once ABSPATH . 'wp-admin/includes/post.php';
require_once ABSPATH . 'wp-admin/includes/template.php';
use function Iotnest\Core\has_text;
use function Iotnest\Core\hidden_reason;
use function Iotnest\Core\has_public_content;

$admin = get_user_by( 'login', 'iotnest-qa' );
wp_set_current_user( $admin->ID );
iotnest_check( post_type_exists( 'iot_case' ) && taxonomy_exists( 'iot_case_sector' ), 'cases and sectors registered independently of theme' );
iotnest_check( post_type_supports( 'iot_case', 'revisions' ) && post_type_supports( 'iot_case', 'editor' ), 'cases retain editor and revisions' );
foreach ( array( '', '  ', '<p><br></p>', '&nbsp;', "\u{200B}\u{FEFF}" ) as $empty ) {
    iotnest_check( ! has_text( $empty ), 'empty markup, whitespace and invisible text are absent' );
}
iotnest_check( has_text( '0' ) && has_text( 0 ), 'zero is meaningful' );
iotnest_check( count( Iotnest\Core\meta_schemas( 'page' ) ) === 21, 'twenty sections and order have explicit schemas' );

$fixture_page = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'QA campos', 'post_author' => $admin->ID ) );
$subscriber = wp_create_user( 'iotnest-subscriber', wp_generate_password(), 'subscriber@example.invalid' );
$hero = '_iotnest_home_hero';
$order = '_iotnest_home_order';
$faq = '_iotnest_home_faq';
$post_ids = array();
function iotnest_submit( $id, $patch, $changes = array() ) {
    $_POST = array_merge( array( '_iotnest_nonce' => wp_create_nonce( 'iotnest_fields_' . $id ), '_iotnest_complete' => '1', '_iotnest_payload' => wp_slash( wp_json_encode( $patch ) ) ), $changes );
    wp_update_post( array( 'ID' => $id ) );
    $_POST = array();
}
try {
    iotnest_submit( $fixture_page, array( $hero => array( 'enabled' => true, 'title' => 'Original', 'description' => 'Preservada' ), $order => array( 'hero', 'faq' ), $faq => array( 'enabled' => true, 'title' => 'FAQ', 'items' => array( array( 'id' => 'a', 'question' => 'Q1', 'answer' => 'A1' ), array( 'id' => 'b', 'question' => 'Q2', 'answer' => '' ) ) ) ) );
    iotnest_check( 'Original' === get_post_meta( $fixture_page, $hero, true )['title'], 'native save persists registered fields' );
    $revisions = wp_get_post_revisions( $fixture_page );
    $original_revision = array_key_first( $revisions );
    iotnest_check( 'Original' === get_post_meta( $original_revision, $hero, true )['title'], 'native revision captures fields after save' );
    iotnest_check( 1 === count( Iotnest\Core\valid_items( 'faq', get_post_meta( $fixture_page, $faq, true ) ) ), 'incomplete rows remain editable but do not render' );
    iotnest_submit( $fixture_page, array( $hero => array( 'title' => '0' ), $order => array( 'faq', 'hero' ) ) );
    iotnest_check( array( 'enabled' => true, 'title' => '0', 'description' => 'Preservada' ) === get_post_meta( $fixture_page, $hero, true ), 'partial submission preserves properties and zero' );
    iotnest_check( array( 'faq', 'hero' ) === get_post_meta( $fixture_page, $order, true ), 'reordering persists without changing section identifiers' );
    iotnest_submit( $fixture_page, array( $hero => array( 'title' => '' ) ) );
    iotnest_check( 'Campo principal vazio.' === hidden_reason( 'hero', get_post_meta( $fixture_page, $hero, true ) ), 'intentional clearing hides section without fallback' );
    $before = get_post_meta( $fixture_page );
    iotnest_submit( $fixture_page, array( $hero => array( 'title' => 'Rejected' ) ), array( '_iotnest_nonce' => 'invalid' ) );
    iotnest_check( $before === get_post_meta( $fixture_page ), 'invalid nonce cannot change fields' );
    iotnest_submit( $fixture_page, array( $hero => array( 'title' => 'Rejected' ) ), array( '_iotnest_complete' => '' ) );
    iotnest_check( $before === get_post_meta( $fixture_page ), 'truncated submission preserves every field' );
    iotnest_submit( $fixture_page, array(), array( '_iotnest_payload' => '{broken' ) );
    iotnest_check( $before === get_post_meta( $fixture_page ), 'malformed JSON preserves every field' );
    iotnest_submit( $fixture_page, array( $hero => array( 'title' => 'Rejected', 'unrecognized' => true ) ) );
    iotnest_check( $before === get_post_meta( $fixture_page ), 'unknown properties fail before any write' );
    iotnest_submit( $fixture_page, array( $hero => array( 'title' => 'Rejected' ), $order => array( 'hero', 'hero' ) ) );
    iotnest_check( $before === get_post_meta( $fixture_page ), 'duplicate IDs reject the whole patch' );
    $oversized_items = array_map( static fn( $n ) => array( 'id' => 'q' . $n, 'question' => 'Q', 'answer' => 'A' ), range( 1, 101 ) );
    iotnest_submit( $fixture_page, array( $faq => array( 'items' => $oversized_items ) ) );
    iotnest_check( $before === get_post_meta( $fixture_page ), 'repeater limit rejects complete patch without truncation' );
    iotnest_submit( $fixture_page, array(), array( '_iotnest_payload' => str_repeat( 'x', 1048577 ) ) );
    iotnest_check( $before === get_post_meta( $fixture_page ), 'payload over 1 MiB preserves every field' );
    wp_update_post( array( 'ID' => $fixture_page, 'post_title' => 'Quick edit' ) );
    iotnest_check( $before === get_post_meta( $fixture_page ), 'quick edit without payload preserves fields' );
    wp_set_current_user( $subscriber );
    iotnest_submit( $fixture_page, array( $hero => array( 'title' => 'Rejected' ) ) );
    iotnest_check( $before === get_post_meta( $fixture_page ), 'valid nonce without edit capability is denied' );
    wp_set_current_user( $admin->ID );
    wp_restore_post_revision( $original_revision );
    iotnest_check( 'Original' === get_post_meta( $fixture_page, $hero, true )['title'] && array( 'hero', 'faq' ) === get_post_meta( $fixture_page, $order, true ), 'restoration recovers complex metadata and order' );

    $_POST = array( '_iotnest_nonce' => wp_create_nonce( 'iotnest_fields_' . $fixture_page ), '_iotnest_complete' => '1', '_iotnest_payload' => wp_slash( wp_json_encode( array( $hero => array( 'title' => 'Preview only' ), $order => array( 'faq', 'hero' ) ) ) ) );
    $autosave = wp_create_post_autosave( array( 'ID' => $fixture_page, 'post_ID' => $fixture_page, 'post_type' => 'page', 'post_title' => 'Preview' ) );
    $_POST = array();
    iotnest_check( ! is_wp_error( $autosave ) && 'Preview only' === get_post_meta( $autosave, $hero, true )['title'], 'native autosave stores preview metadata in its revision' );
    iotnest_check( 'Original' === get_post_meta( $fixture_page, $hero, true )['title'], 'preview does not update public metadata' );
    $GLOBALS['post'] = get_post( $fixture_page );
    add_filter( 'get_post_metadata', '_wp_preview_meta_filter', 10, 4 );
    iotnest_check( 'Preview only' === get_post_meta( $fixture_page, $hero, true )['title'] && array( 'faq', 'hero' ) === get_post_meta( $fixture_page, $order, true ), 'WordPress preview resolves the saved complex fields' );
    remove_filter( 'get_post_metadata', '_wp_preview_meta_filter', 10 );

    iotnest_submit( $fixture_page, array( $hero => array( 'title' => '<span class="evil" onclick="alert(1)">Título</span><script>alert(2)</script>' ) ) );
    $safe = get_post_meta( $fixture_page, $hero, true )['title'];
    iotnest_check( ! str_contains( $safe, 'onclick' ) && ! str_contains( $safe, '<script' ) && ! str_contains( $safe, 'evil' ), 'malicious headline markup and classes removed' );
    iotnest_check( '' === Iotnest\Core\normalize( 'javascript:alert(1)', Iotnest\Core\field( 'url', 'URL' ) ), 'unsafe URL removed' );
    iotnest_check( 'Seção desabilitada.' === hidden_reason( 'hero', array( 'enabled' => false, 'title' => 'Título' ) ), 'explicit disabled flag hides otherwise complete section' );
    iotnest_check( 'A lista não contém itens completos.' === hidden_reason( 'faq', array( 'enabled' => true, 'title' => 'Título', 'items' => array() ) ), 'zero rows hides required list' );
    iotnest_check( '' === hidden_reason( 'faq', array( 'enabled' => true, 'title' => 'Título', 'items' => array( array( 'question' => '0', 'answer' => '0' ), array( 'question' => 'Q', 'answer' => 'A' ) ) ) ), 'several valid rows and zero text remain available' );
    iotnest_check( 'Vídeo e poster ausentes ou inválidos.' === hidden_reason( 'assistant', array( 'enabled' => true, 'title' => 'Título', 'poster' => $fixture_page ) ), 'non-attachment image does not satisfy media dependency' );
    iotnest_check( ! Iotnest\Core\valid_video( array( 'provider' => 'youtube', 'youtube' => '<iframe>' ) ), 'arbitrary video markup rejected' );
    Iotnest\Core\register_settings();
    update_option( 'iotnest_settings', array( 'phone' => 'Original', 'calculator' => false ) );
    iotnest_check( 'Calculadora desabilitada nas configurações.' === hidden_reason( 'calculator', array( 'enabled' => true, 'title' => 'Título' ) ), 'operational flag controls availability' );
    $settings_before = get_option( 'iotnest_settings' );
    wp_set_current_user( $subscriber );
    update_option( 'iotnest_settings', array( 'phone' => 'Rejected' ) );
    iotnest_check( $settings_before === get_option( 'iotnest_settings' ), 'operational settings require manage_options' );
    wp_set_current_user( $admin->ID );
    update_option( 'iotnest_settings', '{broken' );
    iotnest_check( $settings_before === get_option( 'iotnest_settings' ), 'invalid settings JSON preserves existing options' );
    delete_option( 'iotnest_settings' );


    $request = new WP_REST_Request( 'POST', '/wp/v2/pages/' . $fixture_page );
    $request->set_param( 'meta', array( $hero => array( 'title' => 'REST partial' ) ) );
    $response = rest_do_request( $request );
    iotnest_check( 200 === $response->get_status() && 'Preservada' === ( get_post_meta( $fixture_page, $hero, true )['description'] ?? null ), 'REST partial update preserves unspecified fields' );
    $request = new WP_REST_Request( 'POST', '/wp/v2/pages/' . $fixture_page );
    $request->set_param( 'meta', array( $order => array( 'hero', 'hero' ) ) );
    iotnest_check( 400 === rest_do_request( $request )->get_status(), 'REST rejects repeated IDs' );
    wp_set_current_user( 0 );
    $request = new WP_REST_Request( 'POST', '/wp/v2/pages/' . $fixture_page );
    $request->set_param( 'meta', array( $hero => array( 'title' => 'Rejected' ) ) );
    iotnest_check( 401 === rest_do_request( $request )->get_status(), 'anonymous REST write denied' );
    $response = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/pages/' . $fixture_page ) );
    iotnest_check( ! isset( $response->get_data()['meta'][ $hero ] ), 'protected editorial metadata not exposed in public REST view' );
    wp_set_current_user( $admin->ID );

    foreach ( array( 'draft', 'pending', 'future', 'private', 'publish' ) as $status ) {
        $id = wp_insert_post( array( 'post_type' => 'iot_case', 'post_status' => $status, 'post_title' => 'QA ' . $status, 'post_date' => 'future' === $status ? '2035-01-01 12:00:00' : current_time( 'mysql' ), 'post_password' => 'publish' === $status ? 'protected' : '' ) );
        $post_ids[] = $id;
    }
    iotnest_check( ! has_public_content( 'iot_case' ), 'draft, pending, future, private and password-protected cases do not count for administrator' );
    $published = wp_insert_post( array( 'post_type' => 'iot_case', 'post_status' => 'publish', 'post_title' => 'Public QA' ) );
    $post_ids[] = $published;
    iotnest_check( has_public_content( 'iot_case' ), 'first public case makes collection available' );
    wp_update_post( array( 'ID' => $published, 'post_status' => 'draft' ) );
    iotnest_check( ! has_public_content( 'iot_case' ), 'removing last public case hides collection immediately' );
} finally {
    $_POST = array();
    remove_filter( 'get_post_metadata', '_wp_preview_meta_filter', 10 );
    wp_set_current_user( $admin->ID );
    foreach ( array_merge( array( $fixture_page ), $post_ids ) as $id ) wp_delete_post( $id, true );
    require_once ABSPATH . 'wp-admin/includes/user.php';
    wp_delete_user( $subscriber );
    delete_transient( 'iotnest_error_' . $admin->ID );
    delete_option( 'iotnest_settings' );
}
