<?php
/**
 * Invalidação coalescida após gravação completa e política segura sem motor comprovado.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Acumula eventos, sem executar purge antes dos metadados e taxonomias terminarem de gravar.
 *
 * @param string $reason Motivo.
 * @param int    $object_id Objeto afetado.
 */
function queue_purge( $reason, $object_id = 0 ) {
	$GLOBALS['iotnest_purge_queue'][ sanitize_key( $reason ) ][ absint( $object_id ) ] = true;
}

/**
 * Purge integral inclui dependências de menus, listas, páginas e identidade global.
 *
 * @return array|null Evento com resultado, útil no diagnóstico/teste.
 */
function flush_purge() {
	$queue = $GLOBALS['iotnest_purge_queue'] ?? array();
	if ( ! $queue ) {
		return null;
	}
	$GLOBALS['iotnest_purge_queue'] = array();
	$event                          = array(
		'scope'   => 'site',
		'site_id' => get_current_blog_id(),
		'time'    => microtime( true ),
		'reasons' => $queue,
	);
	// O adapter deve retornar true somente após todas as camadas confirmarem o purge.
	$success                   = true === apply_filters( 'iotnest_cache_purge', false, $event );
	$event['verified_adapter'] = $success;
	update_option(
		'iotnest_cache_health',
		array(
			'success' => $success,
			'time'    => $event['time'],
		),
		false
	);
	do_action( 'iotnest_cache_purged', $event );
	return $event;
}
add_action( 'shutdown', __NAMESPACE__ . '\\flush_purge', PHP_INT_MAX );

/**
 * Gravações de posts públicos ou antes públicos atingem todas as listas dependentes.
 *
 * @param int      $post_id Post.
 * @param \WP_Post $post Registro final.
 */
function post_cache_event( $post_id, $post ) {
	if ( ! $post || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( in_array( $post->post_type, array( 'post', 'page', 'iot_case', 'attachment', 'nav_menu_item' ), true ) ) {
		queue_purge( 'post', $post_id );
	}
}
add_action( 'wp_after_insert_post', __NAMESPACE__ . '\\post_cache_event', 100, 2 );
add_action( 'before_delete_post', __NAMESPACE__ . '\\post_cache_event', 10, 2 );

/**
 * Meta em APIs separadas também invalida depois do último write da requisição.
 *
 * @param mixed  $meta_id Identificador ou lista de IDs.
 * @param int    $object_id Post.
 * @param string $key Meta.
 */
function meta_cache_event( $meta_id, $object_id, $key ) {
	unset( $meta_id );
	if ( str_starts_with( $key, '_iotnest_' ) || str_starts_with( $key, '_yoast_wpseo_' ) || in_array( $key, array( '_thumbnail_id', '_wp_attachment_metadata', '_wp_attached_file' ), true ) ) {
		post_cache_event( $object_id, get_post( $object_id ) );
	}
}
foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) {
	add_action( $hook, __NAMESPACE__ . '\\meta_cache_event', 100, 3 );
}

/**
 * Configurações e menus alteram o HTML sem build.
 *
 * @param string $option Nome da opção.
 */
function option_cache_event( $option ) {
	if ( 'iotnest_settings' === $option || str_starts_with( $option, 'theme_mods_' ) || str_starts_with( $option, 'wpseo' ) || in_array( $option, array( 'blogname', 'blogdescription', 'site_icon', 'show_on_front', 'page_on_front', 'page_for_posts', 'permalink_structure', 'blog_public' ), true ) ) {
		queue_purge( 'option' );
	}
}
foreach ( array( 'added_option', 'updated_option', 'deleted_option' ) as $hook ) {
	add_action( $hook, __NAMESPACE__ . '\\option_cache_event', 100, 1 );
}
foreach ( array( 'wp_update_nav_menu', 'created_term', 'edited_term', 'delete_term', 'set_object_terms' ) as $hook ) {
	add_action(
		$hook,
		static function () {
			queue_purge( 'taxonomy_or_menu' );
		},
		100
	);
}

/**
 * Sem adapter medido não existe autorização para cache duradouro de HTML.
 */
function html_cache_policy() {
	$health   = get_option( 'iotnest_cache_health', array() );
	$verified = defined( 'IOTNEST_HTML_CACHE_VERIFIED' ) && true === IOTNEST_HTML_CACHE_VERIFIED && has_filter( 'iotnest_cache_purge' ) && ! empty( $health['success'] );
	if ( ! $verified || is_user_logged_in() || is_preview() || post_password_required() || 'get' !== sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) ) {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();
		return;
	}
	// Mesmo após a homologação do adapter, expiração máxima evita stale prolongado.
	header( 'Cache-Control: public, max-age=0, s-maxage=10, must-revalidate' );
}
add_action( 'template_redirect', __NAMESPACE__ . '\\html_cache_policy', 0 );
