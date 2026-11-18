<?php
/** Disposable PHP server: same front-controller contract as the production web server. */
$root = realpath( getenv( 'IOTNEST_TEST_WP_ROOT' ) ?: '' );
if ( ! $root || ! is_file( $root . '/.iotnest-test' ) ) { http_response_code( 503 ); exit; }
$path = rawurldecode( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
$file = realpath( $root . $path );
// Pool de assets do ensaio de rollout. Restrito ao irmão da instalação descartável.
$pool_marker = $root . '/.iotnest-asset-pool';
$release_asset = false;
if ( is_file( $pool_marker ) ) {
    $pool = realpath( trim( file_get_contents( $pool_marker ) ) );
    if ( $pool && str_starts_with( $pool, dirname( $root ) . '/release-qa/' ) ) {
        if ( preg_match( '~\A/wp-content/(themes/iotnest|plugins/iotnest-core)/assets/dist/([a-z0-9-]+\.[a-f0-9]{12}\.(css|js))\z~', $path, $match ) ) {
            $pooled = $pool . '/' . $match[1] . '/' . $match[2];
            if ( is_file( $pooled ) ) { header( 'Content-Type: ' . ( 'css' === $match[3] ? 'text/css' : 'application/javascript' ) ); readfile( $pooled ); exit; }
        }
        $release_asset = $file && str_starts_with( $file, dirname( $pool ) . '/releases/' ) && preg_match( '~\A/wp-content/(themes/iotnest|plugins/iotnest-core)/(assets|resources)/[a-zA-Z0-9_./-]+\.(woff2|svg|webp|png|jpg|mp4|webm)\z~', $path );
    }
}

// O servidor embutido do PHP não implementa seek por Range; reproduzir o servidor web para mídia local.
if ( $file && ( str_starts_with( $file, $root . '/' ) || $release_asset ) && is_file( $file ) && in_array( pathinfo( $file, PATHINFO_EXTENSION ), array( 'mp4', 'webm' ), true ) ) {
    $size = filesize( $file ); $start = 0; $end = $size - 1;
    header( 'Content-Type: video/' . pathinfo( $file, PATHINFO_EXTENSION ) );
    header( 'Accept-Ranges: bytes' );
    if ( isset( $_SERVER['HTTP_RANGE'] ) ) {
        if ( ! preg_match( '/\Abytes=(\d*)-(\d*)\z/', $_SERVER['HTTP_RANGE'], $matches ) ) { http_response_code( 416 ); exit; }
        $start = '' !== $matches[1] ? (int) $matches[1] : max( 0, $size - (int) $matches[2] );
        $end = '' !== $matches[1] && '' !== $matches[2] ? min( (int) $matches[2], $size - 1 ) : $size - 1;
        if ( $start > $end || $start >= $size ) { http_response_code( 416 ); header( 'Content-Range: bytes */' . $size ); exit; }
        http_response_code( 206 ); header( "Content-Range: bytes $start-$end/$size" );
    }
    header( 'Content-Length: ' . ( $end - $start + 1 ) );
    if ( 'HEAD' !== $_SERVER['REQUEST_METHOD'] ) {
        $stream = fopen( $file, 'rb' ); fseek( $stream, $start ); $remaining = $end - $start + 1;
        while ( $remaining > 0 && ! feof( $stream ) ) { $chunk = fread( $stream, min( 65536, $remaining ) ); echo $chunk; $remaining -= strlen( $chunk ); }
        fclose( $stream );
    }
    exit;
}
if ( $file && ( str_starts_with( $file, $root . '/' ) || $release_asset ) && ( is_file( $file ) || ( is_dir( $file ) && is_file( $file . '/index.php' ) ) ) ) return false;
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
require $root . '/index.php';
