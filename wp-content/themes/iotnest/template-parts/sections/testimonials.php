<?php
/**
 * Seção institucional testimonials.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="section" id="depoimentos"><div class="container container--narrow"><?php section_heading( $args, 'section-head section-head--center' ); ?>
<?php
foreach ( $args['items'] as $item ) :
	?>
	<figure class="quote"><figcaption class="quote__author">
	<?php
	if ( media_url( $item['avatar'] ?? 0 ) ) :
		?>
		<?php media_image( $item['avatar'], 'avatar', 'thumbnail' ); ?>
		<?php
elseif ( has_text( $item['initials'] ?? '' ) ) :
	?>
		<span class="avatar" aria-hidden="true"><?php echo esc_html( $item['initials'] ); ?></span><?php endif; ?>
<span>
	<?php
	text_element( 'strong', $item['name'] );
	text_element( 'span', $item['role'] ?? '' );
	text_element( 'em', $item['company'] ?? '' );
	?>
</span></figcaption><blockquote><?php echo wp_kses_post( $item['quote'] ); ?></blockquote></figure><?php endforeach; ?></div></section>
