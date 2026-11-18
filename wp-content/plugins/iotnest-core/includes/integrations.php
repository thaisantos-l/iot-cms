<?php
/**
 * Adapters de fornecedores aprovados. Credenciais e scripts livres não são campos.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Somente fornecedores habilitados entram na configuração pública.
 *
 * @return array
 */
function integration_config() {
	$settings = settings();
	$config   = array();
	// Infra/CMP registra o bridge somente depois de implementar o contrato JS documentado.
	$consent_ready = (bool) apply_filters( 'iotnest_consent_adapter_ready', false );
	if ( ! empty( $settings['youtube'] ) ) {
		$required          = ! empty( $settings['youtube_consent'] );
		$config['youtube'] = array(
			'consent'   => $required,
			'available' => ! $required || $consent_ready,
		);
	}
	// GA4 e GTM exigem o mecanismo acordado. GTM é a fonte única se ambos forem habilitados.
	if ( $consent_ready ) {
		if ( ! empty( $settings['tag_manager'] ) && preg_match( '/\AGTM-[A-Z0-9]+\z/', $settings['gtm_id'] ?? '' ) ) {
			$config['gtm'] = $settings['gtm_id'];
		} elseif ( ! empty( $settings['analytics'] ) && preg_match( '/\AG-[A-Z0-9]+\z/', $settings['ga4_id'] ?? '' ) ) {
			$config['ga4'] = $settings['ga4_id'];
		}
	}
	return $config;
}

/**
 * Diagnóstico administrativo sem dados do visitante ou configuração secreta.
 */
function integration_notices() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'settings_page_iotnest' !== $screen->id ) {
		return;
	}
	$config = settings();
	$health = get_option( 'iotnest_contact_health', array() );
	if ( empty( $config['contact_enabled'] ) || ! is_email( $config['contact_recipient'] ?? '' ) ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Formulário desabilitado ou sem destinatário válido. Configure e valide o transporte SMTP antes de habilitar.', 'iotnest-core' ) . '</p></div>';
	}
	if ( 'failed' === ( $health['code'] ?? '' ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'O último envio falhou. Consulte o diagnóstico do plugin SMTP sem habilitar registros de conteúdo dos visitantes.', 'iotnest-core' ) . '</p></div>';
	}
	if ( ( ! empty( $config['analytics'] ) || ! empty( $config['tag_manager'] ) || ! empty( $config['youtube_consent'] ) ) && ! apply_filters( 'iotnest_consent_adapter_ready', false ) ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Integrações dependentes de consentimento permanecem bloqueadas até a instalação do bridge da CMP acordada.', 'iotnest-core' ) . '</p></div>';
	}
}
add_action( 'admin_notices', __NAMESPACE__ . '\\integration_notices' );
