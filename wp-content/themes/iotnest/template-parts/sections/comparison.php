<?php
/**
 * Seção institucional comparison.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="section" id="comparativo"><div class="container container--narrow">
<?php section_heading( $args, 'section-head' ); ?>
<?php
$states = array(
	'full'    => __( 'Completo', 'iotnest' ),
	'partial' => __( 'Parcial', 'iotnest' ),
	'none'    => __( 'Não tem', 'iotnest' ),
);
?>
<ul class="legend" aria-label="<?php esc_attr_e( 'Legenda', 'iotnest' ); ?>">
<?php
foreach ( array(
	'full'    => __( 'o recurso está disponível por inteiro', 'iotnest' ),
	'partial' => __( 'atende só em parte', 'iotnest' ),
	'none'    => __( 'não oferece', 'iotnest' ),
) as $state => $label ) :
	?>
	<li><span class="mark" data-s="<?php echo esc_attr( $state ); ?>"><i aria-hidden="true"></i><b><?php echo esc_html( $states[ $state ] ); ?></b></span> <?php echo esc_html( $label ); ?></li><?php endforeach; ?></ul>
<div class="compare" role="region" aria-label="<?php echo esc_attr( wp_strip_all_tags( $args['title'] ) ); ?>" tabindex="0"><table><thead><tr><th scope="col" class="compare__feature"><span class="sr"><?php esc_html_e( 'Recurso', 'iotnest' ); ?></span></th>
<?php
foreach ( $args['columns'] as $column ) :
	?>
	<th scope="col"<?php echo ! empty( $column['highlight'] ) ? ' class="compare__brand"' : ''; ?>>
	<?php
	echo esc_html( $column['label'] );
	if ( has_text( $column['marker'] ?? '' ) ) {
		echo '<sup>' . esc_html( $column['marker'] ) . '</sup>';
	}
	?>
	</th><?php endforeach; ?>
</tr></thead><tbody>
<?php
foreach ( $args['items'] as $row ) :
	?>
	<tr><th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
	<?php $cells = array_column( $row['cells'] ?? array(), 'state', 'column_id' ); ?>
	<?php
	foreach ( $args['columns'] as $column ) :
		?>
		<td<?php echo ! empty( $column['highlight'] ) ? ' class="compare__brand"' : ''; ?>>
		<?php
		$state = $cells[ $column['id'] ] ?? ''; if ( isset( $states[ $state ] ) ) :
			?>
		<span class="mark" data-s="<?php echo esc_attr( $state ); ?>"><i aria-hidden="true"></i><b><?php echo esc_html( $states[ $state ] ); ?></b></span><?php endif; ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
<?php text_element( 'p', $args['note'] ?? '', 'compare__foot' ); ?></div></section>
