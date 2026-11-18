<?php
/**
 * Template Name: Documento legal
 * Template Post Type: page
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="legal" tabindex="-1"><div class="container container--narrow">
<?php
while ( have_posts() ) :
	the_post();
	breadcrumbs( get_the_title() );
	?>
<header class="legal__head"><p class="eyebrow"><?php esc_html_e( 'Legal', 'iotnest' ); ?></p><h1><?php echo esc_html( get_the_title() ); ?></h1>
	<?php
	if ( ! post_password_required() ) {
		text_element( 'p', get_post_field( 'post_excerpt', get_the_ID() ), 'legal__meta' ); }
	?>
</header>
	<?php
	if ( post_password_required() ) :
		the_content();
else :
	$document = current_legal_document();
	?>
<div class="legal__grid<?php echo $document['toc'] ? '' : ' legal__grid--solo'; ?>">
		<?php
		if ( $document['toc'] ) :
			?>
			<aside class="legal__toc" aria-label="<?php esc_attr_e( 'Neste documento', 'iotnest' ); ?>"><p><?php esc_html_e( 'Neste documento', 'iotnest' ); ?></p><ol>
			<?php
			foreach ( $document['toc'] as $item ) :
				?>
			<li><a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li><?php endforeach; ?></ol>
			<?php
			$others = array();
			foreach ( array( 'privacidade', 'cookies', 'termos' ) as $slug ) {
				$other = get_page_by_path( $slug );
				if ( $other && get_the_ID() !== $other->ID && 'publish' === $other->post_status && ! $other->post_password ) {
					$others[] = $other; }
			}
			?>
			<?php
			if ( $others ) :
				?>
				<p class="legal__toc-other"><?php esc_html_e( 'Outros documentos', 'iotnest' ); ?></p><ul>
				<?php
				foreach ( $others as $other ) :
					?>
				<li><a href="<?php echo esc_url( get_permalink( $other ) ); ?>"><?php echo esc_html( $other->post_title ); ?></a></li><?php endforeach; ?></ul><?php endif; ?>
</aside><?php endif; ?>
<article class="legal__body"><?php echo wp_kses_post( $document['html'] ); ?></article></div>
	<?php
endif;
endwhile;
?>
</div></main><?php get_footer(); ?>
