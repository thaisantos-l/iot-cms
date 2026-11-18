<?php
/**
 * Seção institucional contact.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="contact" id="contato"><div class="container container--narrow"><?php section_heading( $args, 'section-head section-head--center' ); ?>
<?php
$form_ready = function_exists( '\\Iotnest\\Core\\contact_context' ) && \Iotnest\Core\contact_context( get_queried_object_id() );
if ( $args['image'] || $args['channels'] || $form_ready ) :
	?>
	<div class="contact__grid"><div class="contact__side">
	<?php
	if ( $args['image'] ) :
		?>
		<figure class="contact__photo"><?php media_image( $args['image'], '', 'full' ); ?>
		<?php
		if ( has_text( $args['caption_title'] ?? '' ) || has_text( $args['caption_text'] ?? '' ) ) :
			?>
		<figcaption>
			<?php
			text_element( 'strong', $args['caption_title'] ?? '' );
			text_element( 'span', $args['caption_text'] ?? '' );
			?>
</figcaption><?php endif; ?></figure><?php endif; ?>
	<?php
	if ( $args['channels'] ) :
		?>
		<address class="contact__info">
		<?php
		foreach ( $args['channels'] as $item ) :
			?>
		<p>
			<?php
			if ( ! empty( $item['icon'] ) ) :
				?>
		<span class="ico"><?php icon( $item['icon'] ); ?></span><?php endif; ?><span>
			<?php
			if ( has_text( $item['label'] ?? '' ) ) :
				?>
		<small><?php echo esc_html( $item['label'] ); ?></small><?php endif; ?>
			<?php
			if ( ! empty( $item['url'] ) ) :
				?>
	<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo wp_kses_post( $item['value'] ); ?></a>
				<?php
		else :
			?>
			<?php echo wp_kses_post( $item['value'] ); ?><?php endif; ?></span></p><?php endforeach; ?></address><?php endif; ?>
</div>
	<?php
	if ( $form_ready ) {
		get_template_part( 'template-parts/components/contact-form' ); }
	?>
</div><?php endif; ?></div></section>
