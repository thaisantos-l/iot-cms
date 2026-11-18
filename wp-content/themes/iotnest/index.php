<?php
/**
 * Apresentação compartilhada do conteúdo nativo.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
get_template_part( 'template-parts/content/' . ( is_singular() ? 'singular' : 'listing' ) );
