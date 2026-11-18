<?php
/**
 * Seção institucional calculator.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<?php
$labels   = array_column( $args['labels'], 'text', 'key' );
$messages = array_column( $args['messages'] ?? array(), 'text', 'key' );
?>
<section class="calc" id="calculadora" data-calc><div class="container calc__head"><div class="calc__intro">
<?php
text_element( 'p', $args['eyebrow'] ?? '', 'eyebrow eyebrow--light' );
text_element( 'h2', $args['title'] );
text_element( 'p', $args['description'] ?? '' );
?>
</div></div>
<?php if ( empty( $args['form_ready'] ) ) : ?>
</section>
	<?php return; ?>
<?php endif; ?>
<div class="container calc__grid"><form class="panel panel--glass" data-calc-form novalidate><fieldset class="calc__group" disabled>
<?php
if ( has_text( $labels['group'] ?? '' ) ) :
	?>
	<legend><?php echo esc_html( $labels['group'] ); ?></legend><?php endif; ?><div class="field-grid">
<?php
foreach ( array(
	'rate'  => array( 0.01, null, '200' ),
	'hours' => array( 0.1, 24, '22' ),
	'days'  => array( 1, 31, '22' ),
	'atual' => array( 0, 100, '60' ),
	'iot'   => array( 0, 100, '80' ),
) as $key => $limits ) :
	?>
<div class="field"><label for="c-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $labels[ $key ] ?? '' ); ?></label><input id="c-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="number" inputmode="<?php echo 'days' === $key ? 'numeric' : 'decimal'; ?>" min="<?php echo esc_attr( $limits[0] ); ?>"
	<?php
	if ( null !== $limits[1] ) :
		?>
	max="<?php echo esc_attr( $limits[1] ); ?>"<?php endif; ?> step="<?php echo 'days' === $key ? '1' : 'any'; ?>" placeholder="<?php echo esc_attr( $limits[2] ); ?>" required></div><?php endforeach; ?></div></fieldset>
<p class="calc__error" data-calc-error role="alert" hidden></p><button class="btn btn--dark btn--block" type="submit" disabled><?php echo esc_html( $labels['submit'] ?? '' ); ?> <?php icon( 'arrow-calc' ); ?></button>
<?php text_element( 'p', $messages['note'] ?? '', 'panel__note' ); ?></form>
<section class="panel panel--glass calc__result" data-calc-out aria-live="polite" aria-labelledby="calc-result-title"><h3 id="calc-result-title"><?php echo esc_html( $labels['result'] ?? '' ); ?></h3>
<?php
foreach ( array(
	'atual' => 'current',
	'iot'   => 'target',
) as $key => $label ) :
	?>
	<div class="result<?php echo 'iot' === $key ? ' result--meta' : ''; ?>" data-sc="<?php echo esc_attr( $key ); ?>"><p class="result__item"><span class="result__label"><?php echo esc_html( $labels[ $label ] ?? '' ); ?></span><strong class="result__oee" data-oee>—</strong></p><p class="result__item"><span class="result__label"><?php echo esc_html( $labels[ 'iot' === $key ? 'loss_target' : 'loss' ] ?? '' ); ?></span><strong class="result__loss" data-loss>—</strong></p></div><?php endforeach; ?>
<p class="result__hint" data-calc-hint><?php echo esc_html( $messages['hint'] ?? '' ); ?></p></section></div>
<?php
if ( has_text( $args['cta']['label'] ?? '' ) && \Iotnest\Core\destination_url( $args['cta'] ) ) :
	?>
	<div class="container calc__cta-wrap" data-calc-cta hidden><div class="calc__cta">
	<?php
	text_element( 'p', $messages['cta'] ?? '' );
	cta( $args['cta'], 'btn btn--light' );
	?>
</div></div><?php endif; ?></section>
