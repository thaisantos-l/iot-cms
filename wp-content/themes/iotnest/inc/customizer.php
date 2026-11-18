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
			'description' => 'Os valores iniciais preservam a referência. Ao mudar cores, confira contraste e legibilidade.',
		)
	);
	$labels = array(
		'brand'                 => 'Marca principal',
		'green'                 => 'Verde da marca',
		'ink'                   => 'Texto principal',
		'surface'               => 'Superfície branca',
		'surface_soft'          => 'Superfície suave',
		'surface_gray'          => 'Superfície cinza',
		'slate'                 => 'Texto secundário',
		'danger'                => 'Alerta',
		'border'                => 'Bordas',
		'contrast'              => 'Texto de contraste',
		'accent_orange'         => 'Destaque laranja',
		'indicator_green'       => 'Indicador verde',
		'accent_green'          => 'Destaque verde',
		'media_surface'         => 'Fundo de mídia',
		'card_gradient_start'   => 'Card: gradiente inicial',
		'card_gradient_end'     => 'Card: gradiente final',
		'media_black'           => 'Fundo do player',
		'whatsapp'              => 'WhatsApp',
		'credit'                => 'Crédito do rodapé',
		'calculator_bg'         => 'Calculadora: fundo',
		'calculator_surface'    => 'Calculadora: painéis',
		'calculator_text'       => 'Calculadora: texto',
		'calculator_muted'      => 'Calculadora: texto secundário',
		'calculator_faint'      => 'Calculadora: legenda',
		'calculator_error'      => 'Calculadora: erro',
		'calculator_success'    => 'Calculadora: resultado',
		'field_surface'         => 'Campos: fundo',
		'calculator_error_text' => 'Calculadora: texto do erro',
		'calculator_hover'      => 'Calculadora: hover',
		'step_surface'          => 'Etapas: mídia',
		'card_two_start'        => 'Segundo card: início',
		'card_two_end'          => 'Segundo card: fim',
		'card_three_start'      => 'Terceiro card: início',
		'card_three_end'        => 'Terceiro card: fim',
		'hero_surface'          => 'Hero: fundo',
		'shadow'                => 'Sombras',
		'media_shadow'          => 'Sombra da mídia',
		'modal_backdrop'        => 'Modal: fundo externo',
		'calculator_glow'       => 'Calculadora: brilho',
		'calculator_shadow'     => 'Calculadora: sombra',
	);
	foreach ( palette() as $key => $color ) {
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
					'label'   => $labels[ $key ] ?? $key,
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
