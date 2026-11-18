<?php
/**
 * Primitivas de apresentação com escape e identidade no HTML inicial.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Paleta inicial idêntica aos tokens da referência.
 *
 * @return array
 */
function palette() {
	return array(
		'brand'                 => '#02afef',
		'green'                 => '#06a26c',
		'ink'                   => '#151e26',
		'surface'               => '#ffffff',
		'surface_soft'          => '#f6f6f6',
		'surface_gray'          => '#f2f2f2',
		'slate'                 => '#606062',
		'danger'                => '#ef4444',
		'border'                => '#151e26',
		'contrast'              => '#ffffff',
		'accent_orange'         => '#f59e0b',
		'indicator_green'       => '#1fd08f',
		'accent_green'          => '#22c55e',
		'media_surface'         => '#f3f3f3',
		'card_gradient_start'   => '#5b646c',
		'card_gradient_end'     => '#2a3138',
		'media_black'           => '#000000',
		'whatsapp'              => '#25d366',
		'credit'                => '#ef6902',
		'calculator_bg'         => '#0f172a',
		'calculator_surface'    => '#1e293b',
		'calculator_text'       => '#f8fafc',
		'calculator_muted'      => '#cbd5e1',
		'calculator_faint'      => '#94a3b8',
		'calculator_error'      => '#f87171',
		'calculator_success'    => '#34d399',
		'field_surface'         => '#fafafa',
		'calculator_error_text' => '#fecaca',
		'calculator_hover'      => '#38c4f5',
		'step_surface'          => '#c4c4c4',
		'card_two_start'        => '#6c7a82',
		'card_two_end'          => '#364048',
		'card_three_start'      => '#7d8b72',
		'card_three_end'        => '#3f4a38',
		'hero_surface'          => '#cecece',
		'shadow'                => '#000000',
		'media_shadow'          => '#0b1218',
		'modal_backdrop'        => '#080c10',
		'calculator_glow'       => '#6366f1',
		'calculator_shadow'     => '#020617',
	);
}

/** Define tokens antes de pintar a página, sem build após edição. */
function identity_styles() {
	$css = ':root{';
	foreach ( palette() as $key => $default ) {
		$color = sanitize_hex_color( get_theme_mod( 'iotnest_color_' . $key, $default ) );
		$color = $color ? $color : $default;
		$name  = str_replace( '_', '-', $key );
		$css  .= '--' . $name . ':' . $color . ';';
		$hex   = substr( $color, 1 );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$rgb  = array_map( 'hexdec', str_split( $hex, 2 ) );
		$css .= '--' . $name . '-rgb:' . implode( ' ', $rgb ) . ';';
	}
	$css .= '--ink-60:rgb(var(--ink-rgb) / .62);--ink-75:rgb(var(--ink-rgb) / .78);--line:rgb(var(--border-rgb) / .08);}';
	wp_add_inline_style( 'iotnest-app', $css );
}

/**
 * Opções do plugin, com fallback seguro quando desativado.
 *
 * @return array
 */
function global_settings() {
	return function_exists( '\Iotnest\Core\settings' ) ? \Iotnest\Core\settings() : array();
}

/**
 * Texto preenchido, preservando zero e descartando marcação vazia.
 *
 * @param mixed $value Valor editorial.
 * @return bool
 */
function has_text( $value ) {
	return function_exists( '\Iotnest\Core\has_text' ) ? \Iotnest\Core\has_text( $value ) : ( is_scalar( $value ) && '' !== trim( wp_strip_all_tags( (string) $value ) ) );
}

/**
 * Ícone auditado, nunca um path ou SVG fornecido pelo visitante.
 *
 * @param string $name Nome local.
 * @param int    $size Largura/altura.
 */
function icon( $name, $size = 18 ) {
	if ( ! preg_match( '/\A[a-z0-9-]+\z/', $name ) || ! is_readable( get_template_directory() . '/assets/icons/' . $name . '.svg' ) ) {
		return;
	}
	echo '<img src="' . esc_url( get_template_directory_uri() . '/assets/icons/' . $name . '.svg' ) . '" alt="" width="' . absint( $size ) . '" height="' . absint( $size ) . '">';
}

