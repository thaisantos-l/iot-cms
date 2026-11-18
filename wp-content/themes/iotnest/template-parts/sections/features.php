<?php
/**
 * Seção institucional features.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="clients" id="recursos" aria-label="<?php echo esc_attr( $args['label'] ); ?>"><div class="marquee"><ul class="marquee__track">
<?php
foreach ( $args['items'] as $item ) :
	?>
	<li class="chip"><?php echo wp_kses_post( $item['text'] ); ?></li><?php endforeach; ?>
<?php if ( ! empty( $args['animate'] ) && count( $args['items'] ) > 1 ) : ?>
	<?php
	foreach ( $args['items'] as $item ) :
		?>
		<li class="chip" aria-hidden="true"><?php echo wp_kses_post( $item['text'] ); ?></li><?php endforeach; ?>
<?php endif; ?></ul></div></section>
