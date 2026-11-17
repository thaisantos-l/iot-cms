<?php
/**
 * Plugin Name: iotnest Core
 * Description: Conteúdo, campos nativos e funcionalidades do iotnest, independentes do tema.
 * Version: 0.1.0
 * Requires at least: 7.1
 * Requires PHP: 8.2
 * Text Domain: iotnest-core
 * License: GPL-2.0-or-later
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

define( 'IOTNEST_CORE_VERSION', '0.1.0' );
define( 'IOTNEST_CORE_FILE', __FILE__ );

require_once __DIR__ . '/includes/bootstrap.php';

add_action( 'init', __NAMESPACE__ . '\\bootstrap' );
