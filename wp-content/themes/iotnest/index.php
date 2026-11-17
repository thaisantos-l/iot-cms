<?php
/**
 * Fallback seguro do loop nativo, até os templates específicos da etapa 05.
 *
 * @package Iotnest
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" tabindex="-1">
	<?php
	while ( have_posts() ) {
		the_post();
		get_template_part( 'template-parts/content/default' );
	}
	the_posts_pagination();
	?>
</main>
<?php get_footer(); ?>
