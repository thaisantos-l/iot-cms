<?php
/**
 * Título de seção reutilizável.
 *
 * @package Iotnest
 */

defined( 'ABSPATH' ) || exit;
$data    = $args['data'];
$classes = $args['classes'];
if ( $classes ) {
	echo '<header class="' . esc_attr( $classes ) . '">';
}
\Iotnest\Theme\text_element( 'p', $data['eyebrow'] ?? '', 'eyebrow' );
\Iotnest\Theme\text_element( 'h2', $data['title'] ?? '' );
\Iotnest\Theme\text_element( 'p', $data['description'] ?? '', 'lead' );
if ( $classes ) {
	echo '</header>';
}
