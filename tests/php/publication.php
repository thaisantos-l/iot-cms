<?php
/** T05–T10/T16/T20: public queries, state transitions and legal rendering. */
require __DIR__ . '/bootstrap.php';
wp_set_current_user( 1 );
$ids = array();
$old_structure = get_option( 'permalink_structure' );
$old_query = $GLOBALS['wp_query'];
$old_main = $GLOBALS['wp_the_query'];
try {
    foreach ( array( 'post', 'iot_case' ) as $type ) {
        iotnest_check( ! Iotnest\Core\has_public_content( $type ), "$type starts empty" );
        $local = array();
        foreach ( array( 'draft', 'pending', 'future', 'private', 'trash', 'publish' ) as $status ) {
            $id = wp_insert_post( array( 'post_type' => $type, 'post_title' => "QA private $status", 'post_status' => $status, 'post_content' => 'Hidden editorial body.', 'post_date' => 'future' === $status ? gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS ) : current_time( 'mysql' ), 'post_password' => 'publish' === $status ? 'qa-secret' : '' ) );
            $ids[] = $id; $local[$status] = $id;
        }
        iotnest_check( ! Iotnest\Core\has_public_content( $type ), "$type excludes all nonpublic states even for administrator" );
        wp_update_post( array( 'ID' => $local['draft'], 'post_status' => 'publish', 'post_title' => 'QA public first' ) );
        iotnest_check( Iotnest\Core\has_public_content( $type ), "$type first publication becomes available in same request" );
        $result = Iotnest\Core\public_query( $type );
        iotnest_check( array_column( $result->posts, 'ID' ) === array( $local['draft'] ), "$type has exactly one public item without duplication" );
        iotnest_check( Iotnest\Core\public_query( $type ) === $result, "$type reuses query in same request" );
        for ( $i = 0; $i < 4; $i++ ) { $id = wp_insert_post( array( 'post_type' => $type, 'post_title' => 'QA public many ' . $i, 'post_status' => 'publish', 'post_content' => str_repeat( 'conteúdo ', 441 ) ) ); $ids[] = $id; $local['many' . $i] = $id; }
        $a = Iotnest\Core\public_query( $type, array( 'posts_per_page' => 2, 'paged' => 1, 'no_found_rows' => false ) );
        $b = Iotnest\Core\public_query( $type, array( 'posts_per_page' => 2, 'paged' => 2, 'no_found_rows' => false ) );
        iotnest_check( (int) $a->found_posts === 5 && (int) $a->max_num_pages === 3 && ! array_intersect( array_column( $a->posts, 'ID' ), array_column( $b->posts, 'ID' ) ), "$type pagination has no duplicates and counts only public records" );
        iotnest_check( $a->posts[0]->ID === $local['many3'], "$type date order uses descending ID as tie-break" );
        if ( 'post' === $type ) {
            $term = wp_insert_term( 'QA category filter', 'category' );
            wp_set_post_categories( $local['many3'], array( $term['term_id'] ) );
            iotnest_check( Iotnest\Core\has_public_content( 'post', $term['term_id'] ) && count( Iotnest\Core\public_query( 'post', array( 'cat' => $term['term_id'] ) )->posts ) === 1, 'category filter uses the same public policy in count and cards' );
            wp_delete_term( $term['term_id'], 'category' );
        }
        $related = Iotnest\Core\public_query( $type, array( 'post__not_in' => array( $local['many3'] ) ) )->posts;
        iotnest_check( ! in_array( $local['many3'], array_column( $related, 'ID' ), true ), "$type related excludes current record" );
        iotnest_check( Iotnest\Theme\entry_data( get_post( $local['many3'] ) )['reading'] === '3 min de leitura', 'reading time is calculated from editorial text' );
        foreach ( array_merge( array( $local['draft'] ), array_values( array_filter( $local, static fn( $k ) => str_starts_with( $k, 'many' ), ARRAY_FILTER_USE_KEY ) ) ) as $id ) { wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) ); }
        iotnest_check( ! Iotnest\Core\has_public_content( $type ), "$type removing last public record immediately hides collection" );
        wp_publish_post( $local['future'] );
        iotnest_check( Iotnest\Core\has_public_content( $type ), "$type scheduled item appears only after actual publish transition" );
        wp_update_post( array( 'ID' => $local['future'], 'post_password' => 'restricted' ) );
        iotnest_check( ! Iotnest\Core\has_public_content( $type ), "$type password protection immediately removes last public record" );
    }
    $GLOBALS['wp_query'] = new WP_Query();
    $GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
    $GLOBALS['wp_query']->query( array( 's' => 'QA private', 'post_status' => 'any', 'posts_per_page' => 100 ) );
    iotnest_check( ! $GLOBALS['wp_query']->posts, 'main public search never exposes protected/nonpublished records to administrator' );
    $document = Iotnest\Theme\legal_document( '<section id="same"><h2><span class="legal__n">01</span>Primeiro &amp; segundo</h2></section><h2 id="same">Outro título</h2><h2>Último</h2>' );
    preg_match_all( '/id="([^"]+)"/', $document['html'], $matches );
    iotnest_check( count( $matches[1] ) === count( array_unique( $matches[1] ) ) && count( $document['toc'] ) === 3, 'legal headings have unique IDs and a real server-rendered table of contents' );
    iotnest_check( $document['toc'][0]['label'] === 'Primeiro & segundo', 'legal table of contents excludes decorative numbering' );
    iotnest_check( ! Iotnest\Core\legacy_destination( 'artigo-1.html' ) && ! Iotnest\Core\legacy_destination( 'case-plasticos-nordeste.html' ), 'example URLs never redirect to nonexistent editorial equivalents' );
    $conflict = wp_insert_post( array( 'post_type' => 'page', 'post_name' => 'cases', 'post_title' => 'Existing page', 'post_status' => 'publish' ) ); $ids[] = $conflict;
    iotnest_check( is_wp_error( Iotnest\Core\configure_routes() ) && get_post_field( 'post_name', $conflict ) === 'cases', 'setup refuses slug conflict without renaming existing page' );
    wp_delete_post( $conflict, true );
    iotnest_check( true === Iotnest\Core\configure_routes(), 'explicit setup configures valid rewrite rules' );
    wp_set_current_user( 0 );
    iotnest_check( is_wp_error( Iotnest\Core\configure_routes() ), 'route setup requires administrative capability' );
} finally {
    $GLOBALS['wp_query'] = $old_query;
    $GLOBALS['wp_the_query'] = $old_main;
    foreach ( $ids as $id ) wp_delete_post( $id, true );
    $GLOBALS['wp_rewrite']->set_permalink_structure( $old_structure );
    flush_rewrite_rules( false );
}
