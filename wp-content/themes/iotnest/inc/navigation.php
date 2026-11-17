<?php
/**
 * Menus nativos com destinos condicionais e markup da referência.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Filtra pelo contrato do destino; não compara texto do item.
 *
 * @param array  $items Itens WordPress.
 * @param object $args Argumentos da localização.
 * @return array
 */
function menu_items( $items, $args ) {
	if ( ! in_array( $args->theme_location ?? '', array( 'primary', 'footer', 'legal' ), true ) ) {
		return $items;
	}
	if ( ! function_exists( '\Iotnest\Core\destination_url' ) ) {
		return array();
	}
	$visible = array();
	foreach ( $items as $original ) {
		$item   = clone $original;
		$target = get_post_meta( $item->ID, '_iotnest_menu_target', true );
		if ( str_starts_with( $target, 'section:' ) ) {
			$item->url = \Iotnest\Core\destination_url(
				array(
					'kind'    => 'section',
					'section' => substr( $target, 8 ),
				)
			);
		} elseif ( in_array( $target, array( 'blog', 'cases' ), true ) ) {
			$item->url = \Iotnest\Core\destination_url( array( 'kind' => $target ) );
		} elseif ( 'group' === $target ) {
			$item->url = '';
		} elseif ( 'post_type' === $item->type ) {
			if ( 'publish' !== get_post_status( $item->object_id ) || get_post_field( 'post_password', $item->object_id ) ) {
				continue;
			}
		} else {
			$item->url = \Iotnest\Core\destination_url(
				array(
					'kind' => 'url',
					'url'  => $item->url,
				)
			);
		}
		if ( $item->url || 'group' === $target ) {
			$visible[ $item->ID ] = $item;
		}
	}
	// Podar agrupadores vazios e filhos de pais indisponíveis até estabilizar.
	do {
		$changed = false;
		foreach ( $visible as $id => $item ) {
			$children = array_filter( $visible, static fn( $child ) => (int) $child->menu_item_parent === (int) $id );
			if ( ( ! $item->url && ! $children ) || ( $item->menu_item_parent && ! isset( $visible[ $item->menu_item_parent ] ) ) ) {
				unset( $visible[ $id ] );
				$changed = true;
			}
		}
	} while ( $changed );
	foreach ( $visible as $item ) {
		$item->classes = array_diff( $item->classes, array( 'menu-item-has-children' ) );
		if ( array_filter( $visible, static fn( $child ) => (int) $child->menu_item_parent === (int) $item->ID ) ) {
			$item->classes[] = 'menu-item-has-children';
		}
	}
	return array_values( $visible );
}

add_filter( 'wp_nav_menu_objects', __NAMESPACE__ . '\\menu_items', 10, 2 );
