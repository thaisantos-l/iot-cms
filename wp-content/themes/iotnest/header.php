<?php
/**
 * Cabeçalho compartilhado, com estrutura da referência e menus nativos.
 *
 * @package Iotnest
 */

defined( 'ABSPATH' ) || exit;
$iotnest_settings = Iotnest\Theme\global_settings();
$iotnest_menu     = wp_nav_menu(
	array(
		'theme_location' => 'primary',
		'container'      => false,
		'items_wrap'     => '%3$s',
		'fallback_cb'    => false,
		'echo'           => false,
		'walker'         => new Iotnest\Theme\Primary_Menu(),
	)
);
ob_start();
Iotnest\Theme\cta(
	array(
		'kind'  => 'url',
		'url'   => $iotnest_settings['platform'] ?? '',
		'label' => $iotnest_settings['platform_label'] ?? '',
	),
	'link-login'
);
Iotnest\Theme\cta( $iotnest_settings['commercial_cta'] ?? array(), 'btn btn--sm' );
$iotnest_actions = ob_get_clean();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Ir para o conteúdo</a>
<header class="site-header">
	<div class="container container--wide site-header__inner">
		<?php Iotnest\Theme\logo(); ?>
		<?php if ( $iotnest_menu || $iotnest_actions ) : ?>
		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu" aria-label="Abrir menu"><span></span><span></span><span></span></button>
		<div class="site-header__menu" id="menu">
			<?php if ( $iotnest_menu ) : ?>
			<nav class="nav" aria-label="Principal"><?php echo $iotnest_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup escapado pelo walker e WordPress. ?></nav>
			<?php endif; ?>
			<?php
			if ( $iotnest_actions ) :
				?>
				<div class="site-header__actions"><?php echo $iotnest_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CTAs escapados pela função comum. ?></div><?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</header>
