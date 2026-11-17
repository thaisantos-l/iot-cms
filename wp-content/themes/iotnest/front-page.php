<?php
/**
 * Base da home: seções editoriais serão adicionadas na etapa 04.
 *
 * @package Iotnest
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" tabindex="-1">
	<?php if ( is_page() && have_posts() ) : ?>
		<?php the_post(); ?>
		<h1 class="screen-reader-text"><?php the_title(); ?></h1>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
