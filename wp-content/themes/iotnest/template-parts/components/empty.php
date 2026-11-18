<?php
/**
 * Apresentação compartilhada do conteúdo nativo.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<div class="empty-state"><p><?php echo esc_html( $args['message'] ?? __( 'Nenhum conteúdo publicado por enquanto.', 'iotnest' ) ); ?></p><?php get_search_form(); ?></div>
