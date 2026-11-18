<?php
/**
 * View models e helpers compartilhados das páginas internas.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Dados de card/individual sem executar uma consulta de coleção por item.
 *
 * @param \WP_Post $entry Publicação.
 * @return array
 */
function entry_data( $entry ) {
	$is_case = 'iot_case' === $entry->post_type;
	$fields  = $is_case ? (array) get_post_meta( $entry->ID, '_iotnest_case', true ) : array();
	$terms   = get_the_terms( $entry, $is_case ? 'iot_case_sector' : 'category' );
	$terms   = is_array( $terms ) ? $terms : array();
	$text    = wp_strip_all_tags( strip_shortcodes( $entry->post_content ) );
	preg_match_all( '/[\p{L}\p{N}]+(?:[’\x27-][\p{L}\p{N}]+)*/u', $text, $words );
	$minutes = max( 1, (int) ceil( count( $words[0] ) / 220 ) );
	return array(
		'post'    => $entry,
		'case'    => $is_case,
		'title'   => get_the_title( $entry ),
		'url'     => get_permalink( $entry ),
		'excerpt' => wp_strip_all_tags( get_the_excerpt( $entry ) ),
		'image'   => get_post_thumbnail_id( $entry ),
		'term'    => $terms ? $terms[0]->name : '',
		'company' => $fields['company'] ?? '',
		'fields'  => $fields,
		// translators: %d is the calculated number of reading minutes.
		'reading' => sprintf( __( '%d min de leitura', 'iotnest' ), $minutes ),
	);
}

/**
 * URL de arquivo acessível inclusive quando a coleção está vazia.
 *
 * @param string $type post/iot_case.
 * @return string
 */
function collection_url( $type ) {
	if ( 'iot_case' === $type ) {
		return (string) get_post_type_archive_link( 'iot_case' );
	}
	$id = (int) get_option( 'page_for_posts' );
	return $id && 'publish' === get_post_status( $id ) && ! get_post_field( 'post_password', $id ) ? get_permalink( $id ) : '';
}

/**
 * Uma fonte de breadcrumbs, com Yoast quando disponível.
 *
 * @param string $label Página atual.
 * @param string $parent_type Coleção opcional.
 */
function breadcrumbs( $label, $parent_type = '' ) {
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		$crumb = yoast_breadcrumb( '<nav class="legal__crumb" aria-label="Você está em">', '</nav>', false );
		if ( $crumb ) {
			echo wp_kses_post( $crumb );
			return;
		}
	}
	echo '<nav class="legal__crumb" aria-label="Você está em"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Início', 'iotnest' ) . '</a><span aria-hidden="true">/</span>';
	if ( $parent_type && collection_url( $parent_type ) ) {
		echo '<a href="' . esc_url( collection_url( $parent_type ) ) . '">' . ( 'iot_case' === $parent_type ? esc_html__( 'Cases', 'iotnest' ) : esc_html__( 'Blog', 'iotnest' ) ) . '</a><span aria-hidden="true">/</span>';
	}
	echo '<span aria-current="page">' . esc_html( $label ) . '</span></nav>';
}

/**
 * Apenas relacionados públicos, excluindo o atual; contexto do loop permanece intacto.
 *
 * @param \WP_Post $entry Item atual.
 */
function related( $entry ) {
	if ( ! function_exists( '\Iotnest\Core\public_query' ) || ! in_array( $entry->post_type, array( 'post', 'iot_case' ), true ) ) {
		return;
	}
	$is_case = 'iot_case' === $entry->post_type;
	$posts   = \Iotnest\Core\public_query(
		$entry->post_type,
		array(
			'posts_per_page' => $is_case ? 2 : 3,
			'post__not_in'   => array( $entry->ID ),
		)
	)->posts;
	if ( $posts ) {
		get_template_part(
			'template-parts/content/related',
			null,
			array(
				'posts' => $posts,
				'case'  => $is_case,
			)
		);
	}
}

/**
 * Conteúdo legal com IDs únicos, links reais e sumário de H2, preservando a marcação.
 *
 * @param string $content HTML editorial filtrado.
 * @return array
 */
function legal_document( $content ) {
	$processor = new \WP_HTML_Tag_Processor( wp_kses_post( $content ) );
	$used      = array(
		'main'     => true,
		'conteudo' => true,
	);
	while ( $processor->next_tag() ) {
		$id = $processor->get_attribute( 'id' );
		if ( is_string( $id ) ) {
			$base = sanitize_title( $id );
			$base = $base ? $base : 'documento';
			$id   = $base;
			$i    = 2;
			while ( isset( $used[ $id ] ) ) {
				$id = $base . '-' . ( $i++ );
			}
			$used[ $id ] = true;
			$processor->set_attribute( 'id', $id );
		}
		$href = $processor->get_attribute( 'href' );
		if ( 'A' === $processor->get_tag() && is_string( $href ) && function_exists( '\Iotnest\Core\legacy_destination' ) ) {
			$parts = wp_parse_url( $href );
			if ( ! empty( $parts['path'] ) && empty( $parts['host'] ) && empty( $parts['scheme'] ) && str_ends_with( $parts['path'], '.html' ) ) {
				$url = \Iotnest\Core\legacy_destination( $parts['path'] );
				if ( $url ) {
					$processor->set_attribute( 'href', $url . ( isset( $parts['fragment'] ) ? '#' . sanitize_title( $parts['fragment'] ) : '' ) );
				} else {
					$processor->remove_attribute( 'href' );
				}
			}
		}
	}
	$processor = new \WP_HTML_Tag_Processor( $processor->get_updated_html() );
	$toc       = array();
	$heading   = null;
	$number    = false;
	while ( $processor->next_token() ) {
		if ( 'H2' === $processor->get_tag() ) {
			if ( $processor->is_tag_closer() ) {
				if ( $heading && has_text( $heading['label'] ) ) {
					$toc[] = $heading;
				}
				$heading = null;
			} else {
				$id = $processor->get_attribute( 'id' );
				if ( ! $id ) {
					$i = count( $toc ) + 1;
					do {
						$id = 'documento-' . ( $i++ );
					} while ( isset( $used[ $id ] ) );
					$used[ $id ] = true;
					$processor->set_attribute( 'id', $id );
				}
				$heading = array(
					'id'    => $id,
					'label' => '',
				);
			}
		} elseif ( $heading && 'SPAN' === $processor->get_tag() ) {
			$number = ! $processor->is_tag_closer() && $processor->has_class( 'legal__n' );
		} elseif ( $heading && ! $number && '#text' === $processor->get_token_type() ) {
			$heading['label'] .= $processor->get_modifiable_text();
		}
	}
	return array(
		'html' => $processor->get_updated_html(),
		'toc'  => $toc,
	);
}
