<?php
/**
 * Autosave de campos estruturados sem alterar a publicação nem o core.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Atualiza a revisão automática do autor, inclusive quando só os campos mudam.
 *
 * @param int   $post_id ID do conteúdo.
 * @param array $patch Campos já autorizados e validados.
 * @return int|\WP_Error
 */
function store_autosave_fields( $post_id, $patch ) {
	$revision = wp_get_post_autosave( $post_id, get_current_user_id() );
	$id       = $revision ? $revision->ID : _wp_put_post_revision( get_post( $post_id, ARRAY_A ), true );
	if ( is_wp_error( $id ) || ! $id ) {
		return new \WP_Error( 'iotnest_autosave', 'Não foi possível criar a revisão automática.' );
	}
	foreach ( meta_schemas( get_post_type( $post_id ) ) as $key => $schema ) {
		if ( array_key_exists( $key, $patch ) || metadata_exists( 'post', $post_id, $key ) ) {
			update_metadata( 'post', $id, $key, wp_slash( $patch[ $key ] ?? get_post_meta( $post_id, $key, true ) ) );
		}
	}
	return $id;
}

/**
 * Heartbeat já autenticado; exige também nonce próprio e capacidade do objeto.
 *
 * @param array $response Resposta do heartbeat.
 * @param array $data Dados desserializados e sem slashes pelo WordPress.
 * @return array
 */
function heartbeat_fields( $response, $data ) {
	$request = $data['iotnest_fields'] ?? null;
	if ( ! is_array( $request ) ) {
		return $response;
	}
	$post_id = absint( $request['post_id'] ?? 0 );
	$patch   = validated_patch( $post_id, wp_slash( $request ) );
	if ( is_wp_error( $patch ) || null === $patch || ! get_post( $post_id ) || wp_check_post_lock( $post_id ) ) {
		$response['iotnest_fields'] = array( 'success' => false );
		return $response;
	}
	$result                     = store_autosave_fields( $post_id, $patch );
	$response['iotnest_fields'] = array( 'success' => ! is_wp_error( $result ) );
	return $response;
}

/**
 * Recria a revisão se o preview nativo descartou o autosave por texto idêntico.
 *
 * @param string   $link Link gerado pelo core.
 * @param \WP_Post $post Conteúdo.
 * @return string
 */
function preview_fields_link( $link, $post ) {
	// A função central confere nonce e capacidade antes de qualquer gravação.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! is_admin() || 'dopreview' !== ( isset( $_POST['wp-preview'] ) && is_string( $_POST['wp-preview'] ) ? sanitize_key( wp_unslash( $_POST['wp-preview'] ) ) : '' ) ) {
		return $link;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$patch = validated_patch( $post->ID, $_POST );
	if ( is_wp_error( $patch ) || null === $patch || in_array( $post->post_status, array( 'draft', 'auto-draft' ), true ) ) {
		return $link;
	}
	$result = store_autosave_fields( $post->ID, $patch );
	if ( is_wp_error( $result ) ) {
		return $link;
	}
	return add_query_arg(
		array(
			'preview_id'    => $post->ID,
			'preview_nonce' => wp_create_nonce( 'post_preview_' . $post->ID ),
		),
		$link
	);
}

add_filter( 'heartbeat_received', __NAMESPACE__ . '\\heartbeat_fields', 20, 2 );
add_filter( 'preview_post_link', __NAMESPACE__ . '\\preview_fields_link', 20, 2 );
