<?php
/**
 * Prepara a home antes de emitir seções ou seus wrappers.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Lista textual preenchida, preservando o número zero.
 *
 * @param array  $items Lista editorial.
 * @param string $key Campo significativo.
 * @return array
 */
function text_items( $items, $key = 'text' ) {
	return array_values( array_filter( (array) $items, static fn( $item ) => is_array( $item ) && has_text( $item[ $key ] ?? '' ) ) );
}

/**
 * Registro de apresentação; preview usa a API nativa de metadata já filtrada.
 *
 * @param int $id Página.
 * @return array
 */
function home_sections( $id ) {
	if ( ! $id || ! function_exists( '\Iotnest\Core\hidden_reason' ) || post_password_required( $id ) ) {
		return array();
	}
	$order = get_post_meta( $id, '_iotnest_home_order', true );
	$out   = array();
	foreach ( is_array( $order ) ? array_unique( $order ) : \Iotnest\Core\section_keys() as $key ) {
		if ( ! in_array( $key, \Iotnest\Core\section_keys(), true ) ) {
			continue;
		}
		$data = get_post_meta( $id, '_iotnest_home_' . $key, true );
		if ( ! is_array( $data ) || \Iotnest\Core\hidden_reason( $key, $data ) ) {
			continue;
		}
		foreach ( array( 'bullets', 'paragraphs', 'labels' ) as $list ) {
			$data[ $list ] = text_items( $data[ $list ] ?? array() );
		}
		$data['items']  = \Iotnest\Core\valid_items( $key, $data );
		$data['images'] = array_values( array_filter( $data['images'] ?? array(), static fn( $item ) => \Iotnest\Core\valid_media( $item['image'] ?? 0 ) ) );
		$data['groups'] = array_values(
			array_filter(
				array_map(
					static function ( $group ) {
						$group['items'] = text_items( $group['items'] ?? array() );
						return $group;
					},
					$data['groups'] ?? array()
				),
				static fn( $group ) => $group['items'] && ( 'hardware' !== $key || has_text( $group['title'] ?? '' ) )
			)
		);
		foreach ( array( 'image', 'poster' ) as $image ) {
			$data[ $image ] = \Iotnest\Core\valid_media( $data[ $image ] ?? 0 ) ? $data[ $image ] : 0;
		}
		$data['columns']  = text_items( $data['columns'] ?? array(), 'label' );
		$data['channels'] = text_items( $data['channels'] ?? array(), 'value' );
		if ( 'calculator' === $key ) {
			$labels             = array_column( $data['labels'], 'text', 'key' );
			$data['form_ready'] = true;
			foreach ( array( 'rate', 'hours', 'days', 'atual', 'iot', 'submit', 'result', 'current', 'loss', 'target', 'loss_target' ) as $required_label ) {
				if ( ! has_text( $labels[ $required_label ] ?? '' ) ) {
					$data['form_ready'] = false;
				}
			}
		}
		if ( in_array( $key, array( 'cases', 'blog' ), true ) ) {
			$query = array(
				'post_type'           => 'cases' === $key ? 'iot_case' : 'post',
				'post_status'         => 'publish',
				'has_password'        => false,
				'posts_per_page'      => min( 12, max( 1, absint( $data['limit'] ?? 4 ) ) ),
				'orderby'             => array(
					'date' => 'DESC',
					'ID'   => 'DESC',
				),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			);
			if ( 'blog' === $key && ! empty( $data['category_id'] ) ) {
				$query['cat'] = absint( $data['category_id'] );
			}
			$data['posts'] = get_posts( $query );
			if ( ! $data['posts'] ) {
				continue;
			}
		}
		$data['key']    = $key;
		$data['anchor'] = \Iotnest\Core\section_anchors()[ $key ];
		$out[ $key ]    = $data;
	}
	return $out;
}
