<?php
/**
 * Importação explícita e conservadora do pacote institucional.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Registra a origem imediatamente; IDs removidos permanecem como tombstones.
 *
 * @param string $key Chave estável.
 * @param int    $id Objeto criado.
 */
function seed_register( $key, $id ) {
	$registry         = (array) get_option( 'iotnest_seed_registry', array() );
	$registry[ $key ] = absint( $id );
	update_option( 'iotnest_seed_registry', $registry, false );
}

/**
 * Reutiliza origem mesmo após falha entre criação e atualização do registro.
 *
 * @param string $key Origem.
 * @return int|null Null permite criação; zero preserva remoção editorial.
 */
function seed_existing( $key ) {
	$registry = (array) get_option( 'iotnest_seed_registry', array() );
	if ( array_key_exists( $key, $registry ) ) {
		return get_post( $registry[ $key ] ) ? absint( $registry[ $key ] ) : 0;
	}
	$ids = get_posts(
		array(
			'post_type'      => array( 'page', 'attachment' ),
			'post_status'    => array_keys( get_post_stati() ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_iotnest_seed_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Importação administrativa explícita.
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Importação administrativa explícita.
		)
	); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Busca administrativa explícita, nunca no front.
	if ( $ids ) {
		seed_register( $key, $ids[0] );
		return (int) $ids[0];
	}
	return null;
}

/**
 * Resolve referências do pacote para attachment IDs do ambiente.
 *
 * @param mixed $value Dados.
 * @param array $media Mapa de origem.
 * @return mixed
 */
function seed_resolve_media( $value, $media ) {
	if ( ! is_array( $value ) ) {
		return $value;
	}
	if ( isset( $value['media'] ) ) {
		return $media[ $value['media'] ] ?? 0;
	}
	foreach ( $value as $key => $child ) {
		$value[ $key ] = seed_resolve_media( $child, $media );
	}
	return $value;
}

/**
 * Cria somente páginas inéditas, com todo o conteúdo inicial na mesma inserção.
 *
 * @param string $key Origem.
 * @param array  $data Dados da página.
 * @param array  $report Relatório por referência.
 * @return int|\WP_Error
 */
function seed_page( $key, $data, &$report ) {
	$id = seed_existing( $key );
	if ( null !== $id ) {
		$report[ $id ? 'reused' : 'ignored' ][] = $key;
		return $id;
	}
	$data['post_type']                       = 'page';
	$data['post_status']                     = 'draft';
	$data['meta_input']['_iotnest_seed_key'] = $key;
	$id                                      = wp_insert_post( wp_slash( $data ), true );
	if ( ! is_wp_error( $id ) ) {
		seed_register( $key, $id );
		$report['created'][] = $key;
	}
	return $id;
}

/**
 * Insere os itens de um menu recém-criado; nunca repara/apaga edições posteriores.
 *
 * @param int   $menu ID do menu.
 * @param array $items Dados do pacote.
 * @param array $pages Mapa de páginas.
 * @param int   $parent_id Pai.
 */
function seed_menu_items( $menu, $items, $pages, $parent_id = 0 ) {
	foreach ( $items as $item ) {
		$args = array(
			'menu-item-title'     => $item['label'],
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => $parent_id,
			'menu-item-type'      => 'custom',
			'menu-item-url'       => '#',
		);
		if ( isset( $item['page'] ) ) {
			if ( empty( $pages[ $item['page'] ] ) ) {
				continue;
			}
			$args = array_merge(
				$args,
				array(
					'menu-item-type'      => 'post_type',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $pages[ $item['page'] ],
				)
			);
		}
		$id = wp_update_nav_menu_item( $menu, 0, $args );
		if ( ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_iotnest_menu_target', $item['target'] ?? '' );
			seed_menu_items( $menu, $item['children'] ?? array(), $pages, $id );
		}
	}
}

/**
 * Importa sem ativação implícita, sem alterar objetos já encontrados e sem posts/cases.
 *
 * @return array|\WP_Error Relatório, IDs e pendências.
 */
function import_institutional() {
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'upload_files' ) ) {
		return new \WP_Error( 'forbidden', 'Sem permissão para importar.' );
	}
	if ( ! add_option( 'iotnest_seed_lock', time(), '', false ) ) {
		return new \WP_Error( 'locked', 'Já existe uma importação em execução. Se ela foi interrompida, peça ao administrador para verificar o processo antes de liberar iotnest_seed_lock.' );
	}
	try {
		$directory = dirname( IOTNEST_CORE_FILE ) . '/resources/seed/';
		// Pacote local versionado e auditado, sem acesso remoto.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$seed = json_decode( file_get_contents( $directory . 'institutional.json' ), true );
		if ( ! is_array( $seed ) || 1 !== ( $seed['version'] ?? 0 ) ) {
			return new \WP_Error( 'invalid_seed', 'Pacote institucional inválido.' );
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$report = array(
			'created' => array(),
			'reused'  => array(),
			'ignored' => array(),
			'pages'   => array(),
		);
		$media  = array();
		foreach ( $seed['media'] as $source => $item ) {
			$key = 'media:' . $source;
			$id  = seed_existing( $key );
			if ( null === $id ) {
				if ( ! preg_match( '/\A[a-z0-9-]+\.(webp|webm|mp4)\z/', $item['file'] ) ) {
					return new \WP_Error( 'invalid_media', 'Nome de mídia inválido.' );
				}
				$file = $directory . 'media/' . $item['file'];
				if ( ! is_readable( $file ) || ! hash_equals( $item['sha256'], hash_file( 'sha256', $file ) ) ) {
					return new \WP_Error( 'media_hash', 'Mídia ausente ou divergente: ' . $item['file'] );
				}
				$temp = wp_tempnam( $item['file'] );
				if ( ! $temp || ! copy( $file, $temp ) ) {
					return new \WP_Error( 'media_copy', 'Não foi possível preparar a mídia.' );
				}
				$id = media_handle_sideload(
					array(
						'name'     => $item['file'],
						'tmp_name' => $temp,
					),
					0,
					pathinfo( $item['file'], PATHINFO_FILENAME ),
					array(
						'meta_input' => array(
							'_iotnest_seed_key'        => $key,
							'_wp_attachment_image_alt' => sanitize_text_field( $item['alt'] ),
						),
					)
				);
				if ( is_wp_error( $id ) ) {
					wp_delete_file( $temp );
					return $id;
				}
				seed_register( $key, $id );
				$report['created'][] = $key;
			} else {
				$report[ $id ? 'reused' : 'ignored' ][] = $key;
			}
			$media[ $source ] = $id;
		}
		$meta = array(
			'_wp_page_template'     => 'page-templates/institutional.php',
			'_iotnest_home_order'   => section_keys(),
			'_yoast_wpseo_title'    => sanitize_text_field( $seed['seo']['home']['title'] ?? '' ),
			'_yoast_wpseo_metadesc' => sanitize_text_field( $seed['seo']['home']['description'] ?? '' ),
		);
		foreach ( $seed['sections'] as $key => $data ) {
			$data  = seed_resolve_media( $data, $media );
			$field = home_schema()[ $key ];
			$valid = rest_validate_value_from_schema( $data, json_schema( $field ) );
			if ( is_wp_error( $valid ) || ! unique_ids( $data, $field ) ) {
				return new \WP_Error( 'seed_schema', 'Dados incompatíveis com o schema: ' . $key );
			}
			$meta[ '_iotnest_home_' . $key ] = normalize( $data, $field );
		}
		$pages = array(
			'home' => seed_page(
				'page:home',
				array(
					'post_title' => 'Início',
					'post_name'  => 'inicio-iotnest',
					'meta_input' => $meta,
				),
				$report
			),
		);
		foreach ( $seed['legal'] as $legal ) {
			$pages[ $legal['key'] ] = seed_page(
				'page:' . $legal['key'],
				array(
					'post_title'   => $legal['title'],
					'post_name'    => $legal['key'],
					'post_content' => wp_kses_post( $legal['content'] ),
					'post_excerpt' => $legal['description'],
					'meta_input'   => array(
						'_wp_page_template'        => 'page-templates/legal.php',
						'_iotnest_review_required' => true,
						'_yoast_wpseo_title'       => sanitize_text_field( $seed['seo'][ $legal['key'] ]['title'] ?? '' ),
						'_yoast_wpseo_metadesc'    => sanitize_text_field( $seed['seo'][ $legal['key'] ]['description'] ?? '' ),
					),
				),
				$report
			);
		}
		$pages['blog'] = seed_page(
			'page:blog',
			array(
				'post_title'   => $seed['blog_page']['title'],
				'post_excerpt' => $seed['blog_page']['description'],
				'post_name'    => 'blog',
				'meta_input'   => array(
					'_yoast_wpseo_title'    => sanitize_text_field( $seed['seo']['blog']['title'] ?? '' ),
					'_yoast_wpseo_metadesc' => sanitize_text_field( $seed['seo']['blog']['description'] ?? '' ),
				),
			),
			$report
		);
		foreach ( $pages as $id ) {
			if ( is_wp_error( $id ) ) {
				return $id;
			}
		}
		// Só a primeira importação inicializa opções ausentes. Opções vazias também são editoriais.
		if ( false === get_option( 'iotnest_seed_settings_done', false ) ) {
			if ( false === get_option( 'iotnest_settings', false ) ) {
				add_option( 'iotnest_settings', normalize( $seed['settings'], settings_schema() ), '', false );
				$report['created'][] = 'settings';
			} else {
				$report['ignored'][] = 'settings';
			}
			add_option( 'iotnest_seed_settings_done', true, '', false );
		}
		$menus = (array) get_option( 'iotnest_seed_menus', array() );
		foreach ( $seed['menus'] as $location => $items ) {
			if ( array_key_exists( $location, $menus ) ) {
				$report['reused'][] = 'menu:' . $location;
				continue;
			}
			$id = wp_create_nav_menu( 'iotnest — ' . $location );
			if ( is_wp_error( $id ) ) {
				return $id;
			}
			$menus[ $location ] = $id;
			update_option( 'iotnest_seed_menus', $menus, false );
			seed_menu_items( $id, $items, $pages );
			$report['created'][] = 'menu:' . $location;
		}
		$report['pages'] = $pages;
		return $report;
	} finally {
		delete_option( 'iotnest_seed_lock' );
	}
}

