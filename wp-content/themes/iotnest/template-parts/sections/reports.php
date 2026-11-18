<?php
/**
 * Seção institucional reports.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="section section--tight-top" id="relatorios"><div class="container split split--media-first<?php echo $args['image'] ? '' : ' split--text-only'; ?>">
<?php
if ( $args['image'] ) :
	?>
	<figure class="media media--soft"><?php media_image( $args['image'], '', 'full' ); ?></figure><?php endif; ?><div class="stack">
<?php section_heading( $args ); ?>
<?php
if ( $args['groups'] ) :
	?>
	<div class="report-lists">
	<?php
	foreach ( $args['groups'] as $group ) {
		bullet_list( $group['items'], '' ); }
	?>
</div><?php endif; ?>
</div></div></section>
