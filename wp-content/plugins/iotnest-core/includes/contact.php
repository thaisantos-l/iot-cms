<?php
/**
 * Contato público com regra única para HTML e REST. Não persiste submissões.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Configuração autorizada e contexto público do formulário.
 *
 * @param int $page_id Página institucional.
 * @return array
 */
function contact_context( $page_id ) {
	$post    = get_post( $page_id );
	$config  = settings();
	$section = get_post_meta( $page_id, '_iotnest_home_contact', true );
	if ( ! $post || 'page' !== $post->post_type || 'publish' !== $post->post_status || $post->post_password || empty( $config['contact_enabled'] ) || ! is_email( $config['contact_recipient'] ?? '' ) || empty( $section['enabled'] ) || ! has_text( $section['title'] ?? '' ) || ! has_text( $section['form_title'] ?? '' ) ) {
		return array();
	}
	return array(
		'recipient' => $config['contact_recipient'],
		'subject'   => $config['contact_subject'] ?? '',
		'section'   => $section,
	);
}

/**
 * Mensagens operacionais podem ser personalizadas na seção, sem detalhes do transporte.
 *
 * @param string $code Código público.
 * @param array  $section Seção editorial.
 * @return string
 */
function contact_message( $code, $section = array() ) {
	$messages = array(
		'ready'       => '',
		'sending'     => __( 'Enviando…', 'iotnest-core' ),
		'accepted'    => __( 'Mensagem encaminhada ao serviço de envio.', 'iotnest-core' ),
		'invalid'     => __( 'Confira os campos indicados.', 'iotnest-core' ),
		'expired'     => __( 'Sessão renovada. Envie novamente.', 'iotnest-core' ),
		'limited'     => __( 'Aguarde um minuto para reenviar.', 'iotnest-core' ),
		'failed'      => __( 'Envio indisponível. Use outro canal.', 'iotnest-core' ),
		'unavailable' => __( 'Formulário indisponível. Use outro canal.', 'iotnest-core' ),
		'uncertain'   => __( 'Envio incerto. Confirme por outro canal.', 'iotnest-core' ),
	);
	foreach ( $section['messages'] ?? array() as $item ) {
		if ( ( $item['key'] ?? '' ) === $code && has_text( $item['text'] ?? '' ) ) {
			return wp_strip_all_tags( $item['text'] );
		}
	}
	return $messages[ $code ] ?? $messages['failed'];
}

/**
 * Nonce renovável sem cache, vinculado ao formulário e à chave de idempotência.
 *
 * @param int $page_id Página.
 * @return array
 */
function contact_token( $page_id ) {
	$id = wp_generate_uuid4();
	return array(
		'request_id' => $id,
		'nonce'      => wp_create_nonce( 'iotnest_contact_' . $page_id . '_' . $id ),
	);
}

/**
 * Exclui somente os registros operacionais próprios após a retenção curta.
 *
 * @param string $key Nome de opção própria.
 */
function contact_cleanup( $key ) {
	if ( is_string( $key ) && preg_match( '/\Aiotnest_contact_(request|lock)_[a-f0-9]{64}\z/', $key ) ) {
		delete_option( $key );
	}
}
add_action( 'iotnest_contact_cleanup', __NAMESPACE__ . '\\contact_cleanup' );

/**
 * Valida e entrega uma única vez ao transporte. Estado persistido não inclui campos pessoais.
 *
 * @param array  $input Entrada sem slashes.
 * @param string $address IP da conexão, sem confiar em X-Forwarded-For público.
 * @return array
 */
