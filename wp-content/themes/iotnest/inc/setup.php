<?php
/**
 * Suportes nativos e dependências de apresentação.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;

/** Registra somente recursos de apresentação. */
function setup() {
	load_theme_textdomain( 'iotnest', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'custom-logo',
		array(
			'width'       => 376,
			'height'      => 94,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
	register_nav_menus(
		array(
			'primary' => 'Principal',
			'footer'  => 'Mapa do site',
			'legal'   => 'Políticas',
		)
	);
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'editor-styles' );
	$editor = asset_file( 'editor.css' );
	if ( $editor ) {
		add_editor_style( 'assets/dist/' . $editor );
	}
}

/** Informa dependências somente a quem pode administrar o tema. */
function dependency_notice() {
	if ( ! current_user_can( 'switch_themes' ) ) {
		return;
	}
	if ( ! defined( 'IOTNEST_CORE_VERSION' ) ) {
		echo '<div class="notice notice-warning"><p>';
		esc_html_e( 'Ative o plugin iotnest-core para administrar o conteúdo do iotnest. O tema mantém a apresentação mínima enquanto o plugin estiver indisponível.', 'iotnest' );
		echo '</p></div>';
	}
	if ( ! asset_file( 'app.css' ) || ! asset_file( 'app.js' ) ) {
		echo '<div class="notice notice-error"><p>';
		esc_html_e( 'O pacote iotnest está sem os assets compilados. Instale o artefato completo gerado pelo build.', 'iotnest' );
		echo '</p></div>';
	}
}
