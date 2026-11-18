<?php
/**
 * Coleção preparada no view model; nenhum item consulta sua própria lista.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="section section--tight-top" id="<?php echo esc_attr( $args['anchor'] ); ?>" data-carousel><div class="container container--narrow">
<?php section_heading( $args, 'cases-head' ); ?>
<ul class="cases" data-cases>
<?php
foreach ( $args['posts'] as $index => $entry ) {
	get_template_part(
		'template-parts/components/card',
		null,
		array(
			'post'  => $entry,
			'index' => $index,
		)
	); }
?>
</ul>
<?php
if ( has_text( $args['cta']['label'] ?? '' ) && \Iotnest\Core\destination_url( $args['cta'] ) ) :
	?>
	<div class="section-cta"><?php cta( $args['cta'], 'btn btn--outline', 'arrow-sm' ); ?></div><?php endif; ?>
</div></section>
