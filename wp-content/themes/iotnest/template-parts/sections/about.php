<?php
/**
 * Seção institucional about.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="section section--tight-top" id="sobre"><div class="container container--narrow split split--about<?php echo $args['image'] ? '' : ' split--text-only'; ?>">
<?php
if ( $args['image'] ) :
	?>
	<figure class="about__media"><?php media_image( $args['image'], '', 'full' ); ?>
	<?php
	if ( isset( $args['number'] ) && is_numeric( $args['number'] ) && has_text( $args['label'] ?? '' ) ) :
		?>
	<figcaption><strong><span data-count="<?php echo esc_attr( $args['number'] ); ?>"><span aria-hidden="true" class="count-reserve"><?php echo esc_html( $args['number'] ); ?></span><span data-count-value><?php echo esc_html( $args['number'] ); ?></span></span><?php echo esc_html( $args['unit'] ?? '' ); ?></strong><span><?php echo esc_html( $args['label'] ); ?></span></figcaption><?php endif; ?></figure><?php endif; ?>
<div class="stack">
<?php
section_heading( $args );
foreach ( $args['paragraphs'] as $paragraph ) {
	text_element( 'p', $paragraph['text'], 'body' );
} bullet_list( $args['bullets'], 'about__list' );
?>
</div></div></section>
