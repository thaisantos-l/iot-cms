<?php
/**
 * Seção institucional videos.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
?>
<section class="section section--tight-top" id="videos"><div class="container container--narrow"><?php section_heading( $args, 'section-head' ); ?><ul class="videos">
<?php
foreach ( $args['items'] as $item ) :
	?>
	<?php
	$video = $item['video'];
	$url   = 'youtube' === $video['provider'] ? 'https://www.youtube.com/watch?v=' . $video['youtube'] . '&t=' . absint( $video['start'] ?? 0 ) : media_url( $video['file'] ?? 0, 'video' );
	$thumb = media_url( $item['thumbnail'] ?? 0 );
	?>
<li class="video"><a class="video__thumb" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" data-video-id="<?php echo esc_attr( $video['youtube'] ?? '' ); ?>" data-video-start="<?php echo absint( $video['start'] ?? 0 ); ?>" data-video-title="<?php echo esc_attr( $item['title'] ); ?>" aria-label="<?php echo esc_attr( $item['title'] ); ?>"
	<?php
	if ( $thumb ) :
		?>
	style="background-image:url('<?php echo esc_url( $thumb ); ?>')"<?php endif; ?>>
<span class="video__play" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M7 4.5v13l11-6.5-11-6.5Z" fill="currentColor"/></svg></span></a><div class="video__body">
	<?php
	text_element( 'h3', $item['title'] );
	text_element( 'p', $item['description'] ?? '' );
	?>
</div></li><?php endforeach; ?></ul>
<?php
if ( has_text( $args['cta']['label'] ?? '' ) && \Iotnest\Core\destination_url( $args['cta'] ) ) :
	?>
	<div class="section-cta"><?php cta( $args['cta'], 'btn btn--outline', 'arrow-sm' ); ?></div><?php endif; ?></div></section>
