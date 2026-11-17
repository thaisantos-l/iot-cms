<?php
/**
 * Configurações operacionais sem credenciais ou scripts arbitrários.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Contrato de opções administrativas.
 *
 * @return array
 */
function settings_schema() {
	$fields = array(
		'email'             => field( 'email', 'E-mail público' ),
		'phone'             => field( 'text', 'Telefone' ),
		'address'           => field( 'text', 'Endereço' ),
		'whatsapp'          => field( 'url', 'Link do WhatsApp' ),
		'platform'          => field( 'url', 'URL da plataforma' ),
		'platform_label'    => field( 'text', 'Texto de acesso à plataforma' ),
		'commercial_cta'    => cta_schema(),
		'copyright'         => field( 'text', 'Copyright (incluindo o ano editorial)' ),
		'socials'           => repeat(
			'Redes sociais',
			array(
				'label' => field( 'text', 'Nome' ),
				'icon'  => field( 'icon', 'Ícone' ),
				'url'   => field( 'url', 'URL' ),
			),
			10
		),
		'contact_enabled'   => field( 'boolean', 'Habilitar formulário (requer serviço de envio validado)' ),
		'contact_recipient' => field( 'email', 'Destinatário autorizado do formulário' ),
		'contact_subject'   => field( 'text', 'Assunto do e-mail de contato' ),
		'ga4_id'            => field( 'text', 'ID GA4' ),
		'gtm_id'            => field( 'text', 'ID GTM' ),
	);
	foreach ( array(
		'motion'      => 'Animações',
		'lenis'       => 'Rolagem Lenis',
		'autoplay'    => 'Autoplay dos componentes',
		'counters'    => 'Contadores',
		'dada_scroll' => 'Rolagem da Dadá',
		'calculator'  => 'Calculadora',
		'youtube'     => 'Player YouTube',
		'analytics'   => 'Analytics',
		'tag_manager' => 'Gerenciador de tags',
	) as $key => $label ) {
		$fields[ $key ] = field( 'boolean', $label );
	}
	return group( 'Contatos, conteúdo global e integrações', $fields );
}

/**
 * Defaults operacionais; nenhum texto editorial é restabelecido.
 *
 * @return array
 */
function settings() {
	return array_merge(
		array(
			'motion'          => true,
			'lenis'           => true,
			'autoplay'        => true,
			'counters'        => true,
			'dada_scroll'     => true,
			'calculator'      => true,
			'youtube'         => true,
			'analytics'       => false,
			'tag_manager'     => false,
			'contact_enabled' => false,
		),
		(array) get_option( 'iotnest_settings', array() )
	);
}

/** Registra opções apenas para administração, sem exposição pública via REST. */
function register_settings() {
	register_setting(
		'iotnest',
		'iotnest_settings',
		array(
			'type'              => 'object',
			'show_in_rest'      => false,
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_settings',
		)
	);
}

/**
 * Decodifica submissão íntegra e preserva opções não enviadas.
 *
 * @param mixed $value Entrada.
 * @return array
 */
function sanitize_settings( $value ) {
	$old = (array) get_option( 'iotnest_settings', array() );
	if ( ! current_user_can( 'manage_options' ) ) {
		return $old;
	}
	if ( is_string( $value ) ) {
		$value = strlen( $value ) <= 1048576 ? json_decode( $value, true, 32 ) : null;
	}
	if ( ! is_array( $value ) ) {
		add_settings_error( 'iotnest', 'invalid', 'Configurações incompletas; os valores anteriores foram preservados.' );
		return $old;
	}
	$field = settings_schema();
	$value = merge_patch( $old, $value, $field );
	$valid = rest_validate_value_from_schema( $value, json_schema( $field ) );
	if ( is_wp_error( $valid ) || ! unique_ids( $value, $field ) ) {
		add_settings_error( 'iotnest', 'invalid', 'Configurações inválidas; os valores anteriores foram preservados.' );
		return $old;
	}
	foreach ( array(
		'ga4_id' => '/\AG-[A-Z0-9]+\z/',
		'gtm_id' => '/\AGTM-[A-Z0-9]+\z/',
	) as $key => $pattern ) {
		if ( ! empty( $value[ $key ] ) && ! preg_match( $pattern, $value[ $key ] ) ) {
			add_settings_error( 'iotnest', 'invalid', 'Identificador de integração inválido.' );
			return $old;
		}
	}
	return normalize( $value, $field );
}

/** Adiciona a tela com a capacidade administrativa nativa. */
function settings_menu() {
	add_options_page( 'iotnest', 'iotnest', 'manage_options', 'iotnest', __NAMESPACE__ . '\\settings_page' );
}

/** Renderiza as opções; nonce e capacidade são conferidos novamente pelo options.php. */
function settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="wrap"><h1>Configurações iotnest</h1><p>Credenciais permanecem no plugin SMTP. Integrações que exigem consentimento só poderão carregar após a configuração da etapa de integrações.</p>';
	echo '<form action="options.php" method="post">';
	settings_fields( 'iotnest' );
	echo '<div class="iotnest-admin" data-iotnest-admin data-settings>';
	render_field( settings_schema(), settings() );
	echo '<input type="hidden" name="iotnest_settings" data-payload value=""><p role="status" aria-live="polite" data-status></p></div>';
	submit_button();
	echo '</form></div>';
}
