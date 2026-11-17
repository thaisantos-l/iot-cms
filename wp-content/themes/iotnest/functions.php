<?php
/**
 * Inicialização do tema, sem gravar conteúdo ou opções editoriais.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/inc/setup.php';
require_once __DIR__ . '/inc/assets.php';
require_once __DIR__ . '/inc/presentation.php';
require_once __DIR__ . '/inc/navigation.php';
require_once __DIR__ . '/inc/class-primary-menu.php';
require_once __DIR__ . '/inc/customizer.php';
require_once __DIR__ . '/inc/view-models.php';
require_once __DIR__ . '/inc/template-tags.php';

add_action( 'after_setup_theme', __NAMESPACE__ . '\\setup' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets' );
add_action( 'admin_notices', __NAMESPACE__ . '\\dependency_notice' );
