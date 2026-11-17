<?php
/**
 * Regras de disponibilidade compartilhadas pelo painel e pela apresentação.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Mídia local deve existir e ser do tipo solicitado.
 *
 * @param int    $id Attachment.
 * @param string $kind image ou video.
 * @return bool
 */
function valid_media( $id, $kind = 'image' ) {
	$id = absint( $id );
	if ( ! $id || 'attachment' !== get_post_type( $id ) ) {
		return false;
	}
	$type = get_post_mime_type( $id );
	return is_string( $type ) && str_starts_with( $type, $kind . '/' ) && is_readable( (string) get_attached_file( $id ) );
}

/**
 * Vídeos de fornecedores aprovados, sem iframe editorial.
 *
 * @param array $video Fonte.
 * @return bool
 */
function valid_video( $video ) {
	if ( 'youtube' === ( $video['provider'] ?? '' ) ) {
		return 1 === preg_match( '/\A[a-zA-Z0-9_-]{11}\z/', $video['youtube'] ?? '' );
	}
	return 'local' === ( $video['provider'] ?? '' ) && valid_media( $video['file'] ?? 0, 'video' );
}

/**
 * Mesma política pública em contagens, menus e cards.
 *
 * @param string $type Tipo WordPress.
 * @param int    $category Categoria opcional.
 * @return bool
 */
function has_public_content( $type, $category = 0 ) {
	$args = array(
		'post_type'           => $type,
		'post_status'         => 'publish',
		'has_password'        => false,
		'posts_per_page'      => 1,
		'fields'              => 'ids',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	);
	if ( 'post' === $type && $category ) {
		$args['cat'] = absint( $category );
	}
	return (bool) get_posts( $args );
}

/**
 * Itens completos para renderização; a edição preserva linhas incompletas.
 *
 * @param string $section Chave.
 * @param array  $data Dados.
 * @return array
 */
function valid_items( $section, $data ) {
	$items = $data['items'] ?? ( 'comparison' === $section ? ( $data['rows'] ?? array() ) : array() );
	return array_values(
		array_filter(
			$items,
			static function ( $item ) use ( $section ) {
				return match ( $section ) {
					'features' => has_text( $item['text'] ?? '' ),
					'ai' => has_text( $item['text'] ?? '' ) && valid_media( $item['image'] ?? 0 ),
					'mes' => has_text( $item['label'] ?? '' ) && has_text( $item['title'] ?? '' ) && has_text( $item['description'] ?? '' ),
					'comparison' => has_text( $item['label'] ?? '' ),
					'how_it_works' => has_text( $item['title'] ?? '' ) && has_text( $item['text'] ?? '' ),
					'testimonials' => has_text( $item['quote'] ?? '' ) && has_text( $item['name'] ?? '' ),
					'videos' => has_text( $item['title'] ?? '' ) && valid_video( $item['video'] ?? array() ),
					'faq' => has_text( $item['question'] ?? '' ) && has_text( $item['answer'] ?? '' ),
					default => true,
				};
			}
		)
	);
}

/**
 * Retorna motivo legível no painel; string vazia significa disponível.
 *
 * @param string $section Chave.
 * @param array  $data Conteúdo normalizado.
 * @return string
 */
function hidden_reason( $section, $data ) {
	if ( empty( $data['enabled'] ) ) {
		return 'Seção desabilitada.';
	}
	if ( ! has_text( $data[ 'features' === $section ? 'label' : 'title' ] ?? '' ) ) {
		return 'Campo principal vazio.';
	}
	if ( in_array( $section, array( 'features', 'ai', 'mes', 'comparison', 'how_it_works', 'testimonials', 'videos', 'faq' ), true ) && ! valid_items( $section, $data ) ) {
		return 'A lista não contém itens completos.';
	}
	if ( 'assistant' === $section && ! valid_video( $data['video'] ?? array() ) && ! valid_media( $data['poster'] ?? 0 ) ) {
		return 'Vídeo e poster ausentes ou inválidos.';
	}
	if ( 'cases' === $section && ! has_public_content( 'iot_case' ) ) {
		return 'Nenhum case público publicado sem senha.';
	}
	if ( 'blog' === $section && ! has_public_content( 'post', $data['category_id'] ?? 0 ) ) {
		return 'Nenhum artigo público publicado sem senha neste filtro.';
	}
	if ( 'calculator' === $section && ! ( settings()['calculator'] ?? true ) ) {
		return 'Calculadora desabilitada nas configurações.';
	}
	return '';
}
