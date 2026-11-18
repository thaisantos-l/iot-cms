<?php
/**
 * Seção institucional mes.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<?php
$first      = $args['items'][0];
$background = media_url( $first['image'] ?? 0 );
?>
<section class="section section--tight-top" id="mess"><div class="container"><div class="mess" data-mess>
<div class="mess__bg is-active" aria-hidden="true"
<?php
if ( $background ) :
	?>
	style="background-image:url('<?php echo esc_url( $background ); ?>')"<?php endif; ?>></div>
<div class="mess__bg" aria-hidden="true"></div>
<div class="mess__copy">
<?php foreach ( $args['items'] as $index => $item ) : ?>
<div class="mess__panel<?php echo 0 === $index ? ' is-active' : ''; ?>" data-mess-panel data-image="<?php echo esc_url( media_url( $item['image'] ?? 0 ) ); ?>" id="mess-panel-<?php echo esc_attr( $item['id'] ); ?>" role="tabpanel" aria-labelledby="mess-tab-<?php echo esc_attr( $item['id'] ); ?>"<?php echo 0 !== $index ? ' aria-hidden="true" inert' : ''; ?>>
<h2 class="mess__title" data-mess-title><?php echo esc_html( $item['title'] ); ?></h2><div class="lead" data-mess-text><?php echo wp_kses_post( $item['description'] ); ?></div></div>
<?php endforeach; ?></div>
<?php
if ( count( $args['items'] ) > 1 ) :
	?>
	<div class="mess__tabs" role="tablist" aria-label="<?php echo esc_attr( wp_strip_all_tags( $args['title'] ) ); ?>">
	<?php
	foreach ( $args['items'] as $index => $item ) :
		?>
		<button id="mess-tab-<?php echo esc_attr( $item['id'] ); ?>" aria-controls="mess-panel-<?php echo esc_attr( $item['id'] ); ?>" class="mess__tab" role="tab" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" type="button" disabled data-img="<?php echo esc_url( media_url( $item['image'] ?? 0 ) ); ?>" data-title="<?php echo esc_attr( $item['title'] ); ?>" data-text="<?php echo esc_attr( $item['description'] ); ?>"><strong><?php echo esc_html( $item['label'] ); ?></strong><?php text_element( 'span', $item['caption'] ?? '' ); ?></button><?php endforeach; ?></div><?php endif; ?>
</div><noscript>
<?php
foreach ( array_slice( $args['items'], 1 ) as $item ) :
	?>
	<section class="stack">
	<?php
	text_element( 'h3', $item['title'] );
	text_element( 'div', $item['description'] );
	media_image( $item['image'] ?? 0, '', 'full' );
	?>
</section><?php endforeach; ?></noscript></div></section>
