<?php
/** Public route fixtures live only in the guarded disposable installation. */
require __DIR__ . '/bootstrap.php';
wp_set_current_user( get_user_by( 'login', 'iotnest-qa' )->ID );
$action = $argv[1] ?? '';
$patch = json_decode( $argv[2] ?? '{}', true );
$state = get_option( 'iotnest_qa_publication', array() );
if ( 'create' === $action ) {
    if ( $state ) throw new RuntimeException( 'Fixture exists.' );
    $seed = json_decode( file_get_contents( WP_PLUGIN_DIR . '/iotnest-core/resources/seed/institutional.json' ), true );
    $state = array( 'options' => array(), 'mods' => get_theme_mods(), 'ids' => array(), 'menus' => array(), 'posts' => array(), 'legal' => array() );
    foreach ( array( 'page_on_front', 'page_for_posts', 'show_on_front', 'iotnest_settings', 'posts_per_page', 'permalink_structure' ) as $key ) $state['options'][$key] = get_option( $key, null );
    $home = wp_insert_post( array( 'post_type' => 'page', 'post_title' => 'QA Home', 'post_status' => 'publish', 'post_name' => 'qa-home' ) ); $state['ids'][] = $home;
    foreach ( array( 'cases', 'blog', 'contact' ) as $key ) update_post_meta( $home, '_iotnest_home_' . $key, $seed['sections'][$key] );
    update_post_meta( $home, '_iotnest_home_order', array( 'cases', 'blog', 'contact' ) );
    $blog = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'blog', 'post_title' => $seed['blog_page']['title'], 'post_excerpt' => $seed['blog_page']['description'] ) ); $state['ids'][] = $blog;
    update_option( 'show_on_front', 'page' ); update_option( 'page_on_front', $home ); update_option( 'page_for_posts', $blog ); update_option( 'posts_per_page', 2 );
    update_option( 'iotnest_settings', Iotnest\Core\normalize( $seed['settings'], Iotnest\Core\settings_schema() ) );
    foreach ( array( 'post', 'iot_case' ) as $type ) {
        foreach ( array( 'draft', 'pending', 'future', 'private', 'password' ) as $status ) {
            $id = wp_insert_post( array( 'post_type' => $type, 'post_status' => 'password' === $status ? 'publish' : $status, 'post_title' => "QA $type $status", 'post_content' => 'Conteúdo editorial confidencial de QA.', 'post_password' => 'password' === $status ? 'private' : '', 'post_date' => 'future' === $status ? gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS ) : current_time( 'mysql' ) ) );
            $state['ids'][] = $id; $state['posts'][$type][$status] = $id;
        }
    }
    foreach ( $seed['legal'] as $legal ) {
        $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $legal['key'], 'post_title' => $legal['title'], 'post_content' => $legal['content'], 'post_excerpt' => $legal['description'], 'meta_input' => array( '_wp_page_template' => 'page-templates/legal.php' ) ) );
        $state['ids'][] = $id; $state['legal'][$legal['key']] = $id;
    }
    $menu = wp_create_nav_menu( 'QA publications' ); $state['menus'][] = $menu;
    foreach ( array( 'cases' => 'Cases', 'blog' => 'Blog' ) as $key => $label ) {
        $id = wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => $label, 'menu-item-url' => home_url( '/' ), 'menu-item-status' => 'publish' ) );
        update_post_meta( $id, '_iotnest_menu_target', $key );
    }
    set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu ) );
    Iotnest\Core\configure_routes();
    update_option( 'iotnest_qa_publication', $state );
} elseif ( 'status' === $action ) {
    foreach ( $patch as $type => $entries ) foreach ( $entries as $key => $data ) { $id = $state['posts'][$type][$key]; wp_update_post( array_merge( $data, array( 'ID' => $id ) ) ); }
} elseif ( 'many' === $action ) {
    foreach ( array( 'post', 'iot_case' ) as $type ) for ( $i = 0; $i < 5; $i++ ) {
        $id = wp_insert_post( array( 'post_type' => $type, 'post_status' => 'publish', 'post_title' => "QA public $type $i", 'post_content' => '<h2>Conteúdo real de teste</h2><p>' . str_repeat( 'Decisões de produção. ', 100 ) . '</p>', 'post_excerpt' => 'Resumo público', 'post_name' => "qa-$type-$i" ) );
        $state['posts'][$type]['many' . $i] = $id; $state['ids'][] = $id;
    }
    update_option( 'iotnest_qa_publication', $state );
 } elseif ( 'visual' === $action ) {
    foreach ( $state['posts'] as $entries ) foreach ( $entries as $id ) wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) );
    update_option( 'posts_per_page', 12 );
    $state['visual'] = array();
    foreach ( array( 'post' => array( 'artigo-1', 'artigo-2', 'artigo-3', 'artigo-4' ), 'iot_case' => array( 'case-plasticos-nordeste', 'case-autoparts-brasil', 'case-embalagens-premium' ) ) as $type => $files ) {
        foreach ( $files as $index => $name ) {
            $doc = new DOMDocument();
            $doc->loadHTML( file_get_contents( __DIR__ . '/../../../iotnest-site/' . $name . '.html' ), LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET );
            $xpath = new DOMXPath( $doc );
            $node = static fn( $class ) => $xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")]' )->item(0);
            $text = static fn( $class ) => trim( $node( $class )->textContent );
            $body = ''; foreach ( $node( 'article__body' )->childNodes as $child ) if ( XML_COMMENT_NODE !== $child->nodeType ) $body .= $doc->saveHTML( $child );
            $title = $xpath->query( '//h1' )->item(0)->textContent;
            $id = wp_insert_post( wp_slash( array( 'post_type' => $type, 'post_status' => 'publish', 'post_title' => $title, 'post_excerpt' => $text( 'article__lead' ), 'post_content' => $body, 'post_name' => 'qa-' . $name, 'post_date' => gmdate( 'Y-m-d H:i:s', time() - 3600 * ( $index + 1 ) ) ) ) );
            $taxonomy = 'post' === $type ? 'category' : 'iot_case_sector';
            $term = wp_insert_term( $text( 'tag' ), $taxonomy );
            $term_id = is_wp_error( $term ) ? (int) $term->get_error_data( 'term_exists' ) : $term['term_id'];
            wp_set_object_terms( $id, array( $term_id ), $taxonomy );
            if ( ! is_wp_error( $term ) ) $state['terms'][$taxonomy][] = $term_id;
            if ( 'iot_case' === $type ) {
                $dds = $xpath->query( '//aside[contains(@class,"article__aside")]//dd' );
                update_post_meta( $id, '_iotnest_case', array( 'company' => $dds->item(0)->textContent, 'solution' => $dds->item(2)->textContent, 'cta' => array( 'label' => 'Quero resultados assim', 'kind' => 'section', 'section' => 'contact' ) ) );
            }
            $state['ids'][] = $id; $state['posts'][$type]['visual' . $index] = $id;
            $state['visual'][$name] = array( 'url' => get_permalink( $id ), 'title' => $title, 'reading' => Iotnest\Theme\entry_data( get_post( $id ) )['reading'] );
        }
    }
    update_option( 'iotnest_qa_publication', $state );
} elseif ( 'empty' === $action ) {
    foreach ( $state['posts'] as $entries ) foreach ( $entries as $id ) wp_update_post( array( 'ID' => $id, 'post_status' => 'draft', 'post_password' => '' ) );
} elseif ( 'delete' === $action ) {
    foreach ( $state['terms'] ?? array() as $taxonomy => $terms ) foreach ( $terms as $id ) wp_delete_term( $id, $taxonomy );
    foreach ( $state['menus'] as $id ) wp_delete_nav_menu( $id );
    foreach ( $state['ids'] as $id ) wp_delete_post( $id, true );
    foreach ( $state['options'] as $key => $value ) { if ( null === $value ) delete_option( $key ); else update_option( $key, $value ); }
    $GLOBALS['wp_rewrite']->set_permalink_structure( $state['options']['permalink_structure'] ); flush_rewrite_rules( false );
    remove_theme_mods(); foreach ( $state['mods'] ?: array() as $key => $value ) set_theme_mod( $key, $value );
    delete_option( 'iotnest_qa_publication' );
} else { throw new RuntimeException( 'Unknown fixture action' ); }
echo wp_json_encode( array( 'posts' => $state['posts'], 'legal' => $state['legal'], 'visual' => $state['visual'] ?? array() ) );
