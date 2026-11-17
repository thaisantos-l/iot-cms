<?php
/**
 * Seção institucional energy.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="section section--tight-top" id="energia"><div class="container split split--energy<?php echo $args['image'] ? '' : ' split--text-only'; ?>"><div class="stack">
<?php
section_heading( $args );
text_element( 'div', $args['body'] ?? '' );
bullet_list( $args['bullets'] );
?>
</div>
<?php
if ( $args['image'] ) :
	?>
	<div class="energy-visual"><span class="energy-visual__blob" aria-hidden="true"></span><figure class="energy-visual__shot"><?php media_image( $args['image'], '', 'full' ); ?></figure><span class="energy-visual__glass" aria-hidden="true"></span><?php bullet_list( $args['labels'] ); ?></div><?php endif; ?></div></section>
