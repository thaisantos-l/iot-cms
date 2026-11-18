<?php
/**
 * Apresentação compartilhada do conteúdo nativo.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="article" tabindex="-1"><div class="container container--narrow">
<?php
while ( have_posts() ) :
	the_post();
	$entry     = entry_data( get_post() );
	$protected = post_password_required();
	?>
	<?php breadcrumbs( ! $protected && $entry['case'] && has_text( $entry['company'] ) ? $entry['company'] : $entry['title'], in_array( get_post_type(), array( 'post', 'iot_case' ), true ) ? get_post_type() : '' ); ?>
<header class="article__head">
	<?php
	if ( ! $protected && 'page' !== get_post_type() ) {
		text_element( 'p', implode( ' · ', array_filter( array( $entry['term'], $entry['case'] ? $entry['company'] : $entry['reading'] ), __NAMESPACE__ . '\has_text' ) ), 'eyebrow' ); }
	?>
<h1><?php echo esc_html( get_the_title() ); ?></h1>
	<?php
	if ( ! $protected ) {
		text_element( 'p', $entry['excerpt'], 'article__lead' ); }
	?>
</header>
	<?php if ( $protected ) : ?>
<div class="article__body article__body--solo"><?php the_content(); ?></div>
<?php else : ?>
	<?php
	if ( media_url( $entry['image'] ) ) :
		?>
		<figure class="article__cover">
		<?php
		media_image( $entry['image'], '', 'full', true );
		text_element( 'span', $entry['term'], 'tag' );
		?>
</figure><?php endif; ?>
	<?php
	$fields  = $entry['fields'];
	$summary = array_filter(
		array(
			__( 'Empresa', 'iotnest' )  => $entry['company'],
			__( 'Segmento', 'iotnest' ) => $entry['term'],
			__( 'Solução', 'iotnest' )  => $fields['solution'] ?? '',
		),
		__NAMESPACE__ . '\has_text'
	);
	$has_cta = function_exists( '\Iotnest\Core\destination_url' ) && has_text( $fields['cta']['label'] ?? '' ) && \Iotnest\Core\destination_url( $fields['cta'] ?? array() );
	$aside   = $entry['case'] && ( $summary || $has_cta );
	?>
	<?php
	if ( $aside ) :
		?>
		<div class="article__layout"><aside class="article__aside" aria-label="<?php esc_attr_e( 'Resumo do case', 'iotnest' ); ?>">
		<?php
		if ( $summary ) :
			?>
			<dl>
			<?php
			foreach ( $summary as $label => $value ) :
				?>
			<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div><?php endforeach; ?></dl><?php endif; ?>
		<?php
		if ( $has_cta ) {
			cta( $fields['cta'], 'btn btn--sm' ); }
		?>
		</aside><?php endif; ?>
<article class="article__body<?php echo $aside ? '' : ' article__body--solo'; ?>">
	<?php
	the_content();
	wp_link_pages();
	?>
	<?php
	$highlights = array_filter( $fields['highlights'] ?? array(), static fn( $item ) => has_text( $item['value'] ?? '' ) && has_text( $item['label'] ?? '' ) ); if ( $highlights ) :
		?>
		<ul class="article__results">
		<?php
		foreach ( $highlights as $item ) :
			?>
		<li><?php echo esc_html( $item['value'] . ' ' . $item['label'] ); ?></li><?php endforeach; ?></ul><?php endif; ?>
</article>
	<?php
	if ( $aside ) :
		?>
	</div><?php endif; ?>
	<?php
	related( get_post() );
	if ( in_array( get_post_type(), array( 'post', 'iot_case' ), true ) ) {
		get_template_part( 'template-parts/components/cta-band', null, array( 'case' => $entry['case'] ) ); }
	?>
	<?php
endif;
endwhile;
?>
</div></main>
<?php get_footer(); ?>
