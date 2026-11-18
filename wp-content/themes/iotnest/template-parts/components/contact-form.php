<?php
/**
 * Formulário progressivo no mesmo desenho, sem dados em URLs ou armazenamento local.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
$page_id = get_queried_object_id();
$context = \Iotnest\Core\contact_context( $page_id );
if ( ! $context ) {
	return;
}
$section  = $context['section'];
$result   = $GLOBALS['iotnest_contact_result'] ?? array(
	'code'   => 'ready',
	'fields' => array(),
	'values' => array(),
);
$token    = \Iotnest\Core\contact_token( $page_id );
$messages = array();
foreach ( array( 'ready', 'sending', 'accepted', 'invalid', 'expired', 'limited', 'failed', 'unavailable', 'uncertain' ) as $code ) {
	$messages[ $code ] = \Iotnest\Core\contact_message( $code, $section );
}
$notes = array_column( $section['messages'] ?? array(), 'text', 'key' );
?>
<form class="panel panel--light" data-contact-form method="post" action="<?php echo esc_url( get_permalink( $page_id ) . '#contato' ); ?>" data-token-url="<?php echo esc_url( add_query_arg( 'page_id', $page_id, rest_url( 'iotnest/v1/contact-token' ) ) ); ?>" data-endpoint="<?php echo esc_url( rest_url( 'iotnest/v1/contact' ) ); ?>" novalidate>
<h3><?php echo esc_html( $section['form_title'] ); ?></h3>
<div class="field-row">
<?php
foreach ( array(
	'nome'  => array( 'Nome completo', 'text', 'Seu nome', 'name', 120 ),
	'email' => array( 'E-mail corporativo', 'email', 'email@empresa.com', 'email', 254 ),
) as $name => $field ) :
	?>
<div class="field"><label for="f-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field[0] ); ?></label><input id="f-<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" type="<?php echo esc_attr( $field[1] ); ?>" placeholder="<?php echo esc_attr( $field[2] ); ?>" autocomplete="<?php echo esc_attr( $field[3] ); ?>" maxlength="<?php echo absint( $field[4] ); ?>" value="<?php echo esc_attr( $result['values'][ $name ] ?? '' ); ?>" required<?php echo in_array( $name, $result['fields'], true ) ? ' aria-invalid="true"' : ''; ?>></div>
<?php endforeach; ?></div>
<?php
foreach ( array(
	'tel'     => array( 'Telefone / WhatsApp', 'tel', '(11) 99999-9999', 'tel', 40 ),
	'empresa' => array( 'Empresa', 'text', 'Nome da empresa', 'organization', 160 ),
) as $name => $field ) :
	?>
<div class="field"><label for="f-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field[0] ); ?></label><input id="f-<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" type="<?php echo esc_attr( $field[1] ); ?>" placeholder="<?php echo esc_attr( $field[2] ); ?>" autocomplete="<?php echo esc_attr( $field[3] ); ?>" maxlength="<?php echo absint( $field[4] ); ?>" value="<?php echo esc_attr( $result['values'][ $name ] ?? '' ); ?>"<?php echo in_array( $name, $result['fields'], true ) ? ' aria-invalid="true"' : ''; ?>></div>
<?php endforeach; ?>
<div class="field"><label for="f-msg"><?php esc_html_e( 'Descreva seu projeto ou necessidade', 'iotnest' ); ?></label><textarea id="f-msg" name="msg" rows="4" maxlength="5000" placeholder="<?php esc_attr_e( 'Conte-nos sobre sua operação, número de injetoras, principais desafios...', 'iotnest' ); ?>"<?php echo in_array( 'msg', $result['fields'], true ) ? ' aria-invalid="true"' : ''; ?>><?php echo esc_textarea( $result['values']['msg'] ?? '' ); ?></textarea></div>
<button class="btn btn--block" type="submit"<?php echo 'accepted' === $result['code'] ? ' disabled' : ''; ?>><?php echo esc_html( $section['form_title'] ); ?> <?php icon( 'arrow-send', 18 ); ?></button>
<div class="panel__note contact-status">
<?php
foreach ( array_merge( $messages, array( $notes['note'] ?? '' ) ) as $message ) :
	?>
	<span class="contact-status__reserve" aria-hidden="true"><?php echo esc_html( $message ); ?></span><?php endforeach; ?>
<p data-contact-status tabindex="-1" role="status" aria-live="polite"><?php echo esc_html( 'ready' === $result['code'] ? ( $notes['note'] ?? '' ) : $messages[ $result['code'] ] ); ?></p></div>
<div hidden aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
<input type="hidden" name="iotnest_action" value="iotnest_contact"><input type="hidden" name="page_id" value="<?php echo absint( $page_id ); ?>"><input type="hidden" name="request_id" value="<?php echo esc_attr( $token['request_id'] ); ?>"><input type="hidden" name="nonce" value="<?php echo esc_attr( $token['nonce'] ); ?>">
<script type="application/json" data-contact-messages><?php echo wp_json_encode( $messages, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON com escape HTML. ?></script>
</form>
