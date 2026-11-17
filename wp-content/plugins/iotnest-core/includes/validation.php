<?php
/**
 * Normalização e validação compartilhadas entre campos, REST e importação explícita.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Texto semanticamente preenchido; zero não é vazio.
 *
 * @param mixed $value Valor editorial.
 * @return bool
 */
function has_text( $value ) {
	if ( ! is_scalar( $value ) ) {
		return false;
	}
	$text = html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = preg_replace( '/[\s\p{Z}\p{Cf}\x{FE0F}]+/u', '', $text );
	return is_string( $text ) && '' !== $text;
}

/**
 * Contrato JSON Schema usado também pela API nativa.
 *
 * @param array $field Descritor.
 * @return array
 */
function json_schema( $field ) {
	$kind = $field['kind'];
	if ( 'object' === $kind ) {
		$properties = array();
		foreach ( $field['fields'] as $key => $child ) {
			$properties[ $key ] = json_schema( $child );
		}
		$result = array(
			'type'                 => 'object',
			'properties'           => $properties,
			'additionalProperties' => false,
		);
		if ( isset( $properties['id'] ) ) {
			$result['required'] = array( 'id' );
		}
		return $result;
	}
	if ( 'array' === $kind ) {
		return array(
			'type'     => 'array',
			'items'    => json_schema( $field['item'] ),
			'maxItems' => $field['limit'],
		);
	}
	if ( 'boolean' === $kind ) {
		return array( 'type' => 'boolean' );
	}
	if ( in_array( $kind, array( 'number', 'integer', 'image', 'video', 'page' ), true ) ) {
		return array(
			'type'    => array( 'number' === $kind ? 'number' : 'integer', 'null' ),
			'minimum' => $field['min'] ?? 0,
			'maximum' => $field['max'] ?? PHP_INT_MAX,
		);
	}
	$result = array(
		'type'      => 'string',
		'maxLength' => in_array( $kind, array( 'rich', 'heading' ), true ) ? 20000 : 4000,
	);
	if ( 'select' === $kind ) {
		$result['enum'] = $field['choices'];
	}
	if ( in_array( $kind, array( 'id', 'idref' ), true ) ) {
		$result['pattern'] = 'id' === $kind ? '^[a-zA-Z0-9_-]{1,80}$' : '^[a-zA-Z0-9_-]{0,80}$';
	}
	if ( 'youtube' === $kind ) {
		$result['pattern'] = '^([a-zA-Z0-9_-]{11})?$';
	}
	return $result;
}

/**
 * Normaliza dados já validados por tipo, sem introduzir texto padrão.
 *
 * @param mixed $value Valor recebido.
 * @param array $field Contrato.
 * @return mixed
 */
function normalize( $value, $field ) {
	$kind = $field['kind'];
	if ( null === $value ) {
		return null;
	}
	if ( 'object' === $kind ) {
		$result = array();
		foreach ( (array) $value as $key => $item ) {
			if ( isset( $field['fields'][ $key ] ) ) {
				$result[ $key ] = normalize( $item, $field['fields'][ $key ] );
			}
		}
		return $result;
	}
	if ( 'array' === $kind ) {
		return array_map( static fn( $item ) => normalize( $item, $field['item'] ), is_array( $value ) ? $value : array() );
	}
	if ( 'boolean' === $kind ) {
		return (bool) $value;
	}
	if ( 'number' === $kind ) {
		return (float) $value;
	}
	if ( in_array( $kind, array( 'integer', 'image', 'video', 'page' ), true ) ) {
		return absint( $value );
	}
	if ( 'heading' === $kind ) {
		$value = wp_kses(
			(string) $value,
			array(
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'span'   => array( 'class' => true ),
			)
		);
		$html  = new \WP_HTML_Tag_Processor( $value );
		while ( $html->next_tag( 'span' ) ) {
			if ( 'accent' !== $html->get_attribute( 'class' ) ) {
				$html->remove_attribute( 'class' );
			}
		}
		return $html->get_updated_html();
	}
	if ( 'rich' === $kind ) {
		return wp_kses(
			(string) $value,
			array(
				'p'          => array(),
				'br'         => array(),
				'strong'     => array(),
				'em'         => array(),
				'ul'         => array(),
				'ol'         => array(),
				'li'         => array(),
				'a'          => array(
					'href'  => true,
					'title' => true,
				),
				'code'       => array(),
				'blockquote' => array(),
			)
		);
	}
	if ( 'url' === $kind ) {
		$url = esc_url_raw( (string) $value, array( 'https', 'http', 'mailto', 'tel' ) );
		return '#' === $url || ! preg_match( '/^(https?:\/\/|mailto:|tel:)/i', $url ) ? '' : $url;
	}
	if ( 'email' === $kind ) {
		return sanitize_email( (string) $value );
	}
	if ( 'icon' === $kind ) {
		$allowed = array( '', 'temperature', 'energy', 'chart', 'chart-black', 'bell-black', 'sliders-black', 'hw', 'mail', 'phone', 'whatsapp', 'pin', 'arrow-sm', 'arrow-case', 'arrow-send', 'arrow-calc', 'tiktok', 'linkedin', 'facebook', 'instagram' );
		return in_array( $value, $allowed, true ) ? $value : '';
	}
	return sanitize_text_field( (string) $value );
}

/**
 * Verifica IDs repetidos recursivamente; arrays representam substituição explícita.
 *
 * @param mixed $value Dados.
 * @param array $field Contrato.
 * @return bool
 */
function unique_ids( $value, $field ) {
	if ( 'array' === $field['kind'] ) {
		$ids = array();
		foreach ( $value as $item ) {
			$id = is_array( $item ) ? ( $item['id'] ?? '' ) : $item;
			if ( in_array( $id, $ids, true ) || ! unique_ids( $item, $field['item'] ) ) {
				return false;
			}
			$ids[] = $id;
		}
	} elseif ( 'object' === $field['kind'] ) {
		foreach ( (array) $value as $key => $item ) {
			if ( isset( $field['fields'][ $key ] ) && ! unique_ids( $item, $field['fields'][ $key ] ) ) {
				return false;
			}
		}
	}
	return true;
}

/**
 * Merge de submissão parcial: não remover propriedades não enviadas.
 *
 * @param mixed $old Estado anterior.
 * @param mixed $patch Submissão.
 * @param array $field Contrato.
 * @return mixed
 */
function merge_patch( $old, $patch, $field ) {
	if ( 'object' !== $field['kind'] || ! is_array( $patch ) ) {
		return $patch;
	}
	$result = is_array( $old ) ? $old : array();
	foreach ( $patch as $key => $value ) {
		$result[ $key ] = isset( $field['fields'][ $key ] ) ? merge_patch( $result[ $key ] ?? null, $value, $field['fields'][ $key ] ) : $value;
	}
	return $result;
}
