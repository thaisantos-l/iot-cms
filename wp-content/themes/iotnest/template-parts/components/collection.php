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
<?php foreach ( $args['posts'] as $entry ) : ?>
	<li class="case"><div class="case__img"><?php media_image( get_post_thumbnail_id( $entry ), '', 'large' ); ?></div><div class="case__body">
		<?php
		if ( 'iot_case' === $entry->post_type ) {
			$fields = get_post_meta( $entry->ID, '_iotnest_case', true );
			text_element( 'p', $fields['company'] ?? '', 'case__company' ); }
		?>
		<h3><?php echo esc_html( get_the_title( $entry ) ); ?></h3>
		<?php text_element( 'p', get_the_excerpt( $entry ) ); ?>
		<a class="case__link" href="<?php echo esc_url( get_permalink( $entry ) ); ?>"><?php echo 'iot_case' === $entry->post_type ? esc_html__( 'Ver Case', 'iotnest' ) : esc_html__( 'Ler artigo', 'iotnest' ); ?> <?php icon( 'arrow-case', 14 ); ?></a>
	</div></li>
<?php endforeach; ?>
</ul>
<?php
if ( has_text( $args['cta']['label'] ?? '' ) && \Iotnest\Core\destination_url( $args['cta'] ) ) :
	?>
	<div class="section-cta"><?php cta( $args['cta'], 'btn btn--outline', 'arrow-sm' ); ?></div><?php endif; ?>
</div></section>
