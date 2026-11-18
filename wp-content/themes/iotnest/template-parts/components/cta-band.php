<?php
/**
 * Apresentação compartilhada do conteúdo nativo.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
$data = global_settings()[ 'archive_' . ( $args['case'] ? 'cases' : 'blog' ) ] ?? array();
if ( ! has_text( $data['cta_title'] ?? '' ) || ! function_exists( '\Iotnest\Core\destination_url' ) || ! has_text( $data['cta']['label'] ?? '' ) || ! \Iotnest\Core\destination_url( $data['cta'] ?? array() ) ) {
	return; }
?>
<aside class="cta-band"><div>
<?php
text_element( 'h2', $data['cta_title'] );
text_element( 'p', $data['cta_description'] ?? '' );
?>
</div><?php cta( $data['cta'], 'btn btn--light' ); ?></aside>
