<?php
/**
 * Rodapé compartilhado e dados globais opcionais.
 *
 * @package Iotnest
 */

defined( 'ABSPATH' ) || exit;
$iotnest_settings = Iotnest\Theme\global_settings();
?>
<footer class="footer">
	<div class="container footer__inner">
		<div class="footer__brand">
			<?php Iotnest\Theme\logo( true ); ?>
			<?php if ( Iotnest\Theme\has_text( $iotnest_settings['footer_description'] ?? '' ) ) : ?>
			<p><?php echo esc_html( $iotnest_settings['footer_description'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $iotnest_settings['whatsapp'] ) ) : ?>
			<a class="btn btn--whatsapp" href="<?php echo esc_url( $iotnest_settings['whatsapp'] ); ?>" rel="noopener"><?php Iotnest\Theme\icon( 'whatsapp', 16 ); ?>WhatsApp</a>
			<?php endif; ?>
		</div>
		<?php
		foreach ( array(
			'footer' => 'Mapa do Site',
			'legal'  => 'Políticas',
		) as $iotnest_location => $iotnest_label ) :
			?>
			<?php
			$iotnest_menu = wp_nav_menu(
				array(
					'theme_location' => $iotnest_location,
					'container'      => false,
					'fallback_cb'    => false,
					'echo'           => false,
				)
			);
			?>
			<?php if ( $iotnest_menu ) : ?>
		<nav class="footer__col" aria-label="<?php echo esc_attr( $iotnest_label ); ?>">
			<h4><?php echo esc_html( $iotnest_label ); ?></h4>
				<?php echo $iotnest_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup do menu nativo. ?>
		</nav>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php $iotnest_socials = array_filter( $iotnest_settings['socials'] ?? array(), static fn( $item ) => ! empty( $item['url'] ) && Iotnest\Theme\has_text( $item['label'] ?? '' ) ); ?>
		<?php if ( $iotnest_socials ) : ?>
		<div class="footer__col"><h4>Redes Sociais</h4><ul class="social">
			<?php foreach ( $iotnest_socials as $iotnest_social ) : ?>
			<li><a href="<?php echo esc_url( $iotnest_social['url'] ); ?>" target="_blank" rel="noopener noreferrer"><span class="ico"><?php Iotnest\Theme\icon( $iotnest_social['icon'] ?? '', 18 ); ?></span><?php echo esc_html( $iotnest_social['label'] ); ?></a></li>
			<?php endforeach; ?>
		</ul></div>
		<?php endif; ?>
	</div>
	<?php if ( Iotnest\Theme\has_text( $iotnest_settings['copyright'] ?? '' ) || Iotnest\Theme\has_text( $iotnest_settings['credit_name'] ?? '' ) ) : ?>
	<div class="container footer__bottom">
		<?php
		if ( Iotnest\Theme\has_text( $iotnest_settings['copyright'] ?? '' ) ) :
			?>
			<p><?php echo esc_html( $iotnest_settings['copyright'] ); ?></p><?php endif; ?>
		<?php if ( Iotnest\Theme\has_text( $iotnest_settings['credit_name'] ?? '' ) ) : ?>
		<p><?php echo esc_html( $iotnest_settings['credit_label'] ?? '' ); ?>
			<?php
			if ( ! empty( $iotnest_settings['credit_url'] ) ) :
				?>
				<a href="<?php echo esc_url( $iotnest_settings['credit_url'] ); ?>"><?php echo esc_html( $iotnest_settings['credit_name'] ); ?></a>
				<?php
else :
	?>
				<span class="footer__credit"><?php echo esc_html( $iotnest_settings['credit_name'] ); ?></span><?php endif; ?>
		</p>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</footer>
<?php if ( ! empty( $iotnest_settings['whatsapp'] ) ) : ?>
<a class="wa-fab" href="<?php echo esc_url( Iotnest\Theme\has_text( $iotnest_settings['whatsapp_message'] ?? '' ) ? add_query_arg( 'text', $iotnest_settings['whatsapp_message'], $iotnest_settings['whatsapp'] ) : $iotnest_settings['whatsapp'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Falar com a iotnest.ai no WhatsApp"><?php Iotnest\Theme\icon( 'whatsapp', 28 ); ?></a>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
