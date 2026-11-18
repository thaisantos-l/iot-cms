<?php
/**
 * Seção institucional ai.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="section section--gray" id="ia"><div class="container">
<?php section_heading( $args, 'section-head section-head--center' ); ?>
<div class="ai-tabs" data-ai-tabs><div class="ai-tabs__stage">
<?php
foreach ( $args['items'] as $index => $item ) {
	media_image(
		$item['image'],
		0 === $index ? 'is-on' : '',
		'full',
		0 === $index,
		array(
			'id'              => 'ai-panel-' . $item['id'],
			'role'            => 'tabpanel',
			'aria-labelledby' => 'ai-tab-' . $item['id'],
			'aria-hidden'     => 0 === $index ? 'false' : 'true',
		)
	); }
?>
</div><div class="ai-tabs__list" role="tablist" aria-label="<?php echo esc_attr( wp_strip_all_tags( $args['title'] ) ); ?>">
<?php
foreach ( $args['items'] as $index => $item ) :
	?>
	<button id="ai-tab-<?php echo esc_attr( $item['id'] ); ?>" aria-controls="ai-panel-<?php echo esc_attr( $item['id'] ); ?>" class="ai-tabs__item" role="tab" type="button" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" disabled>
	<?php
	if ( ! empty( $item['icon'] ) ) :
		?>
	<span class="ai-card__icon-box ai-card__icon-box--<?php echo esc_attr( $item['tone'] ?? 'orange' ); ?>"><?php icon( $item['icon'], 22 ); ?></span><?php endif; ?><?php echo esc_html( $item['text'] ); ?></button><?php endforeach; ?>
</div></div></div></section>
