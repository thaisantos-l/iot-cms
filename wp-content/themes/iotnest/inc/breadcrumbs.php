<?php
/**
 * Uma única árvore compartilhada com Yoast, sem modificar o desenho da referência.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Normaliza os links do Yoast antes da geração do schema e da apresentação.
 *
 * @param array $links Links calculados pelo Yoast.
 * @return array
 */
function yoast_breadcrumb_links( $links ) {
	if ( is_front_page() ) {
		return $links;
	}
	$label  = '';
	$parent = '';
	$id     = get_queried_object_id();
	if ( is_singular() ) {
		$post  = get_queried_object();
		$label = get_the_title( $id );
		if ( in_array( $post->post_type, array( 'post', 'iot_case' ), true ) ) {
			$parent = $post->post_type;
		}
		if ( 'iot_case' === $post->post_type && ! $post->post_password ) {
			$fields = get_post_meta( $id, '_iotnest_case', true );
			if ( has_text( $fields['company'] ?? '' ) ) {
				$label = $fields['company'];
			}
		}
		$custom = get_post_meta( $id, '_yoast_wpseo_bctitle', true );
		if ( has_text( $custom ) ) {
			$label = $custom;
		}
	} elseif ( is_home() ) {
		$label = __( 'Blog', 'iotnest' );
	} elseif ( is_post_type_archive( 'iot_case' ) ) {
		$label = __( 'Cases', 'iotnest' );
	} elseif ( is_search() ) {
		$label = __( 'Busca', 'iotnest' );
	} elseif ( is_404() ) {
		$label = __( 'Página não encontrada', 'iotnest' );
	} else {
		$label = wp_strip_all_tags( get_the_archive_title() );
	}
	$result = array(
		array(
			'url'  => home_url( '/' ),
			'text' => __( 'Início', 'iotnest' ),
		),
	);
	if ( $parent && collection_url( $parent ) ) {
		$result[] = array(
			'url'  => collection_url( $parent ),
			'text' => 'iot_case' === $parent ? __( 'Cases', 'iotnest' ) : __( 'Blog', 'iotnest' ),
		);
	}
	$last     = end( $links );
	$result[] = array(
		'text' => $label,
		'url'  => is_singular() ? get_permalink( $id ) : ( $last['url'] ?? '' ),
	);
	return $result;
}
add_filter( 'wpseo_breadcrumb_links', __NAMESPACE__ . '\\yoast_breadcrumb_links' );

/**
 * O presenter do Yoast fornece os mesmos dados usados por seu schema.
 *
 * @param string $output Marcação original.
 * @param object $presentation Presenter Yoast.
 * @return string
 */
function yoast_breadcrumb_markup( $output, $presentation ) {
	$links = $presentation->breadcrumbs ?? array();
	if ( ! $links ) {
		return $output;
	}
	$parts = array();
	foreach ( $links as $index => $link ) {
		$text    = esc_html( wp_strip_all_tags( $link['text'] ?? '' ) );
		$parts[] = ( count( $links ) - 1 ) === $index ? '<span aria-current="page">' . $text . '</span>' : '<a href="' . esc_url( $link['url'] ?? '' ) . '">' . $text . '</a>';
	}
	return '<nav class="legal__crumb" aria-label="Você está em">' . implode( '<span aria-hidden="true">/</span>', $parts ) . '</nav>';
}
add_filter( 'wpseo_breadcrumb_output', __NAMESPACE__ . '\\yoast_breadcrumb_markup', 10, 2 );
