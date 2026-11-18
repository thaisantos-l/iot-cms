<?php
/**
 * Painel compartilhado das etapas, inclusive no fallback sem JavaScript.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
$item  = $args['item'];
$index = $args['index'];
$total = $args['total'];
?>
<div class="how__panel<?php echo ! empty( $args['fallback'] ) ? ' how__panel--fallback' : ''; ?>" id="<?php echo ! empty( $args['fallback'] ) ? 'fallback-' : ''; ?>how-panel-<?php echo esc_attr( $item['id'] ); ?>" role="tabpanel" aria-labelledby="how-tab-<?php echo esc_attr( $item['id'] ); ?>" data-step-item="<?php echo esc_attr( $item['id'] ); ?>"<?php echo $args['inactive'] ? ' hidden' : ''; ?>>
<div class="how__copy"><div class="how__head">
	<?php
	if ( ! empty( $item['icon'] ) ) :
		?>
	<span class="how__icon"><?php icon( $item['icon'], 32 ); ?></span><?php endif; ?><div>
<p class="how__step" data-how-step><?php echo esc_html( step_label( $index + 1 ) ); ?></p><h3 data-how-title><?php echo esc_html( $item['title'] ); ?></h3></div></div>
<div class="how__text" data-how-text><?php echo wp_kses_post( $item['text'] ); ?></div>
	<?php
	if ( $total > 1 ) :
		?>
		<div class="how__controls"><button class="icon-btn icon-btn--light" type="button" data-how-prev aria-label="<?php esc_attr_e( 'Etapa anterior', 'iotnest' ); ?>" disabled><?php icon( 'chev-left-w' ); ?></button><button class="icon-btn icon-btn--light" type="button" data-how-next aria-label="<?php esc_attr_e( 'Próxima etapa', 'iotnest' ); ?>" disabled><?php icon( 'chev-right-w' ); ?></button><span class="how__count" data-how-count><?php echo esc_html( position_label( $index + 1, $total ) ); ?></span></div><?php endif; ?></div>
	<?php
	if ( media_url( $item['image'] ?? 0 ) ) :
		?>
	<figure class="how__media"<?php echo 'contain' === ( $item['fit'] ?? '' ) ? ' data-contain' : ''; ?>><?php media_image( $item['image'], '', 'full', false, array( 'data-how-img' => '' ) ); ?><figcaption class="how__tag" data-how-tag><?php echo esc_html( step_label( $index + 1 ) ); ?></figcaption></figure><?php endif; ?></div>
