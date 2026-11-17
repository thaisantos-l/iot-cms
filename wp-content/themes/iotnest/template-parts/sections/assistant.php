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
<div class="dada-full__frame" data-dada-frame><?php background_video( $args, 'dada__bg' ); ?><div class="dada-full__veil" aria-hidden="true"></div></div></section>