/**
 * Setup separado: publica a home importada, sem substituir home/blog/menu existente.
 *
 * @return true|\WP_Error
 */
function setup_institutional() {
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'publish_pages' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return new \WP_Error( 'forbidden', 'Sem permissão para configurar.' );
	}
	$home = seed_existing( 'page:home' );
	if ( ! $home || 'trash' === get_post_status( $home ) ) {
		return new \WP_Error( 'missing_home', 'Home importada ausente.' );
	}
	if ( get_option( 'page_on_front' ) && (int) get_option( 'page_on_front' ) !== $home ) {
		return new \WP_Error( 'existing_home', 'Já existe uma home configurada. Revise em Configurações → Leitura; nada foi substituído.' );
	}
	$routes = configure_routes();
	if ( is_wp_error( $routes ) ) {
		return $routes;
	}
	$result = wp_update_post(
		array(
			'ID'          => $home,
			'post_status' => 'publish',
		),
		true
	);
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	update_option( 'page_on_front', $home );
	update_option( 'show_on_front', 'page' );
	$example = native_example_post();
	if ( $example ) {
		wp_update_post(
			array(
				'ID'          => $example,
				'post_status' => 'draft',
			)
		);
	}
	$blog = seed_existing( 'page:blog' );
	if ( $blog && ! get_option( 'page_for_posts' ) && 'trash' !== get_post_status( $blog ) ) {
		wp_update_post(
			array(
				'ID'          => $blog,
				'post_status' => 'publish',
			)
		);
		update_option( 'page_for_posts', $blog );
	}
	$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
	foreach ( (array) get_option( 'iotnest_seed_menus', array() ) as $location => $menu ) {
		if ( empty( $locations[ $location ] ) && wp_get_nav_menu_object( $menu ) ) {
			$locations[ $location ] = $menu;
		}
	}
	set_theme_mod( 'nav_menu_locations', $locations );
	return true;
}

