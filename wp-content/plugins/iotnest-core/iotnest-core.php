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
foreach ( array( 'schema', 'validation', 'post-types', 'visibility', 'meta-boxes', 'settings', 'rest', 'autosave', 'navigation' ) as $module ) {
	require_once __DIR__ . '/includes/' . $module . '.php';
}

add_action( 'init', __NAMESPACE__ . '\\bootstrap' );
add_action( 'init', __NAMESPACE__ . '\\register_content', 5 );
add_action( 'add_meta_boxes', __NAMESPACE__ . '\\add_boxes' );
add_action( 'save_post', __NAMESPACE__ . '\\save_boxes', 20, 2 );
add_action( 'wp_creating_autosave', __NAMESPACE__ . '\\save_preview', 20 );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_assets' );
add_action( 'admin_notices', __NAMESPACE__ . '\\save_notice' );
add_action( 'admin_menu', __NAMESPACE__ . '\\settings_menu' );
add_action( 'admin_init', __NAMESPACE__ . '\\register_settings' );
add_filter( 'use_block_editor_for_post', __NAMESPACE__ . '\\editor_mode', 10, 2 );
