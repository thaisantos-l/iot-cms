<?php
/** Disposable PHP server: same front-controller contract as the production web server. */
$root = realpath( getenv( 'IOTNEST_TEST_WP_ROOT' ) ?: '' );
if ( ! $root || ! is_file( $root . '/.iotnest-test' ) ) { http_response_code( 503 ); exit; }
$path = rawurldecode( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
$file = realpath( $root . $path );
if ( $file && str_starts_with( $file, $root . '/' ) && ( is_file( $file ) || ( is_dir( $file ) && is_file( $file . '/index.php' ) ) ) ) return false;
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
require $root . '/index.php';
