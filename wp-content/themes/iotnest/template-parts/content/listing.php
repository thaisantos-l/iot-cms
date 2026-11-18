<?php
/**
 * Apresentação compartilhada do conteúdo nativo.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
get_header();
$case_archive  = is_post_type_archive( 'iot_case' );
$posts_page    = is_home() ? get_post( (int) get_option( 'page_for_posts' ) ) : null;
$settings      = global_settings()[ $case_archive ? 'archive_cases' : 'archive_blog' ] ?? array();
$listing_title = $case_archive ? ( $settings['title'] ?? __( 'Cases', 'iotnest' ) ) : ( $posts_page ? $posts_page->post_title : get_the_archive_title() );
$description   = $case_archive ? ( $settings['description'] ?? '' ) : ( $posts_page ? $posts_page->post_excerpt : get_the_archive_description() );
$label         = $case_archive ? __( 'Cases', 'iotnest' ) : ( is_home() ? __( 'Blog', 'iotnest' ) : wp_strip_all_tags( $listing_title ) );
if ( is_search() ) {
	// translators: %s is the visitor search text.
	$listing_title = sprintf( __( 'Resultados para: %s', 'iotnest' ), get_search_query() );
	$label         = __( 'Busca', 'iotnest' );
	$description   = ''; }
?>
<main id="main" class="legal" tabindex="-1"><div class="container container--narrow">
<?php breadcrumbs( $label ); ?>
<header class="legal__head">
<?php text_element( 'p', ( $case_archive || is_home() ) ? ( $settings['eyebrow'] ?? '' ) : '', 'eyebrow' ); ?>
<h1><?php echo esc_html( wp_strip_all_tags( $listing_title ) ); ?></h1>
<?php text_element( 'p', $description, 'lead' ); ?>
</header>
<?php if ( have_posts() ) : ?>
<ul class="listing">
	<?php
	$index = 0;
	while ( have_posts() ) {
		the_post();
		get_template_part(
			'template-parts/components/card',
			null,
			array(
				'post'  => get_post(),
				'index' => $index++,
			)
		); }
	?>
</ul>
	<?php
	get_template_part( 'template-parts/components/pagination' );
else :
	get_template_part( 'template-parts/components/empty' );
endif;
?>
<?php
if ( is_home() || $case_archive ) {
	get_template_part( 'template-parts/components/cta-band', null, array( 'case' => $case_archive ) ); }
?>
</div></main>
<?php get_footer(); ?>
