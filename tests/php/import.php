<?php
/** T02–T05/T15: real import, editorial preservation and server-rendered sections. */
require __DIR__ . '/bootstrap.php';
wp_set_current_user( get_user_by( 'login', 'iotnest-qa' )->ID );
$options = array();
foreach ( array( 'permalink_structure', 'show_on_front', 'page_on_front', 'page_for_posts', 'iotnest_settings' ) as $key ) $options[$key] = get_option( $key, null );
$mods = get_theme_mods();
$extra = array();
try {
    $report = Iotnest\Core\import_institutional();
    iotnest_check( ! is_wp_error( $report ), 'institutional import succeeds: ' . ( is_wp_error( $report ) ? $report->get_error_message() : 'schema valid' ) );
    iotnest_check( count( $report['created'] ) === 39, '30 media, five draft pages, settings and three native menus created' );
    $home = $report['pages']['home'];
    iotnest_check( str_contains( get_post_meta( $home, '_yoast_wpseo_title', true ), 'iotnest' ), 'new institutional page receives the supplied SEO title' );
    foreach ( $report['pages'] as $id ) iotnest_check( 'draft' === get_post_status( $id ), 'imported page remains a draft for review' );
    iotnest_check( ! get_option( 'page_on_front' ) && ! get_option( 'page_for_posts' ), 'import does not configure or publish home/blog implicitly' );
    iotnest_check( ! get_posts( array( 'post_type' => array( 'post', 'iot_case' ), 'post_status' => 'any' ) ), 'import creates zero articles and zero cases' );
    $original = array();
    foreach ( Iotnest\Core\meta_schemas( 'page' ) as $key => $field ) $original[$key] = get_post_meta( $home, $key, true );
    $view = Iotnest\Theme\home_sections( $home );
    iotnest_check( count( $view ) === 17 && ! isset( $view['cases'], $view['blog'] ), 'seventeen institutional sections are available; collections remain absent' );
    ob_start();
    foreach ( $view as $key => $data ) get_template_part( 'template-parts/sections/' . str_replace( '_', '-', $key ), null, $data );
    $html = ob_get_clean();
    iotnest_check( str_contains( $html, 'Captura e processamento de dados' ) && str_contains( $html, 'Plataforma e aplicativo' ), 'every step formerly embedded in JS is rendered by PHP' );
    iotnest_check( ! preg_match( '/(?:src|href)="(?:|#)"/', $html ), 'no empty media URL or placeholder link in institutional HTML' );
    iotnest_check( ! str_contains( $html, 'iotnest-site' ) && ! str_contains( $html, 'assets/img/' ), 'runtime HTML does not depend on the reference directory' );
    iotnest_check( ! str_contains( $html, 'data-contact-form' ), 'no unusable contact submission before delivery service exists' );
    foreach ( $view as $key => $data ) {
        $meta = '_iotnest_home_' . $key;
        $cleared = $original[$meta];
        $cleared['features' === $key ? 'label' : 'title'] = '<p> </p>';
        update_post_meta( $home, $meta, $cleared );
        iotnest_check( ! isset( Iotnest\Theme\home_sections( $home )[$key] ), "clearing $key removes the section before rendering" );
        update_post_meta( $home, $meta, $original[$meta] );
    }
    foreach ( array( 'features', 'ai', 'mes', 'how_it_works', 'testimonials', 'videos', 'faq' ) as $key ) {
        $meta = '_iotnest_home_' . $key;
        foreach ( array( 0, 1, 2 ) as $count ) {
            $data = $original[$meta];
            $data['enabled'] = true;
            $data['items'] = array_slice( $data['items'], 0, $count );
            update_post_meta( $home, $meta, $data );
            $current = Iotnest\Theme\home_sections( $home );
            iotnest_check( 0 === $count ? ! isset( $current[$key] ) : count( $current[$key]['items'] ) === min( $count, count( $original[$meta]['items'] ) ), "$key handles $count requested rows without duplication" );
        }
        update_post_meta( $home, $meta, $original[$meta] );
    }
    $comparison = $original['_iotnest_home_comparison'];
    $comparison['columns'] = array_reverse( $comparison['columns'] );
    update_post_meta( $home, '_iotnest_home_comparison', $comparison );
    $prepared = Iotnest\Theme\home_sections( $home )['comparison'];
    ob_start(); get_template_part( 'template-parts/sections/comparison', null, $prepared ); $table = ob_get_clean();
    $expected_labels = array_column( $comparison['columns'], 'label' );
    iotnest_check( strpos( $table, $expected_labels[0] ) < strpos( $table, $expected_labels[2] ), 'comparison follows editorial column order while cells retain column IDs' );
    update_post_meta( $home, '_iotnest_home_comparison', $original['_iotnest_home_comparison'] );
    $about = $original['_iotnest_home_about']; $about['number'] = 0;
    update_post_meta( $home, '_iotnest_home_about', $about );
    ob_start(); get_template_part( 'template-parts/sections/about', null, Iotnest\Theme\home_sections( $home )['about'] ); $html = ob_get_clean();
    iotnest_check( str_contains( $html, 'data-count="0"' ) && str_contains( $html, '<span data-count-value>0</span>' ), 'zero remains a valid visible numeric indicator' );
    update_post_meta( $home, '_iotnest_home_about', $original['_iotnest_home_about'] );
    $solution = $original['_iotnest_home_solution'];
    $solution['image'] = 999999; $solution['description'] = ''; $solution['bullets'] = array(); $solution['cta'] = array( 'label' => 'Incomplete', 'kind' => 'url', 'url' => '' );
    update_post_meta( $home, '_iotnest_home_solution', $solution );
    ob_start(); get_template_part( 'template-parts/sections/solution', null, Iotnest\Theme\home_sections( $home )['solution'] ); $html = ob_get_clean();
    iotnest_check( str_contains( $html, 'split--text-only' ) && ! str_contains( $html, '<figure' ) && ! str_contains( $html, '<ul' ) && ! str_contains( $html, '<a' ), 'missing optional media, list and CTA omit their wrappers' );
    update_post_meta( $home, '_iotnest_home_solution', $original['_iotnest_home_solution'] );
    update_post_meta( $home, '_iotnest_home_order', array_reverse( Iotnest\Core\section_keys() ) );
    iotnest_check( array_key_first( Iotnest\Theme\home_sections( $home ) ) === 'contact', 'editorial reordering changes HTML order with stable anchors' );
    update_post_meta( $home, '_iotnest_home_hero', array( 'enabled' => true, 'title' => '' ) );
    update_option( 'iotnest_settings', array( 'footer_description' => '', 'phone' => 'Edited' ) );
    wp_update_post( array( 'ID' => $report['pages']['privacidade'], 'post_content' => 'Legal revised by editor.' ) );
    update_post_meta( $home, '_yoast_wpseo_title', '' );
    $again = Iotnest\Core\import_institutional();
    iotnest_check( ! is_wp_error( $again ) && ! $again['created'], 'second import creates no duplicate pages, media or menus' );
    iotnest_check( get_post_meta( $home, '_yoast_wpseo_title', true ) === '', 'reimport preserves an intentionally cleared SEO title' );
    iotnest_check( get_post_meta( $home, '_iotnest_home_hero', true )['title'] === '', 'second import preserves intentionally cleared title' );
    iotnest_check( get_post_meta( $home, '_iotnest_home_order', true ) === array_reverse( Iotnest\Core\section_keys() ), 'second import preserves changed order' );
    iotnest_check( get_option( 'iotnest_settings' ) === array( 'footer_description' => '', 'phone' => 'Edited' ), 'second import preserves global changes and clears' );
    iotnest_check( get_post_field( 'post_content', $report['pages']['privacidade'] ) === 'Legal revised by editor.', 'second import preserves legal edits' );
    $registry = get_option( 'iotnest_seed_registry' );
    $removed_key = 'media:assets/img/hero-poster.webp';
    wp_delete_attachment( $registry[$removed_key], true );
    $again = Iotnest\Core\import_institutional();
    iotnest_check( ! is_wp_error( $again ) && in_array( $removed_key, $again['ignored'], true ), 'deleted media is not resurrected by import' );
    $existing = wp_insert_post( array( 'post_type' => 'page', 'post_title' => 'Existing editorial home', 'post_status' => 'publish' ) );
    $extra[] = $existing;
    update_option( 'page_on_front', $existing );
    iotnest_check( is_wp_error( Iotnest\Core\setup_institutional() ) && (int) get_option( 'page_on_front' ) === $existing, 'setup refuses to replace an existing home' );
    update_option( 'page_on_front', 0 );
    $sample = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => __( 'Hello world!' ), 'post_content' => '<p>' . __( 'Welcome to WordPress. This is your first post. Edit or delete it, then start writing!' ) . '</p>' ) );
    $extra[] = $sample;
    $article = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => __( 'Hello world!' ), 'post_content' => 'Legitimate editorial content.' ) );
    $extra[] = $article;
    iotnest_check( Iotnest\Core\native_example_post() === $sample, 'native example detection checks exact title/body and unchanged timestamps' );
    iotnest_check( true === Iotnest\Core\setup_institutional() && 'publish' === get_post_status( $home ), 'separate explicit setup publishes reviewed home' );
    iotnest_check( 'draft' === get_post_status( $sample ) && 'publish' === get_post_status( $article ), 'explicit setup drafts the intact example and preserves legitimate article' );
    iotnest_check( 'draft' === get_post_status( $report['pages']['privacidade'] ), 'setup keeps legal documents as drafts' );
    wp_set_current_user( 0 );
    iotnest_check( is_wp_error( Iotnest\Core\import_institutional() ) && is_wp_error( Iotnest\Core\setup_institutional() ), 'import and setup enforce capabilities independently of nonce' );
} finally {
    foreach ( (array) get_option( 'iotnest_seed_menus', array() ) as $id ) wp_delete_nav_menu( $id );
    foreach ( (array) get_option( 'iotnest_seed_registry', array() ) as $key => $id ) {
        if ( str_starts_with( $key, 'media:' ) ) wp_delete_attachment( $id, true ); else wp_delete_post( $id, true );
    }
    foreach ( $extra as $id ) wp_delete_post( $id, true );
    foreach ( array( 'iotnest_seed_registry', 'iotnest_seed_menus', 'iotnest_seed_settings_done', 'iotnest_seed_lock' ) as $key ) delete_option( $key );
    foreach ( $options as $key => $value ) { if ( null === $value ) delete_option( $key ); else update_option( $key, $value ); }
    remove_theme_mods();
    foreach ( $mods ?: array() as $key => $value ) set_theme_mod( $key, $value );
}
