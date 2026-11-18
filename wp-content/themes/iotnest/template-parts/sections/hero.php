<?php
/**
 * Seção institucional hero.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="hero" id="topo">
<?php background_video( $args, 'hero__bg', true ); ?>
<div class="hero__content">
<?php
text_element( 'span', $args['eyebrow'] ?? '', 'badge' );
text_element( 'h1', $args['title'], 'hero__title' );
text_element( 'p', $args['description'] ?? '', 'hero__text' );
?>
<?php
foreach ( $args['ctas'] ?? array() as $cta_item ) {
	cta( $cta_item, 'btn hero__cta', 'arrow-calc' ); }
?>
</div></section>
