<?php
/** Disposable-only validation of the contact service. pre_wp_mail prevents delivery. */
require __DIR__ . '/bootstrap.php';
$previous = get_option( 'iotnest_settings', null );
$health = get_option( 'iotnest_contact_health', null );
$post_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'QA contato' ) );
update_post_meta( $post_id, '_iotnest_home_contact', array( 'enabled' => true, 'title' => 'Contato QA', 'form_title' => 'Contato QA' ) );
update_option( 'iotnest_settings', array( 'contact_enabled' => true, 'contact_recipient' => 'approved@example.test', 'contact_subject' => 'Contato QA' ) );
wp_set_current_user( 0 );
$sent = array(); $mode = true;
$transport = function ( $return, $attributes ) use ( &$sent, &$mode ) { $sent[] = $attributes; return $mode; };
add_filter( 'pre_wp_mail', $transport, 10, 2 );
$payload = function ( $patch = array() ) use ( $post_id ) {
    return array_merge( array( 'page_id' => $post_id, 'nome' => 'Pessoa QA', 'email' => 'visitor@example.test', 'tel' => '123', 'empresa' => 'Empresa QA', 'msg' => '__iotnest_body_not_persisted__', 'website' => '' ), Iotnest\Core\contact_token( $post_id ), $patch );
};
try {
    $input = $payload();
    $input['recipient'] = 'attacker@example.test';
    $result = Iotnest\Core\process_contact( $input, '192.0.2.1' );
    iotnest_check( 'accepted' === $result['code'] && count( $sent ) === 1, 'anonymous valid form reaches wp_mail once' );
    iotnest_check( 'approved@example.test' === $sent[0]['to'] && 'Contato QA' === $sent[0]['subject'], 'recipient and subject are exclusively authorized server settings' );
    iotnest_check( in_array( 'Reply-To: visitor@example.test', $sent[0]['headers'], true ) && ! str_contains( implode( '\n', $sent[0]['headers'] ), 'From:' ), 'visitor only supplies validated Reply-To' );
    $result = Iotnest\Core\process_contact( $input, '192.0.2.1' );
    iotnest_check( 'accepted' === $result['code'] && count( $sent ) === 1, 'duplicate token returns known acceptance without another delivery' );
    foreach ( array( array( 'email' => "valid@example.test\r\nBcc:bad@example.test" ), array( 'nome' => '' ), array( 'nome' => '<script>bad()</script>' ), array( 'email' => array() ), array( 'tel' => str_repeat( '1', 41 ) ), array( 'msg' => str_repeat( 'á', 5001 ) ), array( 'website' => 'https://bot.example.test' ) ) as $patch ) {
        $result = Iotnest\Core\process_contact( $payload( $patch ), '192.0.2.2' );
        iotnest_check( 'invalid' === $result['code'] && count( $sent ) === 1, 'invalid type, size, honeypot or header cannot reach transport' );
    }
    $result = Iotnest\Core\process_contact( $payload( array( 'nonce' => 'expired' ) ), '192.0.2.2' );
    iotnest_check( 403 === $result['status'] && 'Pessoa QA' === $result['values']['nome'], 'expired nonce preserves validated fields in request memory' );
    $result = Iotnest\Core\process_contact( $payload( array( 'request_id' => array() ) ), '192.0.2.2' );
    iotnest_check( 403 === $result['status'], 'malformed token is rejected without warnings' );
    foreach ( range( 1, 4 ) as $index ) $result = Iotnest\Core\process_contact( $payload(), '192.0.2.3' );
    iotnest_check( 'limited' === $result['code'] && count( $sent ) === 4, 'rate limiter allows three attempts per minute for one connection' );
    $mode = false; $failed = $payload();
    $result = Iotnest\Core\process_contact( $failed, '192.0.2.4' );
    iotnest_check( 'failed' === $result['code'] && 502 === $result['status'], 'transport failure has a generic public response' );
    $result = Iotnest\Core\process_contact( $failed, '192.0.2.4' );
    iotnest_check( 'uncertain' === $result['code'] && count( $sent ) === 5, 'same failed token never retries an operation automatically' );
    wp_set_current_user( get_user_by( 'login', 'iotnest-qa' )->ID ); $mode = true;
    $result = Iotnest\Core\process_contact( $payload(), '192.0.2.5' );
    iotnest_check( 'accepted' === $result['code'], 'authenticated visitor uses the same validation and service' );
    global $wpdb;
    $persisted = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_value LIKE %s", '%__iotnest_body_not_persisted__%' ) );
    iotnest_check( 0 === (int) $persisted, 'submission body is not stored in options, transients or idempotence records' );
    $diagnostic = get_option( 'iotnest_contact_health' );
    iotnest_check( array( 'code', 'time' ) === array_keys( $diagnostic ), 'operational diagnostic contains no visitor or transport credentials' );
    update_option( 'iotnest_settings', array( 'contact_enabled' => false, 'contact_recipient' => 'approved@example.test' ) );
    iotnest_check( array() === Iotnest\Core\contact_context( $post_id ), 'disabled form is unavailable in server context' );
    update_option( 'iotnest_settings', array( 'contact_enabled' => true, 'contact_recipient' => 'invalid' ) );
    iotnest_check( array() === Iotnest\Core\contact_context( $post_id ), 'invalid recipient never presents an apparently working form' );
} finally {
    remove_filter( 'pre_wp_mail', $transport, 10 ); wp_delete_post( $post_id, true );
    foreach ( array( 'iotnest_settings' => $previous, 'iotnest_contact_health' => $health ) as $key => $value ) { if ( null === $value ) delete_option( $key ); else update_option( $key, $value ); }
    global $wpdb;
    $keys = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'iotnest_contact_request_%' OR option_name LIKE 'iotnest_contact_lock_%' OR option_name LIKE '_transient_iotnest_contact_rate_%' OR option_name LIKE '_transient_timeout_iotnest_contact_rate_%'" );
    foreach ( $keys as $key ) delete_option( $key );
    wp_clear_scheduled_hook( 'iotnest_contact_cleanup' );
}
