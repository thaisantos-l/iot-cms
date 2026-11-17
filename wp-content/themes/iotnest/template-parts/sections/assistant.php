<?php
/**
 * Seção institucional assistant.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="dada-full" aria-labelledby="dada-title"><header class="section-head section-head--center dada-head container" id="dada"><h2 id="dada-title"><?php echo wp_kses_post( $args['title'] ); ?></h2><?php text_element( 'p', $args['description'] ?? '', 'lead' ); ?></header>
<div class="dada-full__frame" data-dada-frame><?php background_video( $args, 'dada__bg' ); ?><div class="dada-full__veil" aria-hidden="true"></div>
<?php if ( 'local' === ( $args['video']['provider'] ?? '' ) && media_url( $args['video']['file'] ?? 0, 'video' ) ) : ?>
<div class="dada-full__bar">
<button class="dada-full__toggle is-paused" type="button" data-dada-toggle aria-label="<?php esc_attr_e( 'Reproduzir vídeo', 'iotnest' ); ?>" disabled>
	<svg class="ic-pause" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1.2"/><rect x="14" y="5" width="4" height="14" rx="1.2"/></svg>
	<svg class="ic-play" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5Z"/></svg>
</button>
<div class="dada-full__progress" data-dada-progress role="progressbar" aria-label="<?php esc_attr_e( 'Progresso do vídeo', 'iotnest' ); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><i></i></div>
</div>
<?php endif; ?>
</div></section>
