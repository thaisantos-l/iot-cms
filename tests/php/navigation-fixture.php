<?php
/** Guarded fixtures used only for navigation and visual comparisons. */
require_once __DIR__ . '/bootstrap.php';
$admin = get_user_by( 'login', 'iotnest-qa' );
wp_set_current_user( $admin->ID );
$action = $argv[1] ?? '';
$state = get_option( 'iotnest_qa_navigation', array() );
if ( 'create' === $action ) {
    if ( $state ) throw new RuntimeException( 'Fixture already exists.' );
    $state = array( 'posts' => array(), 'menus' => array(), 'options' => array(), 'mods' => get_theme_mods() );
    foreach ( array( 'show_on_front', 'page_on_front', 'page_for_posts', 'iotnest_settings' ) as $key ) $state['options'][$key] = get_option( $key, null );
    $home_id = wp_insert_post( array( 'post_type' => 'page', 'post_title' => 'Home QA', 'post_status' => 'publish', 'post_author' => $admin->ID ) );
    $state['posts'][] = $home_id;
    $state['home'] = $home_id;
    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $home_id );
    $upload = wp_upload_bits( 'qa-navigation.png', null, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScLttAAAAABJRU5ErkJggg==' ) );
    $media = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => 'QA image' ), $upload['file'] );
    $state['media'] = $media;
    foreach ( Iotnest\Core\section_keys() as $key ) {
        $data = array( 'enabled' => true, 'title' => 'QA ' . $key );
        if ( 'ai' === $key ) $data['items'] = array( array( 'id' => 'ai', 'text' => 'QA', 'image' => $media ) );
        if ( 'assistant' === $key ) $data['poster'] = $media;
        if ( 'faq' === $key ) $data['items'] = array( array( 'id' => 'faq', 'question' => 'QA', 'answer' => 'QA' ) );
        if ( 'how_it_works' === $key ) $data['items'] = array( array( 'id' => 'step', 'title' => 'QA', 'text' => 'QA' ) );
        update_post_meta( $home_id, '_iotnest_home_' . $key, $data );
    }
    update_post_meta( $home_id, '_iotnest_home_order', Iotnest\Core\section_keys() );
    $state['case'] = wp_insert_post( array( 'post_type' => 'iot_case', 'post_title' => 'Case QA', 'post_status' => 'publish' ) );
    $state['posts'][] = $state['case'];
    update_option( 'iotnest_settings', array(
        'platform' => 'https://telemetria.iotnest.ai', 'platform_label' => 'Login',
        'commercial_cta' => array( 'label' => 'Entenda como funciona', 'kind' => 'section', 'section' => 'how_it_works' ),
        'footer_description' => 'A plataforma de dados e inteligência artificial que transforma injetoras plásticas em máquinas de alta performance.',
        'whatsapp' => 'https://wa.me/5511950323958',
        'copyright' => '© 2024 iotnest.ai Tecnologia Industrial. Todos os direitos reservados.',
        'credit_label' => 'Site desenvolvido por', 'credit_name' => 'FaiskaLab',
        'socials' => array( array( 'id' => 'linkedin', 'label' => 'LinkedIn', 'icon' => 'linkedin', 'url' => 'https://br.linkedin.com/company/iot-nest-tecnologia' ) ),
    ) );
    function qa_menu_item( $menu, $label, $target, $parent = 0, $page_id = 0 ) {
        $args = array( 'menu-item-title' => $label, 'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent, 'menu-item-type' => 'custom', 'menu-item-url' => '#' );
        if ( $page_id ) $args = array_merge( $args, array( 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $page_id ) );
        $item = wp_update_nav_menu_item( $menu, 0, $args );
        update_post_meta( $item, '_iotnest_menu_target', $target );
        return $item;
    }
    $primary = wp_create_nav_menu( 'QA primary' );
    $footer = wp_create_nav_menu( 'QA footer' );
    $legal = wp_create_nav_menu( 'QA legal' );
    $state['menus'] = array( $primary, $footer, $legal );
    set_theme_mod( 'nav_menu_locations', array( 'primary' => $primary, 'footer' => $footer, 'legal' => $legal ) );
    $group = qa_menu_item( $primary, 'Soluções', 'group' );
    foreach ( array( 'solution' => 'Alertas', 'ai' => 'Tomada de decisões', 'assistant' => 'Dadá IA', 'defects' => 'Apontamentos', 'energy' => 'Custo de energia', 'reports' => 'Relatórios' ) as $key => $label ) qa_menu_item( $primary, $label, 'section:' . $key, $group );
    foreach ( array( 'calculator' => 'Calculadora', 'cases' => 'Cases', 'how_it_works' => 'Como funciona', 'about' => 'Sobre nós', 'faq' => 'FAQ', 'contact' => 'Contato' ) as $key => $label ) qa_menu_item( $primary, $label, 'cases' === $key ? 'cases' : 'section:' . $key );
    foreach ( array( 'solution' => 'Soluções', 'how_it_works' => 'Como Funciona', 'cases' => 'Cases', 'about' => 'Sobre Nós', 'faq' => 'FAQ', 'contact' => 'Contato' ) as $key => $label ) qa_menu_item( $footer, $label, 'cases' === $key ? 'cases' : 'section:' . $key );
    foreach ( array( 'Privacidade', 'Cookies', 'Termos de Uso' ) as $label ) {
        $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $label, 'post_content' => 'QA legal' ) );
        $state['posts'][] = $id;
        qa_menu_item( $legal, $label, '', 0, $id );
    }
    update_option( 'iotnest_qa_navigation', $state );
    echo wp_json_encode( $state );
} elseif ( 'mutate' === $action ) {
    $patch = json_decode( $argv[2], true, 16, JSON_THROW_ON_ERROR );
    if ( isset( $patch['case_status'] ) ) wp_update_post( array( 'ID' => $state['case'], 'post_status' => $patch['case_status'] ) );
    foreach ( $patch['sections'] ?? array() as $key => $data ) update_post_meta( $state['home'], '_iotnest_home_' . $key, $data );
    if ( isset( $patch['settings'] ) ) update_option( 'iotnest_settings', array_merge( get_option( 'iotnest_settings' ), $patch['settings'] ) );
    foreach ( $patch['colors'] ?? array() as $key => $value ) set_theme_mod( 'iotnest_color_' . $key, $value );
    if ( isset( $patch['logo_shape'] ) ) {
        [ $width, $height ] = $patch['logo_shape'];
        $chunk = static fn( $name, $data ) => pack( 'N', strlen( $data ) ) . $name . $data . pack( 'N', crc32( $name . $data ) );
        $png = "\x89PNG\r\n\x1a\n" . $chunk( 'IHDR', pack( 'NNCCCCC', $width, $height, 8, 2, 0, 0, 0 ) );
        $png .= $chunk( 'IDAT', gzcompress( str_repeat( "\x00" . str_repeat( "\x02\xaf\xef", $width ), $height ) ) ) . $chunk( 'IEND', '' );
        $upload = wp_upload_bits( 'qa-logo-' . $width . '.png', null, $png );
        $id = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => 'QA logo shape' ), $upload['file'] );
        wp_update_attachment_metadata( $id, array( 'width' => $width, 'height' => $height, 'file' => _wp_relative_upload_path( $upload['file'] ), 'sizes' => array() ) );
        $state['generated_media'][] = $id;
        update_option( 'iotnest_qa_navigation', $state );
        set_theme_mod( 'custom_logo', $id );
        set_theme_mod( 'iotnest_footer_logo', $id );
    }
    if ( ! empty( $patch['logo_missing'] ) ) {
        $id = end( $state['generated_media'] );
        unlink( get_attached_file( $id ) );
    }
    if ( isset( $patch['home_status'] ) ) wp_update_post( array( 'ID' => $state['home'], 'post_status' => $patch['home_status'] ) );
} elseif ( 'delete' === $action && $state ) {
    foreach ( $state['menus'] as $id ) wp_delete_nav_menu( $id );
    foreach ( $state['posts'] as $id ) wp_delete_post( $id, true );
    wp_delete_attachment( $state['media'], true );
    foreach ( $state['generated_media'] ?? array() as $id ) wp_delete_attachment( $id, true );
    foreach ( $state['options'] as $key => $value ) {
        if ( null === $value ) delete_option( $key ); else update_option( $key, $value );
    }
    remove_theme_mods();
    foreach ( $state['mods'] ?: array() as $key => $value ) set_theme_mod( $key, $value );
    delete_option( 'iotnest_qa_navigation' );
} else {
    throw new RuntimeException( 'Unknown fixture action.' );
}
