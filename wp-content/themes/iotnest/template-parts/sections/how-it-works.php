<?php
/**
 * Seção institucional how_it_works.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="how" id="como-funciona" data-how data-step="1"><div class="container container--narrow"><header class="section-head section-head--center">
<?php
text_element( 'p', $args['eyebrow'] ?? '', 'eyebrow eyebrow--light eyebrow--lines' );
text_element( 'h2', $args['title'] );
text_element( 'p', $args['description'] ?? '', 'lead' );
?>
</header>
<?php
if ( count( $args['items'] ) > 1 ) :
	?>
	<div class="how__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Etapas', 'iotnest' ); ?>">
	<?php
	foreach ( $args['items'] as $index => $item ) :
		?>
	<button class="pill" role="tab" type="button" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" disabled><b><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></b><?php echo esc_html( $item['label'] ?? '' ); ?></button><?php endforeach; ?></div><?php endif; ?>
<?php
foreach ( $args['items'] as $index => $item ) {
	get_template_part(
		'template-parts/components/how-panel',
		null,
		array(
			'item'     => $item,
			'index'    => $index,
			'total'    => count( $args['items'] ),
			'inactive' => 0 !== $index,
		)
	);
}
?>
<noscript>
<style>[data-how] .how__tabs,[data-how] .how__controls{display:none}</style>
<?php
foreach ( $args['items'] as $index => $item ) {
	if ( $index ) {
		get_template_part(
			'template-parts/components/how-panel',
			null,
			array(
				'item'     => $item,
				'index'    => $index,
				'total'    => 0,
				'inactive' => false,
				'fallback' => true,
			)
		);
	}
}
?>
</noscript></div></section>
