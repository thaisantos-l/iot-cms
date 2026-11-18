<?php
/**
 * Coleção preparada no view model; nenhum item consulta sua própria lista.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="section section--tight-top" id="<?php echo esc_attr( $args['anchor'] ); ?>" data-carousel data-count="<?php echo count( $args['posts'] ); ?>"><div class="container container--narrow">
<header class="cases-head"><div><?php section_heading( $args ); ?></div>
<?php if ( count( $args['posts'] ) > 1 ) : ?>
<div class="arrows" data-cases-arrows><button class="icon-btn" type="button" data-cases-prev aria-label="<?php esc_attr_e( 'Anterior', 'iotnest' ); ?>"><?php icon( 'chev-left', 18 ); ?></button><button class="icon-btn" type="button" data-cases-next aria-label="<?php esc_attr_e( 'Próximo', 'iotnest' ); ?>"><?php icon( 'chev-right', 18 ); ?></button></div>
<?php endif; ?></header>
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
if ( count( $args['posts'] ) > 1 ) :
	?>
	<div class="dots" data-cases-dots aria-hidden="true">
	<?php
	foreach ( $args['posts'] as $index => $entry ) :
		?>
		<i<?php echo 0 === $index ? ' class="is-active"' : ''; ?>></i><?php endforeach; ?></div><?php endif; ?>
<?php
if ( has_text( $args['cta']['label'] ?? '' ) && \Iotnest\Core\destination_url( $args['cta'] ) ) :
	?>
	<div class="section-cta"><?php cta( $args['cta'], 'btn btn--outline', 'arrow-sm' ); ?></div><?php endif; ?>
</div></section>
