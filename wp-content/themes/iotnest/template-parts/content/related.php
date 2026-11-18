<?php
/**
 * Apresentação compartilhada do conteúdo nativo.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="related" aria-labelledby="rel-title"><div class="related__head">
<h2 id="rel-title"><?php echo $args['case'] ? esc_html__( 'Outros cases', 'iotnest' ) : esc_html__( 'Leia também', 'iotnest' ); ?></h2>
<?php $url = collection_url( $args['case'] ? 'iot_case' : 'post' ); if ( $url ) : ?>
<a class="case__link" href="<?php echo esc_url( $url ); ?>"><?php echo $args['case'] ? esc_html__( 'Ver todos os cases', 'iotnest' ) : esc_html__( 'Ver todos os artigos', 'iotnest' ); ?> <?php icon( 'arrow-case', 14 ); ?></a><?php endif; ?>
</div><ul class="listing<?php echo $args['case'] ? ' listing--2' : ''; ?>">
<?php
foreach ( $args['posts'] as $index => $related_entry ) {
	get_template_part(
		'template-parts/components/card',
		null,
		array(
			'post'  => $related_entry,
			'index' => $index + 1,
		)
	); }
?>
</ul></section>