/**
 * Reconhece somente o exemplo nativo intacto, sem usar um ID fixo.
 *
 * @return int Zero quando há qualquer alteração editorial reconhecível.
 */
function native_example_post() {
	// Strings pertencem ao core e usam seu domínio/locale.
	$title = __( 'Hello world!', 'default' ); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
	$body  = __( 'Welcome to WordPress. This is your first post. Edit or delete it, then start writing!', 'default' ); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
	foreach ( get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'title'          => $title,
			'posts_per_page' => 2,
			'has_password'   => false,
		)
	) as $post ) {
		if ( $post->post_date === $post->post_modified && '' === $post->post_excerpt && trim( wp_strip_all_tags( $post->post_content ) ) === $body && ! get_post_thumbnail_id( $post ) ) {
			return (int) $post->ID;
		}
	}
	return 0;
}

/** Tela administrativa com ação explícita, nonce e relato de preservação. */
function importer_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="wrap"><h1>Importação institucional iotnest</h1><p>Cria rascunhos e mídias novos. Reexecutar preserva todas as edições e limpezas. Blog e cases começam sem registros.</p><p>Revise textos, contatos, indicadores e documentos legais antes de publicar. O setup publica somente a home importada e a página vazia do blog. Páginas legais continuam em rascunho.</p>';
	$example = native_example_post();
	if ( $example ) {
		echo '<p>O setup também moverá para rascunho o exemplo nativo intacto: <a href="' . esc_url( get_edit_post_link( $example ) ) . '">' . esc_html( get_the_title( $example ) ) . '</a>. Nenhum artigo editorial será removido.</p>';
	}
	if ( isset( $_POST['iotnest_seed_action'] ) ) {
		check_admin_referer( 'iotnest_seed' );
		$action = sanitize_key( wp_unslash( $_POST['iotnest_seed_action'] ) );
		$result = 'import' === $action ? import_institutional() : ( 'setup' === $action ? setup_institutional() : new \WP_Error( 'action', 'Ação inválida.' ) );
		echo '<div class="notice notice-info"><p>' . esc_html( is_wp_error( $result ) ? $result->get_error_message() : ( true === $result ? 'Setup concluído. Home e menus anteriores foram preservados.' : 'Importação concluída; revise os rascunhos.' ) ) . '</p></div>';
		if ( is_array( $result ) ) {
			foreach ( array(
				'created' => 'Criados',
				'reused'  => 'Reutilizados',
				'ignored' => 'Ignorados/preservados',
			) as $key => $label ) {
				echo '<h2>' . esc_html( $label ) . '</h2><p>' . esc_html( implode( ', ', $result[ $key ] ) ) . '</p>';
			}
		}
	}
	foreach ( array(
		'import' => 'Importar para revisão',
		'setup'  => 'Publicar home revisada e configurar menus/local de posts',
	) as $action => $label ) {
		echo '<form method="post">';
		wp_nonce_field( 'iotnest_seed' );
		echo '<input type="hidden" name="iotnest_seed_action" value="' . esc_attr( $action ) . '">';
		submit_button( $label, 'secondary' );
		echo '</form>';
	}
	echo '</div>';
}

add_action(
	'admin_menu',
	static function () {
		add_management_page( 'Importação iotnest', 'Importação iotnest', 'manage_options', 'iotnest-import', __NAMESPACE__ . '\\importer_page' );
	}
);
