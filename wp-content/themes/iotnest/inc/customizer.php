<?php
/**
 * Controles nativos de identidade; valores iniciais vêm da referência.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Registra logos e cores sem permitir CSS arbitrário.
 *
 * @param \WP_Customize_Manager $manager Customizer nativo.
 */
function customize_register( $manager ) {
	$manager->add_section(
		'iotnest_identity',
		array(
			'title'       => 'Identidade iotnest',
			'description' => 'Os valores iniciais preservam a referência. Ao mudar cores, confira contraste e legibilidade. A aplicação completa da identidade faz parte da etapa 03.',
		)
	);
	foreach ( array(
		'brand'        => '#02afef',
		'green'        => '#06a26c',
		'ink'          => '#151e26',
		'surface'      => '#ffffff',
		'surface_soft' => '#f6f6f6',
		'surface_gray' => '#f2f2f2',
		'slate'        => '#606062',
		'danger'       => '#ef4444',
	) as $key => $color ) {
		$id = 'iotnest_color_' . $key;
		$manager->add_setting(
			$id,
			array(
				'default'           => $color,
				'sanitize_callback' => 'sanitize_hex_color',
				'capability'        => 'edit_theme_options',
				'transport'         => 'refresh',
			)
		);
		$manager->add_control(
			new \WP_Customize_Color_Control(
				$manager,
				$id,
				array(
					'label'   => $key,
					'section' => 'iotnest_identity',
				)
			)
		);
	}
	$manager->add_setting(
		'iotnest_footer_logo',
		array(
			'sanitize_callback' => 'absint',
			'capability'        => 'edit_theme_options',
		)
	);
	$manager->add_control(
		new \WP_Customize_Media_Control(
			$manager,
			'iotnest_footer_logo',
			array(
				'label'     => 'Logo do rodapé',
				'section'   => 'iotnest_identity',
				'mime_type' => 'image',
			)
		)
	);
}

add_action( 'customize_register', __NAMESPACE__ . '\\customize_register' );
