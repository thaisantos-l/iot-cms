<?php
/**
 * Seção institucional defects.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="section" id="apontamento"><div class="container split split--media-first<?php echo $args['image'] ? '' : ' split--text-only'; ?>">
<?php
if ( $args['image'] ) :
	?>
	<figure class="media media--soft"><?php media_image( $args['image'], '', 'full' ); ?></figure><?php endif; ?><div class="stack">
<?php
section_heading( $args );
text_element( 'div', $args['body'] ?? '' );
bullet_list( $args['bullets'], 'bullets' );
cta( $args['cta'] ?? array(), 'btn btn--outline', 'arrow-sm' );
?>
</div></div></section>
