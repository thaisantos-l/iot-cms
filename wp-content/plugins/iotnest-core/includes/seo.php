<?php
/**
 * Yoast permanece dono das tags; o fallback usa a API nativa do WordPress.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * O sitemap nativo obedece à mesma política pública das listagens.
 *
 * @param array $args Consulta do provider.
 * @return array
 */
function sitemap_public_query( $args ) {
	$args['has_password'] = false;
	$args['post_status']  = 'publish';
	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', __NAMESPACE__ . '\\sitemap_public_query' );

/**
 * Conteúdo protegido e previews não devem ser indexados, mesmo após autenticação.
 *
 * @param array $robots Diretivas nativas.
 * @return array
 */
function private_robots( $robots ) {
	$post = get_queried_object();
	if ( is_preview() || ( $post instanceof \WP_Post && $post->post_password ) ) {
		$robots['noindex']   = true;
		$robots['noarchive'] = true;
		unset( $robots['index'] );
	}
	return $robots;
}
add_filter( 'wp_robots', __NAMESPACE__ . '\\private_robots' );

/**
 * A mesma proteção na representação do Yoast, sem imprimir outra tag.
 *
 * @param array $robots Diretivas Yoast.
 * @return array
 */
function yoast_private_robots( $robots ) {
	$post = get_queried_object();
	if ( is_preview() || ( $post instanceof \WP_Post && $post->post_password ) ) {
		$robots['index']   = 'noindex';
		$robots['archive'] = 'noarchive';
	}
	return $robots;
}
add_filter( 'wpseo_robots_array', __NAMESPACE__ . '\\yoast_private_robots' );
