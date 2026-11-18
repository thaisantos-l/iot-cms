<?php
/**
 * Apresentação compartilhada do conteúdo nativo.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
$entry   = entry_data( $args['post'] );
$variant = 1 + absint( $args['index'] ?? 0 ) % 3;
?>
<li class="case">
<a class="case__img case__img--<?php echo absint( $variant ); ?>" href="<?php echo esc_url( $entry['url'] ); ?>" tabindex="-1" aria-hidden="true"><?php media_image( $entry['image'], '', 'large' ); ?><?php text_element( 'span', $entry['term'], 'tag' ); ?></a>
<div class="case__body">
<?php text_element( 'p', $entry['case'] ? $entry['company'] : $entry['reading'], 'case__company' ); ?>
<h3><?php echo esc_html( $entry['title'] ); ?></h3>
<?php text_element( 'p', $entry['excerpt'] ); ?>
<a class="case__link" href="<?php echo esc_url( $entry['url'] ); ?>"><?php echo $entry['case'] ? esc_html__( 'Ver Case', 'iotnest' ) : esc_html__( 'Ler artigo', 'iotnest' ); ?> <?php icon( 'arrow-case', 14 ); ?></a>
</div></li>
