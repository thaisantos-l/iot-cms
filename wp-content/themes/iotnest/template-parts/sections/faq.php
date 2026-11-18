<?php
/**
 * Seção institucional faq.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="section section--tight-top" id="faq"><div class="container container--narrow"><?php section_heading( $args, 'section-head section-head--center' ); ?><div class="faq">
<?php
foreach ( $args['items'] as $index => $item ) :
	?>
	<details class="faq__item" name="faq"<?php echo 0 === $index ? ' open' : ''; ?>><summary><span><?php echo esc_html( $item['question'] ); ?></span><i class="faq__icon" aria-hidden="true"></i></summary><div class="faq__answer"><?php echo wp_kses_post( $item['answer'] ); ?></div></details><?php endforeach; ?></div>
<?php
if ( has_text( $args['cta']['label'] ?? '' ) && \Iotnest\Core\destination_url( $args['cta'] ) ) :
	?>
	<p class="faq__cta"><?php echo esc_html( $args['cta_intro'] ?? '' ); ?> <?php cta( $args['cta'], '' ); ?></p><?php endif; ?></div></section>
