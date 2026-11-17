<?php
/** Install a fresh, private test site; no local CMS configuration is read. */
define( 'WP_INSTALLING', true );
require __DIR__ . '/bootstrap.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
wp_install( 'iotnest QA', 'iotnest-qa', 'qa@example.invalid', false, '', getenv( 'IOTNEST_TEST_ADMIN_PASSWORD' ), 'en_US' );
// Only native installation fixtures in this disposable database are removed.
foreach ( get_posts( array( 'post_type' => 'any', 'post_status' => 'any', 'numberposts' => -1 ) ) as $post ) {
    wp_delete_post( $post->ID, true );
}
update_option( 'blog_public', '0' );
echo 'Runtime HTTP/CLI: PHP ' . PHP_VERSION . '; WordPress ' . $wp_version . "\n";
