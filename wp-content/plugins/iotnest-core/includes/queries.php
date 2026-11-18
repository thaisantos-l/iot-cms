<?php
/**
 * Consultas públicas e rotas, sem interferir no editor ou preview nativo.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Consulta pública reutilizável durante o request, invalidada pelas alterações nativas.
 *
 * @param string $type Tipo permitido.
 * @param array  $args Filtros e paginação.
 * @return \WP_Query
 */
function public_query( $type, $args = array() ) {
	static $queries = array();
	$type           = in_array( $type, array( 'post', 'iot_case', 'page' ), true ) ? $type : 'post';
	$args           = array_merge(
		array(
			'posts_per_page' => 4,
			'no_found_rows'  => true,
		),
		$args,
		array(
			'post_type'           => $type,
			'post_status'         => 'publish',
			'has_password'        => false,
			'ignore_sticky_posts' => true,
			'orderby'             => array(
				'date' => 'DESC',
				'ID'   => 'DESC',
			),
		)
	);
	$key            = md5( wp_json_encode( $args ) . wp_cache_get_last_changed( 'posts' ) . wp_cache_get_last_changed( 'terms' ) );
	if ( ! isset( $queries[ $key ] ) ) {
		$queries[ $key ] = new \WP_Query( $args );
	}
	return $queries[ $key ];
}

/**
 * Mesmo contrato para arquivos, feeds e busca, inclusive com administrador logado.
 *
 * @param \WP_Query $query Consulta.
 */
function public_main_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || $query->is_singular() || $query->is_preview() ) {
		return;
	}
	if ( ! ( $query->is_home() || $query->is_archive() || $query->is_search() || $query->is_feed() ) ) {
		return;
	}
	$query->set( 'post_status', 'publish' );
	$query->set( 'has_password', false );
	$query->set( 'ignore_sticky_posts', true );
	$query->set(
		'orderby',
		array(
			'date' => 'DESC',
			'ID'   => 'DESC',
		)
	);
	if ( $query->is_search() ) {
		$query->set( 'post_type', array( 'post', 'page', 'iot_case' ) );
	}
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\\public_main_query' );

/**
 * Configuração explícita, somente no setup autorizado; preserva estrutura existente.
 *
 * @return true|\WP_Error
 */
function configure_routes() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return new \WP_Error( 'forbidden', 'Sem permissão para configurar URLs.' );
	}
	$conflict = get_page_by_path( 'cases' );
	if ( $conflict && 'trash' !== $conflict->post_status ) {
		return new \WP_Error( 'route_conflict', 'Existe uma página com o slug cases. Revise o conflito antes de configurar o arquivo; nada foi renomeado.' );
	}
	if ( ! get_option( 'permalink_structure' ) ) {
		$GLOBALS['wp_rewrite']->set_permalink_structure( '/blog/%postname%/' );
	}
	register_content();
	flush_rewrite_rules( false );
	return true;
}

/**
 * Equivalentes legados existentes; nunca cria destinos para exemplos.
 *
 * @param string $file Nome legado.
 * @return string
 */
function legacy_destination( $file ) {
	if ( 'index.html' === $file ) {
		$id = (int) get_option( 'page_on_front' );
		return $id && 'publish' === get_post_status( $id ) && ! get_post_field( 'post_password', $id ) ? home_url( '/' ) : '';
	}
	if ( 'cases.html' === $file ) {
		return (string) get_post_type_archive_link( 'iot_case' );
	}
	if ( 'blog.html' === $file ) {
		$id = (int) get_option( 'page_for_posts' );
	} elseif ( in_array( $file, array( 'privacidade.html', 'cookies.html', 'termos.html' ), true ) ) {
		$page = get_page_by_path( basename( $file, '.html' ) );
		$id   = $page ? $page->ID : 0;
	} else {
		return '';
	}
	return $id && 'publish' === get_post_status( $id ) && ! get_post_field( 'post_password', $id ) ? get_permalink( $id ) : '';
}

/** Redireciona apenas o caminho legado exato e com destino equivalente disponível. */
function legacy_redirect() {
	if ( is_preview() || is_admin() ) {
		return;
	}
	$path = wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH );
	$base = trailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
	if ( ! is_string( $path ) || ! str_starts_with( $path, $base ) ) {
		return;
	}
	$file = substr( $path, strlen( $base ) );
	$url  = legacy_destination( $file );
	if ( $url && wp_safe_redirect( $url, 301, 'iotnest' ) ) {
		exit;
	}
}
add_action( 'template_redirect', __NAMESPACE__ . '\\legacy_redirect', 1 );
