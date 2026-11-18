<?php
/**
 * Assets locais identificados por conteúdo, sem depender de Node em produção.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Resolve um nome lógico sem aceitar caminhos fora do diretório do build.
 *
 * @param string $entry Nome lógico no manifesto.
 * @return string Nome compilado ou string vazia.
 */
function asset_file( $entry ) {
	static $manifest;
	$directory = get_template_directory() . '/assets/dist/';
	if ( null === $manifest ) {
		$path = $directory . 'manifest.json';
		// Manifesto local do build; não é uma requisição HTTP.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$manifest = is_readable( $path ) ? json_decode( file_get_contents( $path ), true ) : array();
		$manifest = is_array( $manifest ) ? $manifest : array();
	}
	$file = $manifest[ $entry ] ?? '';
	if ( ! is_string( $file ) || ! preg_match( '/\A[a-z0-9-]+\.[a-f0-9]{12}\.(css|js)\z/', $file ) ) {
		return '';
	}
	if ( pathinfo( $entry, PATHINFO_EXTENSION ) !== pathinfo( $file, PATHINFO_EXTENSION ) ) {
		return '';
	}
	return is_readable( $directory . $file ) ? $file : '';
}

/** Carrega apenas arquivos efetivamente presentes no artefato. */
function enqueue_assets() {
	$base = get_template_directory_uri() . '/assets/dist/';
	$css  = asset_file( 'app.css' );
	if ( $css ) {
		wp_enqueue_style( 'iotnest-app', $base . $css, array(), explode( '.', $css )[1] );
	}
	foreach ( array( 'app', 'home' ) as $entry ) {
		if ( 'home' === $entry && ! is_front_page() && ! is_page_template( 'page-templates/institutional.php' ) ) {
			continue;
		}
		$file = asset_file( $entry . '.js' );
		if ( $file ) {
			wp_enqueue_script(
				'iotnest-' . $entry,
				$base . $file,
				array(),
				explode( '.', $file )[1],
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}
	$settings = global_settings();
	$flags    = array();
	foreach ( array( 'motion', 'lenis', 'autoplay', 'counters', 'dada_scroll', 'calculator', 'youtube' ) as $flag ) {
		$flags[ $flag ] = (bool) ( $settings[ $flag ] ?? false );
	}
	if ( asset_file( 'app.js' ) ) {
		if ( function_exists( '\\Iotnest\\Core\\integration_config' ) ) {
			wp_add_inline_script( 'iotnest-app', 'window.iotnestIntegrations=' . wp_json_encode( \Iotnest\Core\integration_config() ) . ';', 'before' );
		}
		wp_add_inline_script( 'iotnest-app', 'window.iotnestConfig=' . wp_json_encode( $flags ) . ';', 'before' );
	}
}
