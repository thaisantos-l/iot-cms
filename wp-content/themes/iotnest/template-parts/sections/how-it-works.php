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
foreach ( $args['items'] as $index => $item ) :
	?>
	<div class="how__panel" data-step-item="<?php echo esc_attr( $item['id'] ); ?>"<?php echo $index ? ' hidden' : ''; ?>>
<div class="how__copy"><div class="how__head">
	<?php
	if ( ! empty( $item['icon'] ) ) :
		?>
	<span class="how__icon"><?php icon( $item['icon'], 32 ); ?></span><?php endif; ?><div>
<p class="how__step" data-how-step><?php echo esc_html( step_label( $index + 1 ) ); ?></p><h3 data-how-title><?php echo esc_html( $item['title'] ); ?></h3></div></div>
<div class="how__text" data-how-text><?php echo wp_kses_post( $item['text'] ); ?></div>
	<?php
	if ( count( $args['items'] ) > 1 ) :
		?>
		<div class="how__controls"><button class="icon-btn icon-btn--light" type="button" data-how-prev aria-label="<?php esc_attr_e( 'Etapa anterior', 'iotnest' ); ?>" disabled><?php icon( 'chev-left-w' ); ?></button><button class="icon-btn icon-btn--light" type="button" data-how-next aria-label="<?php esc_attr_e( 'Próxima etapa', 'iotnest' ); ?>" disabled><?php icon( 'chev-right-w' ); ?></button><span class="how__count" data-how-count><?php echo esc_html( position_label( $index + 1, count( $args['items'] ) ) ); ?></span></div><?php endif; ?></div>
	<?php
	if ( media_url( $item['image'] ?? 0 ) ) :
		?>
	<figure class="how__media"<?php echo 'contain' === ( $item['fit'] ?? '' ) ? ' data-contain' : ''; ?>><?php media_image( $item['image'], '', 'full', false, array( 'data-how-img' => '' ) ); ?><figcaption class="how__tag" data-how-tag><?php echo esc_html( step_label( $index + 1 ) ); ?></figcaption></figure><?php endif; ?></div><?php endforeach; ?>
<noscript><style>.how__panel[hidden]{display:grid;margin-top:32px}</style></noscript></div></section>