function process_contact( $input, $address ) {
	$result = array(
		'code'   => 'invalid',
		'status' => 422,
		'fields' => array(),
		'values' => array(),
	);
	if ( ! is_array( $input ) ) {
		return $result;
	}
	foreach ( array(
		'nome'    => 120,
		'email'   => 254,
		'tel'     => 40,
		'empresa' => 160,
		'msg'     => 5000,
	) as $key => $limit ) {
		$raw = $input[ $key ] ?? '';
		if ( ! is_string( $raw ) || strlen( $raw ) > $limit * 4 ) {
			$result['fields'][] = $key;
			continue; }
		$raw = trim( $raw );
		if ( ( function_exists( 'mb_strlen' ) ? mb_strlen( $raw ) : strlen( $raw ) ) > $limit || ( 'msg' !== $key && preg_match( '/[\r\n\x00]/', $raw ) ) ) {
			$result['fields'][] = $key;
		}
		$result['values'][ $key ] = 'msg' === $key ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
	}
	foreach ( array( 'nome', 'email' ) as $key ) {
		if ( ! has_text( $result['values'][ $key ] ?? '' ) ) {
			$result['fields'][] = $key;
		}
	}
	if ( ! is_email( $result['values']['email'] ?? '' ) ) {
		$result['fields'][] = 'email';
	}
	$result['fields'] = array_values( array_unique( $result['fields'] ) );
	$page_id          = isset( $input['page_id'] ) && is_scalar( $input['page_id'] ) ? absint( $input['page_id'] ) : 0;
	$context          = contact_context( $page_id );
	if ( ! $context ) {
		$result['code']   = 'unavailable';
		$result['status'] = 503;
		return $result; }
	$id    = $input['request_id'] ?? '';
	$nonce = $input['nonce'] ?? '';
	if ( ! is_string( $id ) || ! preg_match( '/\A[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}\z/', $id ) || ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'iotnest_contact_' . $page_id . '_' . $id ) ) {
		$result['code']   = 'expired';
		$result['status'] = 403;
		return $result;
	}
	if ( ! empty( $input['website'] ) || $result['fields'] ) {
		return $result;
	}
	$key      = 'iotnest_contact_request_' . hash_hmac( 'sha256', $id, wp_salt( 'nonce' ) );
	$previous = get_option( $key, false );
	if ( false !== $previous ) {
		$result['code']   = 'accepted' === $previous ? 'accepted' : 'uncertain';
		$result['status'] = 'accepted' === $previous ? 200 : 409;
		return $result; }
	$address     = filter_var( $address, FILTER_VALIDATE_IP ) ? $address : 'unknown';
	$fingerprint = hash_hmac( 'sha256', $address, wp_salt( 'auth' ) );
	$lock        = 'iotnest_contact_lock_' . $fingerprint;
	if ( ! add_option( $lock, time(), '', false ) ) {
		$result['code']   = 'limited';
		$result['status'] = 429;
		return $result; }
	wp_schedule_single_event( time() + 60, 'iotnest_contact_cleanup', array( $lock ) );
	try {
		$rate_key = 'iotnest_contact_rate_' . $fingerprint;
		$rate     = get_transient( $rate_key );
		if ( is_array( $rate ) && $rate['expires'] > time() && $rate['count'] >= 3 ) {
			$result['code']   = 'limited';
			$result['status'] = 429;
			return $result; }
		if ( ! is_array( $rate ) || $rate['expires'] <= time() ) {
			$rate = array(
				'expires' => time() + 60,
				'count'   => 0,
			);
		}
		++$rate['count'];
		set_transient( $rate_key, $rate, max( 1, $rate['expires'] - time() ) );
		// add_option usa a chave única da tabela para impedir dois envios concorrentes do mesmo token.
		if ( ! add_option( $key, 'pending', '', false ) ) {
			$result['code']   = 'uncertain';
			$result['status'] = 409;
			return $result; }
		wp_schedule_single_event( time() + DAY_IN_SECONDS, 'iotnest_contact_cleanup', array( $key ) );
		$values  = $result['values'];
		$subject = sanitize_text_field( $context['subject'] );
		if ( ! has_text( $subject ) ) {
			$subject = __( 'Contato pelo site', 'iotnest-core' );
		}
		$body = 'Nome: ' . $values['nome'] . "\nE-mail: " . $values['email'] . "\nTelefone: " . $values['tel'] . "\nEmpresa: " . $values['empresa'] . "\n\n" . $values['msg'];
		try {
			$sent = wp_mail( $context['recipient'], $subject, $body, array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $values['email'] ) );
		} catch ( \Throwable $error ) {
			$sent = false; }
		$result['code']   = $sent ? 'accepted' : 'failed';
		$result['status'] = $sent ? 200 : 502;
		update_option( $key, $result['code'], false );
		// Somente código e horário: nenhum destinatário, IP, conteúdo ou erro SMTP no diagnóstico.
		update_option(
			'iotnest_contact_health',
			array(
				'code' => $result['code'],
				'time' => time(),
			),
			false
		);
		return $result;
	} finally {
		delete_option( $lock ); }
}

/** Registra endpoints públicos com nonce próprio e sem respostas cacheáveis. */
function contact_routes() {
	register_rest_route(
		'iotnest/v1',
		'/contact-token',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => function ( $request ) {
				$page_id = absint( $request->get_param( 'page_id' ) );
				$response = new \WP_REST_Response( contact_context( $page_id ) ? contact_token( $page_id ) : array( 'code' => 'unavailable' ), contact_context( $page_id ) ? 200 : 503 );
				$response->header( 'Cache-Control', 'private, no-store, max-age=0' );
				return $response;
			},
		)
	);
	register_rest_route(
		'iotnest/v1',
		'/contact',
		array(
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => function ( $request ) {
				$result = strlen( $request->get_body() ) > 24000 ? array(
					'code'   => 'invalid',
					'status' => 413,
					'fields' => array(),
				) : process_contact( $request->is_json_content_type() ? $request->get_json_params() : $request->get_body_params(), sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );
				unset( $result['values'] );
				$response = new \WP_REST_Response( $result, $result['status'] );
				$response->header( 'Cache-Control', 'private, no-store, max-age=0' );
				return $response;
			},
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\contact_routes' );

/** HTML sem JS: renderiza a mesma página e conserva campos em falha sem querystring/DB. */
function contact_html_request() {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce validado em process_contact.
	if ( 'post' !== ( sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) || 'iotnest_contact' !== ( sanitize_text_field( wp_unslash( $_POST['iotnest_action'] ?? '' ) ) ) ) {
		return;
	}
	nocache_headers();
    // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Tipos, limites e sanitização centralizados.
	$input                             = wp_unslash( $_POST );
	$input['page_id']                  = get_queried_object_id();
	$result                            = process_contact( $input, sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );
	$GLOBALS['iotnest_contact_result'] = $result;
	status_header( $result['status'] );
}
add_action( 'template_redirect', __NAMESPACE__ . '\\contact_html_request', 5 );
