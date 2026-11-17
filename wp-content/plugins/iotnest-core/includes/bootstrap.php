<?php
/**
 * Bootstrap do plugin; ativação/desativação não modifica conteúdo ou configurações.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/** Prepara o domínio do plugin. Módulos editoriais começam na etapa 02. */
function bootstrap() {
	load_plugin_textdomain( 'iotnest-core', false, dirname( plugin_basename( IOTNEST_CORE_FILE ) ) . '/languages' );
}
