<?php
/**
 * Seção institucional hardware.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="section section--tight-top" id="hardware"><div class="container">
<?php
section_heading( $args, 'section-head section-head--center' );
bullet_list( $args['bullets'], 'hw-facts bullets' );
?>
<?php
if ( $args['images'] ) :
	?>
	<div class="hw-carousel" data-hw aria-roledescription="carrossel" aria-label="<?php esc_attr_e( 'Hardware iotnest.ai', 'iotnest' ); ?>"><div class="hw-carousel__stage">
	<?php
	foreach ( $args['images'] as $index => $item ) {
		media_image( $item['image'], 'hw-carousel__slide', 'full', 0 === $index, array( 'data-pos' => 0 === $index ? 0 : ( 1 === $index ? 1 : ( count( $args['images'] ) - 1 === $index ? 2 : 3 ) ) ) ); }
	?>
</div>
	<?php
	if ( count( $args['images'] ) > 1 ) :
		?>
	<div class="hw-carousel__dots" role="tablist" aria-label="<?php esc_attr_e( 'Escolher imagem', 'iotnest' ); ?>">
		<?php
		foreach ( $args['images'] as $index => $item ) :
			?>
	<button type="button" role="tab" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( image_label( $index + 1 ) ); ?>" disabled></button><?php endforeach; ?></div><?php endif; ?></div><?php endif; ?>
<?php if ( has_text( $args['subtitle'] ?? '' ) || $args['groups'] || $args['image'] ) : ?>
<div class="split split--telemetry<?php echo $args['image'] ? '' : ' split--text-only'; ?>">
	<?php
	if ( has_text( $args['subtitle'] ?? '' ) || $args['groups'] ) :
		?>
		<div class="stack stack--lg">
		<?php text_element( 'h2', $args['subtitle'] ?? '' ); ?>
		<?php
		if ( $args['groups'] ) :
			?>
			<div class="signals">
			<?php
			foreach ( $args['groups'] as $group ) :
				?>
			<section class="signal"><h3 class="signal__title">
				<?php
				if ( ! empty( $group['icon'] ) ) :
					?>
		<span class="signal__icon"><?php icon( $group['icon'], 20 ); ?></span><?php endif; ?><?php echo esc_html( $group['title'] ); ?></h3>
			<?php bullet_list( $group['items'], 'bullets bullets--sm' ); ?></section><?php endforeach; ?></div><?php endif; ?></div><?php endif; ?>
	<?php
	if ( $args['image'] ) :
		?>
	<figure class="media media--telemetry"><?php media_image( $args['image'], '', 'full' ); ?></figure><?php endif; ?></div><?php endif; ?>
</div></section>
