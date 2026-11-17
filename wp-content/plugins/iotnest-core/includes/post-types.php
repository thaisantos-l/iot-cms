<?php
/**
 * Tipos de conteúdo permanecem disponíveis ao trocar de tema.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/** Registra cases, taxonomia e os metadados revisáveis. */
function register_content() {
	register_post_type(
		'iot_case',
		array(
			'labels'       => array(
				'name'          => 'Cases',
				'singular_name' => 'Case',
				'add_new_item'  => 'Adicionar case',
				'edit_item'     => 'Editar case',
			),
			'public'       => true,
			'show_in_rest' => true,
			'has_archive'  => 'cases',
			'rewrite'      => array(
				'slug'       => 'cases',
				'with_front' => false,
			),
			'menu_icon'    => 'dashicons-portfolio',
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			'map_meta_cap' => true,
		)
	);
	register_taxonomy(
		'iot_case_sector',
		'iot_case',
		array(
			'label'        => 'Segmentos',
			'public'       => true,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'segmento' ),
		)
	);
	foreach ( array( 'page', 'iot_case' ) as $post_type ) {
		foreach ( meta_schemas( $post_type ) as $key => $field ) {
			$schema            = json_schema( $field );
			$schema['context'] = array( 'edit' );
			register_post_meta(
				$post_type,
				$key,
				array(
					'type'              => $schema['type'],
					'single'            => true,
					'revisions_enabled' => true,
					'show_in_rest'      => array( 'schema' => $schema ),
					'auth_callback'     => static fn( $allowed, $meta_key, $object_id ) => current_user_can( 'edit_post', $object_id ),
					'sanitize_callback' => static fn( $value ) => normalize( $value, $field ),
				)
			);
		}
	}
}