/**
 * Logo alternativa contida na mesma área; default conserva os cinco SVGs.
 *
 * @param bool $footer Usar composição do rodapé.
 */
function logo( $footer = false ) {
	$id = absint( get_theme_mod( $footer ? 'iotnest_footer_logo' : 'custom_logo' ) );
	echo '<a class="logo" href="' . esc_url( home_url( '/' ) ) . '" aria-label="iotnest.ai — início">';
	if ( $id && wp_attachment_is_image( $id ) && is_readable( (string) get_attached_file( $id ) ) ) {
		echo wp_get_attachment_image(
			$id,
			'full',
			false,
			array(
				'class'   => 'custom-logo',
				'loading' => 'eager',
				'sizes'   => '188px',
			)
		);
	} else {
		$insets = array( '0 0 0 74.28%', '11.87% 3.08% 11.87% 77.36%', '24.85% 6.39% 24.84% 80.67%', '17.67% 50.25% 25.02% 0', '17.67% 30.97% 25.02% 51.04%' );
		foreach ( $insets as $index => $inset ) {
			echo '<img src="' . esc_url( get_template_directory_uri() . '/assets/logo/' . ( $footer ? 'f' : 'm' ) . ( $index + 1 ) . '.svg' ) . '" alt="" style="inset:' . esc_attr( $inset ) . '">';
		}
	}
	echo '</a>';
}

/**
 * CTA só existe com texto e destino resolvido.
 *
 * @param array  $cta Campos do plugin.
 * @param string $classes Classes do componente.
 * @param string $icon Nome do ícone opcional.
 */
function cta( $cta, $classes = 'btn', $icon = '' ) {
	if ( ! function_exists( '\Iotnest\Core\destination_url' ) || ! \Iotnest\Core\has_text( $cta['label'] ?? '' ) ) {
		return;
	}
	$url = \Iotnest\Core\destination_url( $cta );
	if ( ! $url ) {
		return;
	}
	echo '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( $url ) . '"' . ( ! empty( $cta['new_window'] ) ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $cta['label'] );
	if ( $icon ) {
		icon( $icon, 'arrow-sm' === $icon ? 16 : ( 'arrow-case' === $icon ? 14 : 18 ) );
	}
	echo '</a>';
}

/**
 * Imagem da biblioteca com dimensões e fontes responsivas do WordPress.
 *
 * @param int    $id Attachment.
 * @param string $classes Classes.
 * @param string $size Tamanho registrado.
 * @param bool   $eager Carregamento prioritário.
 * @param array  $attributes Atributos adicionais definidos pelo componente.
 */
function media_image( $id, $classes = '', $size = 'large', $eager = false, $attributes = array() ) {
	if ( ! $id || ! wp_attachment_is_image( $id ) || ! is_readable( (string) get_attached_file( $id ) ) ) {
		return;
	}
	echo wp_get_attachment_image(
		$id,
		$size,
		false,
		array_merge(
			array(
				'class'    => $classes,
				'loading'  => $eager ? 'eager' : 'lazy',
				'decoding' => 'async',
			),
			$attributes
		)
	);
}

/** Inicializa apenas a indicação de JS no head, evitando flash do menu mobile. */
function navigation_bootstrap() {
	if ( asset_file( 'app.js' ) ) {
		wp_print_inline_script_tag( 'document.documentElement.classList.add("js-nav");' );
	}
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\identity_styles', 20 );
add_action( 'wp_head', __NAMESPACE__ . '\\navigation_bootstrap', 1 );

/**
 * Prioriza a fonte latina local usada na primeira dobra.
 *
 * @param array $resources Recursos já registrados.
 * @return array
 */
function preload_font( $resources ) {
	if ( asset_file( 'app.css' ) && is_readable( get_template_directory() . '/assets/fonts/inter-latin.woff2' ) ) {
		$resources[] = array(
			'href'        => get_template_directory_uri() . '/assets/fonts/inter-latin.woff2',
			'as'          => 'font',
			'type'        => 'font/woff2',
			'crossorigin' => 'anonymous',
		);
	}
	return $resources;
}

add_filter( 'wp_preload_resources', __NAMESPACE__ . '\\preload_font' );
