<?php
/**
 * Conteúdo existente, sem defaults editoriais criados pelo tema.
 *
 * @package Iotnest
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class(); ?>>
	<?php if ( is_singular() ) : ?>
		<h1><?php the_title(); ?></h1>
		<?php the_content(); ?>
		<?php wp_link_pages(); ?>
	<?php else : ?>
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php the_excerpt(); ?>
	<?php endif; ?>
</article>
