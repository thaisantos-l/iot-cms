<?php
/**
 * Apresentação compartilhada do conteúdo nativo.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="legal" tabindex="-1"><div class="container container--narrow"><?php breadcrumbs( __( 'Página não encontrada', 'iotnest' ) ); ?><header class="legal__head"><p class="eyebrow">404</p><h1><?php esc_html_e( 'Página não encontrada', 'iotnest' ); ?></h1></header><?php get_template_part( 'template-parts/components/empty', null, array( 'message' => __( 'O endereço não existe ou não está disponível. Use a busca ou volte ao início.', 'iotnest' ) ) ); ?><a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Voltar ao início', 'iotnest' ); ?></a></div></main><?php get_footer(); ?>
