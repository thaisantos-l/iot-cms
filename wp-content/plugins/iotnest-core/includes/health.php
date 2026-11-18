<?php
/**
 * Atestação pública mínima de código carregado pelo runtime, sem configuração de ambiente.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/** Registra a saúde usada somente após a troca de release. */
function health_route() {
	register_rest_route(
		'iotnest/v1',
		'/health',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => function () {
				global $wp_version;
				$plugin = is_file( __DIR__ . '/release.php' ) ? require __DIR__ . '/release.php' : array();
				$path = get_template_directory() . '/inc/release.php';
				$theme = 'iotnest' === get_template() && is_file( $path ) ? require $path : array();
				$data = array(
					'plugin'    => $plugin['commit'] ?? 'development',
					'theme'     => $theme['commit'] ?? 'unavailable',
					'schema'    => SCHEMA_VERSION,
					'php'       => PHP_VERSION,
					'wordpress' => $wp_version,
				);
				$response = new \WP_REST_Response( $data );
				$response->header( 'Cache-Control', 'no-store, max-age=0' );
				return $response;
			},
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\health_route' );
