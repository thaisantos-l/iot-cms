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
<?php
if ( $background ) :
	?>
	<div class="mess__bg is-active" aria-hidden="true" style="background-image:url('<?php echo esc_url( $background ); ?>')"></div><?php endif; ?>
<div class="mess__copy"><h2 class="mess__title" data-mess-title><?php echo esc_html( $first['title'] ); ?></h2><div class="lead" data-mess-text><?php echo wp_kses_post( $first['description'] ); ?></div></div>
<?php
if ( count( $args['items'] ) > 1 ) :
	?>
	<div class="mess__tabs" role="tablist" aria-label="<?php echo esc_attr( wp_strip_all_tags( $args['title'] ) ); ?>">
	<?php
	foreach ( $args['items'] as $index => $item ) :
		?>
		<button class="mess__tab" role="tab" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" type="button" disabled data-img="<?php echo esc_url( media_url( $item['image'] ?? 0 ) ); ?>" data-title="<?php echo esc_attr( $item['title'] ); ?>" data-text="<?php echo esc_attr( $item['description'] ); ?>"><strong><?php echo esc_html( $item['label'] ); ?></strong><?php text_element( 'span', $item['caption'] ?? '' ); ?></button><?php endforeach; ?></div><?php endif; ?>
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
