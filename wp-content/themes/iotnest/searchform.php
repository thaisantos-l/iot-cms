<?php
/**
 * Apresentação compartilhada do conteúdo nativo.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<form class="site-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"><label><span><?php esc_html_e( 'Buscar no site', 'iotnest' ); ?></span><input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" required></label><button class="btn btn--sm" type="submit"><?php esc_html_e( 'Buscar', 'iotnest' ); ?></button></form>
