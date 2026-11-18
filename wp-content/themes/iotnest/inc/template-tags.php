<?php
/**
 * Elementos compartilhados com omissão de valores vazios e escape no contexto.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Rótulo de etapa, independente do texto editorial.
 *
 * @param int $number Número da etapa.
 * @return string
 */
function step_label( $number ) {
	/* translators: %s: número da etapa com dois dígitos. */
	return sprintf( __( 'Etapa %s', 'iotnest' ), sprintf( '%02d', $number ) );
}

/**
 * Posição de item acessível.
 *
 * @param int $position Posição atual.
 * @param int $count Quantidade.
 * @return string
 */
function position_label( $position, $count ) {
	/* translators: 1: posição atual, 2: quantidade total de itens. */
	return sprintf( __( '%1$d de %2$d', 'iotnest' ), $position, $count );
}

/**
 * Rótulo de imagem no carrossel.
 *
 * @param int $number Número da imagem.
 * @return string
 */
function image_label( $number ) {
	/* translators: %d: número da imagem. */
	return sprintf( __( 'Imagem %d', 'iotnest' ), $number );
}

/**
 * Texto editorial sem recriar defaults apagados.
 *
 * @param string $tag Tag permitida.
 * @param mixed  $value Texto/HTML sanitizado.
 * @param string $classes Classe.
 */
function text_element( $tag, $value, $classes = '' ) {
	if ( ! has_text( $value ) || ! in_array( $tag, array( 'h1', 'h2', 'h3', 'p', 'span', 'strong', 'em', 'div' ), true ) ) {
		return;
	}
	if ( 'p' === $tag && preg_match( '/<(p|ul|ol|blockquote)\b/i', $value ) ) {
		$tag = 'div';
	}
	echo '<' . esc_attr( $tag ) . ( $classes ? ' class="' . esc_attr( $classes ) . '"' : '' ) . '>' . wp_kses_post( $value ) . '</' . esc_attr( $tag ) . '>';
}

/**
 * Título compartilhado em stack ou cabeçalho de seção.
 *
 * @param array  $data Campos.
 * @param string $classes Wrapper opcional.
 */
function section_heading( $data, $classes = '' ) {
	get_template_part(
		'template-parts/components/section-head',
		null,
		array(
			'data'    => $data,
			'classes' => $classes,
		)
	);
}

/**
 * Lista só gera wrapper quando existem itens completos.
 *
 * @param array  $items Itens válidos.
 * @param string $classes Classes.
 */
function bullet_list( $items, $classes = 'bullets' ) {
	if ( ! $items ) {
		return;
	}
	echo '<ul class="' . esc_attr( $classes ) . '">';
	foreach ( $items as $item ) {
		echo '<li>' . wp_kses_post( $item['text'] ) . '</li>';
	}
	echo '</ul>';
}

/**
 * URL de mídia local válida; nenhum src vazio é emitido.
 *
 * @param int    $id Attachment.
 * @param string $kind image ou video.
 * @return string
 */
function media_url( $id, $kind = 'image' ) {
	return function_exists( '\Iotnest\Core\valid_media' ) && \Iotnest\Core\valid_media( $id, $kind ) ? (string) wp_get_attachment_url( $id ) : '';
}

/**
 * Vídeo de fundo local com fallback de poster; controle é adicionado pela interação.
 *
 * @param array  $data Campos.
 * @param string $classes Classe do vídeo/imagem.
 * @param bool   $hero Prioridade da mídia.
 */
function background_video( $data, $classes, $hero = false ) {
	$video = $data['video'] ?? array();
	$file  = 'local' === ( $video['provider'] ?? '' ) ? media_url( $video['file'] ?? 0, 'video' ) : '';
	if ( ! $file ) {
		media_image( $data['poster'] ?? 0, $classes, 'full', $hero );
		return;
	}
	echo '<video class="' . esc_attr( $classes ) . '" playsinline preload="' . ( $hero ? 'auto' : 'metadata' ) . '" data-autoplay="' . ( $hero || ! empty( $data['autoplay'] ) ? 'true' : 'false' ) . '"' . ( $hero || ! empty( $data['muted'] ) ? ' muted' : '' ) . ( $hero || ! empty( $data['loop'] ) ? ' loop' : '' ) . ( $hero ? ' aria-hidden="true" tabindex="-1"' : ' controls' ) . ' disablepictureinpicture';
	$poster = media_url( $data['poster'] ?? 0 );
	if ( $poster ) {
		echo ' poster="' . esc_url( $poster ) . '"';
	}
	// Autoplay é progressivo e respeita flags, visibilidade e redução de movimento.
	echo '>';
	foreach ( array( $video['file'] ?? 0, $video['alternate'] ?? 0 ) as $id ) {
		$url = media_url( $id, 'video' );
		if ( $url ) {
			echo '<source src="' . esc_url( $url ) . '" type="' . esc_attr( get_post_mime_type( $id ) ) . '">';
		}
	}
	echo '</video>';
}
