<?php
/**
 * Destinos editoriais persistem independentemente do tema.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Âncoras estáveis da referência, sem inferência pelo texto do link.
 *
 * @return array
 */
function section_anchors() {
	return array_combine( section_keys(), array( 'topo', 'recursos', 'solucao', 'hardware', 'ia', 'dada', 'apontamento', 'energia', 'relatorios', 'mess', 'calculadora', 'comparativo', 'cases', 'como-funciona', 'depoimentos', 'blog', 'videos', 'sobre', 'faq', 'contato' ) );
}

/**
 * Disponibilidade da home para navegação pública, incluindo usuários autenticados.
 *
 * @return array
 */
function public_sections() {
	$id = (int) get_option( 'page_on_front' );
	if ( ! $id || 'publish' !== get_post_status( $id ) || get_post_field( 'post_password', $id ) ) {
		return array();
	}
	$result = array();
	$order  = get_post_meta( $id, '_iotnest_home_order', true );
	foreach ( is_array( $order ) ? $order : section_keys() as $key ) {
		$data = get_post_meta( $id, '_iotnest_home_' . $key, true );
		if ( is_array( $data ) && ! hidden_reason( $key, $data ) ) {
			$result[ $key ] = $data;
		}
	}
	return $result;
}

/**
 * Resolve apenas destinos válidos e disponíveis.
 *
 * @param array $cta Contrato de CTA.
 * @return string
 */
function destination_url( $cta ) {
	$kind = $cta['kind'] ?? '';
	if ( 'section' === $kind ) {
		$key = $cta['section'] ?? '';
		return isset( section_anchors()[ $key ], public_sections()[ $key ] ) ? home_url( '/#' . section_anchors()[ $key ] ) : '';
	}
	if ( 'page' === $kind ) {
		$id = absint( $cta['page_id'] ?? 0 );
		return $id && 'page' === get_post_type( $id ) && 'publish' === get_post_status( $id ) && ! get_post_field( 'post_password', $id ) ? get_permalink( $id ) : '';
	}
	if ( 'cases' === $kind ) {
		return has_public_content( 'iot_case' ) ? get_post_type_archive_link( 'iot_case' ) : '';
	}
	if ( 'blog' === $kind ) {
		$id = (int) get_option( 'page_for_posts' );
		return has_public_content( 'post' ) && $id && 'publish' === get_post_status( $id ) ? get_permalink( $id ) : '';
	}
	return 'url' === $kind ? normalize( $cta['url'] ?? '', field( 'url', 'URL' ) ) : '';
}

/**
 * Escolhas do destino condicional no menu nativo.
 *
 * @return array
 */
function menu_targets() {
	$choices = array(
		''      => 'Destino nativo do WordPress',
		'group' => 'Agrupador sem destino',
		'cases' => 'Coleção de cases',
		'blog'  => 'Coleção de artigos',
	);
	foreach ( section_labels() as $key => $label ) {
		$choices[ 'section:' . $key ] = 'Seção: ' . $label;
	}
	return $choices;
}

/**
 * Campo adicional em Aparência > Menus.
 *
 * @param int      $item_id ID do item.
 * @param \WP_Post $item Objeto do item.
 */
function menu_item_field( $item_id, $item ) {
	unset( $item );
	$value = get_post_meta( $item_id, '_iotnest_menu_target', true );
	wp_nonce_field( 'iotnest_menu_' . $item_id, 'iotnest_menu_nonce[' . $item_id . ']' );
	echo '<p class="description description-wide"><label>Disponibilidade iotnest<br><select class="widefat" name="iotnest_menu_target[' . esc_attr( $item_id ) . ']">';
	foreach ( menu_targets() as $key => $label ) {
		echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></label><span class="description">Seções e coleções indisponíveis são omitidas. Agrupadores só aparecem com filhos disponíveis.</span></p>';
}

/**
 * Salva associação sem decidir pela legenda do menu.
 *
 * @param int $menu_id Menu.
 * @param int $item_id Item.
 */
function save_menu_target( $menu_id, $item_id ) {
	unset( $menu_id );
	if ( ! current_user_can( 'edit_theme_options' ) || ! isset( $_POST['iotnest_menu_nonce'][ $item_id ], $_POST['iotnest_menu_target'][ $item_id ] ) ) {
		return;
	}
	if ( ! is_string( $_POST['iotnest_menu_nonce'][ $item_id ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['iotnest_menu_nonce'][ $item_id ] ) ), 'iotnest_menu_' . $item_id ) ) {
		return;
	}
	$value = is_string( $_POST['iotnest_menu_target'][ $item_id ] ) ? sanitize_text_field( wp_unslash( $_POST['iotnest_menu_target'][ $item_id ] ) ) : '';
	if ( array_key_exists( $value, menu_targets() ) ) {
		update_post_meta( $item_id, '_iotnest_menu_target', $value );
	}
}

add_action( 'wp_nav_menu_item_custom_fields', __NAMESPACE__ . '\\menu_item_field', 10, 2 );
add_action( 'wp_update_nav_menu_item', __NAMESPACE__ . '\\save_menu_target', 10, 2 );
