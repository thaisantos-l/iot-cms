<?php
/** Syntax checks restricted to project PHP, never WordPress core. */
$roots = array( 'wp-content/themes/iotnest', 'wp-content/plugins/iotnest-core', 'scripts', 'tests/php', 'ops' );
$failed = false;
foreach ( $roots as $root ) {
    if ( ! is_dir( $root ) ) {
        continue;
    }
    $files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
    foreach ( $files as $file ) {
        if ( 'php' !== $file->getExtension() ) {
            continue;
        }
        passthru( escapeshellarg( PHP_BINARY ) . ' -l ' . escapeshellarg( $file->getPathname() ), $status );
        $failed = $failed || 0 !== $status;
    }
}
exit( $failed ? 1 : 0 );
