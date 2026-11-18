<?php
/**
 * Apresentação compartilhada do conteúdo nativo.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
global $wp_query;
if ( $wp_query->max_num_pages > 1 ) {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => __( 'Anterior', 'iotnest' ),
			'next_text' => __( 'Próxima', 'iotnest' ),
		)
	);
}
