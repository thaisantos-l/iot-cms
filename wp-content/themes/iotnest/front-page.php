<?php
/**
 * Home composta exclusivamente por seções editoriais disponíveis.
 *
 * @package Iotnest
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" tabindex="-1">
	<?php if ( is_page() && have_posts() ) : ?>
		<?php the_post(); ?>
		<?php $sections = \Iotnest\Theme\home_sections( get_the_ID() ); ?>
		<?php if ( ! isset( $sections['hero'] ) ) : ?>
			<h1 class="screen-reader-text"><?php the_title(); ?></h1>
		<?php endif; ?>
		<?php foreach ( $sections as $section => $data ) : ?>
			<?php get_template_part( 'template-parts/sections/' . str_replace( '_', '-', $section ), null, $data ); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
