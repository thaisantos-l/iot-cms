<?php
/**
 * Gravação REST segue o mesmo contrato de integridade dos campos nativos.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Preserva propriedades não enviadas e recusa IDs duplicados antes de gravar.
 *
 * @param object           $prepared Conteúdo preparado pelo controller.
 * @param \WP_REST_Request $request Requisição autenticada pelo WordPress.
 * @return object|\WP_Error
 */
function rest_prepare( $prepared, $request ) {
	$meta = $request->get_param( 'meta' );
	if ( ! is_array( $meta ) ) {
		return $prepared;
	}
	$type    = $prepared->post_type ?? 'page';
	$id      = absint( $request['id'] ?? 0 );
	$schemas = meta_schemas( $type );
	$own     = array_intersect_key( $meta, $schemas );
	if ( ! $own ) {
		return $prepared;
	}
	if ( ! ( $id ? current_user_can( 'edit_post', $id ) : current_user_can( get_post_type_object( $type )->cap->create_posts ) ) ) {
		return new \WP_Error( 'iotnest_forbidden', 'Sem permissão para estes campos.', array( 'status' => 403 ) );
	}
	$valid = validate_meta_patch( $id, $own, $type );
	if ( is_wp_error( $valid ) ) {
		$valid->add_data( array( 'status' => 400 ) );
		return $valid;
	}
	$request->set_param( 'meta', array_replace( $meta, $valid ) );
	return $prepared;
}

add_filter( 'rest_pre_insert_page', __NAMESPACE__ . '\\rest_prepare', 10, 2 );
add_filter( 'rest_pre_insert_iot_case', __NAMESPACE__ . '\\rest_prepare', 10, 2 );

/**
 * Preserva o envelope esperado por get_metadata_raw no preview de arrays/objetos.
 *
 * O filtro nativo retorna o valor single da revisão; a API de metadata desembrulha
 * arrays de short-circuit novamente. Corrigir só os campos registrados do projeto.
 *
 * @param mixed  $value Resultado do filtro nativo.
 * @param int    $object_id ID público sendo visualizado.
 * @param string $key Chave de meta.
 * @param bool   $single Consulta de um único valor.
 * @return mixed
 */
function preview_array_value( $value, $object_id, $key, $single ) {
	global $post;
	if ( $single && is_array( $value ) && $post && $post->ID === $object_id && isset( meta_schemas( $post->post_type )[ $key ] ) && has_filter( 'get_post_metadata', '_wp_preview_meta_filter' ) && current_user_can( 'edit_post', $object_id ) ) {
		return array( $value );
	}
	return $value;
}

add_filter( 'get_post_metadata', __NAMESPACE__ . '\\preview_array_value', 11, 4 );
