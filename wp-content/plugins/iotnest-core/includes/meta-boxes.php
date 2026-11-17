<?php
/**
 * Meta boxes nativas e salvamento íntegro de payloads editoriais.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Identifica a home explicitamente selecionada ou pelo template editorial.
 *
 * @param \WP_Post $post Conteúdo.
 * @return bool
 */
function is_home_editor( $post ) {
	return 'page' === $post->post_type && ( (int) get_option( 'page_on_front' ) === $post->ID || 'page-templates/institutional.php' === get_post_meta( $post->ID, '_wp_page_template', true ) );
}

/**
 * Home usa campos; páginas textuais e artigos mantêm o editor nativo.
 *
 * @param bool     $enabled Estado do editor de blocos.
 * @param \WP_Post $post Conteúdo.
 * @return bool
 */
function editor_mode( $enabled, $post ) {
	if ( is_home_editor( $post ) ) {
		remove_post_type_support( 'page', 'editor' );
		return false;
	}
	return $enabled;
}

/** Registra uma meta box agrupada, sem impor conteúdo a páginas comuns. */
function add_boxes() {
	global $post;
	if ( $post && ( is_home_editor( $post ) || 'iot_case' === $post->post_type ) ) {
		add_meta_box( 'iotnest-content', 'Conteúdo iotnest', __NAMESPACE__ . '\\render_box', $post->post_type, 'normal', 'high' );
	}
}

/**
 * Renderiza controles nativos; nomes HTML não multiplicam os inputs recebidos no PHP.
 *
 * @param array  $field Contrato.
 * @param mixed  $value Valor.
 * @param string $key Chave no objeto pai.
 */
function render_field( $field, $value, $key = '' ) {
	$kind = $field['kind'];
	$id   = wp_unique_id( 'iotnest-field-' );
	echo '<div class="iotnest-field" data-field data-key="' . esc_attr( $key ) . '" data-kind="' . esc_attr( $kind ) . '">';
	if ( 'object' === $kind ) {
		echo '<fieldset><legend>' . esc_html( $field['label'] ) . '</legend>';
		foreach ( $field['fields'] as $child_key => $child ) {
			render_field( $child, is_array( $value ) ? ( $value[ $child_key ] ?? null ) : null, $child_key );
		}
		echo '</fieldset>';
	} elseif ( 'array' === $kind ) {
		echo '<fieldset data-repeater data-limit="' . esc_attr( $field['limit'] ) . '"><legend>' . esc_html( $field['label'] ) . '</legend><p>Limite: ' . esc_html( $field['limit'] ) . ' itens. Use os botões para ordenar pelo teclado.</p><div data-items>';
		foreach ( is_array( $value ) ? $value : array() as $item ) {
			render_repeater_item( $field['item'], $item );
		}
		echo '</div><template data-template>';
		render_repeater_item( $field['item'], null );
		echo '</template><button type="button" class="button" data-add>Adicionar item</button></fieldset>';
	} elseif ( 'id' === $kind ) {
		echo '<input data-input type="hidden" id="' . esc_attr( $id ) . '" value="' . esc_attr( (string) $value ) . '">';
	} else {
		echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label>';
		if ( 'page' === $kind ) {
			echo '<select data-input id="' . esc_attr( $id ) . '"><option value="0">—</option>';
			foreach ( get_pages( array( 'post_status' => 'publish' ) ) as $page ) {
				echo '<option value="' . esc_attr( $page->ID ) . '" ' . selected( (int) $value, $page->ID, false ) . '>' . esc_html( $page->post_title ) . '</option>';
			}
			echo '</select>';
		} elseif ( 'idref' === $kind ) {
			echo '<select data-input id="' . esc_attr( $id ) . '"><option value="' . esc_attr( (string) $value ) . '">Selecionar coluna</option></select>';
		} elseif ( 'select' === $kind ) {
			echo '<select data-input id="' . esc_attr( $id ) . '">';
			foreach ( $field['choices'] as $choice ) {
				echo '<option value="' . esc_attr( $choice ) . '" ' . selected( (string) $value, $choice, false ) . '>' . esc_html( '' === $choice ? '—' : ( section_labels()[ $choice ] ?? $choice ) ) . '</option>';
			}
			echo '</select>';
		} elseif ( in_array( $kind, array( 'rich', 'heading' ), true ) ) {
			echo '<textarea data-input id="' . esc_attr( $id ) . '" rows="3">' . esc_textarea( (string) $value ) . '</textarea>';
		} elseif ( 'boolean' === $kind ) {
			echo '<input data-input type="checkbox" id="' . esc_attr( $id ) . '" ' . checked( (bool) $value, true, false ) . '>';
		} else {
			$type = in_array( $kind, array( 'integer', 'number' ), true ) ? 'number' : ( in_array( $kind, array( 'image', 'video' ), true ) ? 'hidden' : 'text' );
			echo '<input data-input type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" value="' . esc_attr( (string) $value ) . '" step="' . esc_attr( 'number' === $kind ? 'any' : '1' ) . '">';
			if ( in_array( $kind, array( 'image', 'video' ), true ) ) {
				echo '<button type="button" class="button" data-media="' . esc_attr( $kind ) . '">Selecionar mídia</button> <button type="button" class="button" data-clear>Remover mídia</button><p data-media-label>' . esc_html( $value ? get_the_title( (int) $value ) : 'Nenhum arquivo selecionado.' ) . '</p>';
			}
		}
	}
	echo '</div>';
}

/**
 * Item de repetidor com controles acessíveis.
 *
 * @param array $field Contrato do item.
 * @param mixed $value Dados.
 */
function render_repeater_item( $field, $value ) {
	echo '<div data-item class="iotnest-item">';
	render_field( $field, $value );
	echo '<div><button type="button" class="button" data-up aria-label="Mover item para cima">↑ Mover acima</button> <button type="button" class="button" data-down aria-label="Mover item para baixo">↓ Mover abaixo</button> <button type="button" class="button" data-remove>Remover item</button></div></div>';
}

/**
 * Editor de campos com snapshot JSON limitado e marcador de integridade.
 *
 * @param \WP_Post $post Conteúdo.
 */
function render_box( $post ) {
	wp_nonce_field( 'iotnest_fields_' . $post->ID, '_iotnest_nonce' );
	echo '<div class="iotnest-admin" data-iotnest-admin><p>A home é composta pelos campos abaixo. Limpar o campo principal ou desabilitar uma seção a oculta. Nenhum texto é reposto automaticamente. Recomenda-se manter a extensão e proporções da referência; textos longos não são cortados.</p><noscript>Ative JavaScript para editar os campos estruturados. Os dados existentes serão preservados.</noscript>';
	foreach ( meta_schemas( $post->post_type ) as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		if ( '_iotnest_home_order' === $key && ! metadata_exists( 'post', $post->ID, $key ) ) {
			$value = section_keys();
		}
		echo '<details data-meta="' . esc_attr( $key ) . '"><summary>' . esc_html( $field['label'] ) . '</summary>';
		if ( str_starts_with( $key, '_iotnest_home_' ) && '_iotnest_home_order' !== $key ) {
			$reason = hidden_reason( substr( $key, strlen( '_iotnest_home_' ) ), is_array( $value ) ? $value : array() );
			echo '<p class="description">' . esc_html( $reason ? 'Oculta: ' . $reason : 'Disponível para exibição.' ) . '</p>';
		}
		render_field( $field, $value, $key );
		echo '</details>';
	}
	echo '<p role="status" aria-live="polite" data-status></p><input type="hidden" name="_iotnest_payload" data-payload value=""><input type="hidden" name="_iotnest_complete" value="1"></div>';
}

/**
 * Valida todo o patch antes de gravar qualquer seção.
 *
 * @param int   $post_id Conteúdo pai.
 * @param array $request Dados já desserializados pelo PHP, ainda com slashes.
 * @return array|\WP_Error|null Null indica submissão sem estes campos.
 */
function validated_patch( $post_id, $request ) {
	if ( ! isset( $request['_iotnest_payload'] ) || '' === $request['_iotnest_payload'] ) {
		return null;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) || ! isset( $request['_iotnest_nonce'] ) || ! is_string( $request['_iotnest_nonce'] ) || ! wp_verify_nonce( $request['_iotnest_nonce'], 'iotnest_fields_' . $post_id ) ) {
		return new \WP_Error( 'iotnest_permission', 'Não foi possível autorizar a gravação dos campos.' );
	}
	if ( '1' !== ( $request['_iotnest_complete'] ?? '' ) || ! is_string( $request['_iotnest_payload'] ) || strlen( $request['_iotnest_payload'] ) > 1048576 ) {
		return new \WP_Error( 'iotnest_incomplete', 'Formulário incompleto ou maior que 1 MiB. Os campos anteriores foram preservados.' );
	}
	$patch = json_decode( wp_unslash( $request['_iotnest_payload'] ), true, 32 );
	if ( ! is_array( $patch ) || JSON_ERROR_NONE !== json_last_error() ) {
		return new \WP_Error( 'iotnest_json', 'Dados inválidos ou truncados. Os campos anteriores foram preservados.' );
	}
	return validate_meta_patch( $post_id, $patch );
}

/**
 * Validação comum para o formulário nativo e o endpoint autenticado.
 *
 * @param int    $post_id ID do objeto.
 * @param array  $patch Metadados enviados.
 * @param string $post_type Tipo para objetos ainda não criados.
 * @return array|\WP_Error
 */
function validate_meta_patch( $post_id, $patch, $post_type = '' ) {
	$schemas = meta_schemas( $post_type ? $post_type : get_post_type( $post_id ) );
	$result  = array();
	foreach ( $patch as $key => $value ) {
		if ( ! isset( $schemas[ $key ] ) ) {
			return new \WP_Error( 'iotnest_key', 'Campo desconhecido na submissão.' );
		}
		$field = $schemas[ $key ];
		$value = merge_patch( get_post_meta( $post_id, $key, true ), $value, $field );
		$valid = rest_validate_value_from_schema( $value, json_schema( $field ), $key );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}
		if ( ! unique_ids( $value, $field ) ) {
			return new \WP_Error( 'iotnest_ids', 'Há identificadores duplicados no repetidor ou na ordem de seções.' );
		}
		$result[ $key ] = normalize( $value, $field );
	}
	return $result;
}

/**
 * Salvamento convencional. Autosave/revisão não são submissões completas.
 *
 * @param int      $post_id Conteúdo.
 * @param \WP_Post $post Objeto.
 */
function save_boxes( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! meta_schemas( $post->post_type ) ) {
		return;
	}
	// A função central valida nonce, capacidade, tipos e integridade antes de gravar.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$patch = validated_patch( $post_id, $_POST );
	if ( is_wp_error( $patch ) ) {
		set_transient( 'iotnest_error_' . get_current_user_id(), $patch->get_error_message(), 300 );
		return;
	}
	foreach ( $patch ?? array() as $key => $value ) {
		update_post_meta( $post_id, $key, wp_slash( $value ) );
	}
	if ( $patch ) {
		update_post_meta( $post_id, '_iotnest_schema_version', SCHEMA_VERSION );
	}
}

/**
 * Preview usa metadados da revisão, sem escrever no post público.
 *
 * @param array $autosave Revisão criada pelo WordPress.
 */
function save_preview( $autosave ) {
	$parent = (int) $autosave['post_parent'];
	// O nonce próprio e a capacidade também são conferidos no autosave.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	$request = $_POST['data']['wp_autosave'] ?? $_POST;
	$patch   = validated_patch( $parent, $request );
	if ( is_wp_error( $patch ) || null === $patch ) {
		return;
	}
	foreach ( meta_schemas( get_post_type( $parent ) ) as $key => $schema ) {
		if ( array_key_exists( $key, $patch ) || metadata_exists( 'post', $parent, $key ) ) {
			$value = $patch[ $key ] ?? get_post_meta( $parent, $key, true );
			update_metadata( 'post', $autosave['ID'], $key, wp_slash( $value ) );
		}
	}
}

/** Exibe erros de validação apenas ao usuário que submeteu os campos. */
function save_notice() {
	$key     = 'iotnest_error_' . get_current_user_id();
	$message = get_transient( $key );
	if ( $message ) {
		echo '<div class="notice notice-error" role="alert"><p>Campos iotnest não gravados: ' . esc_html( $message ) . '</p></div>';
		delete_transient( $key );
	}
}

/** Carrega mídia e UI somente em telas com campos do projeto. */
function admin_assets() {
	$screen = get_current_screen();
	global $post;
	if ( ! $screen || ( 'settings_page_iotnest' !== $screen->id && ( ! $post || ( ! is_home_editor( $post ) && 'iot_case' !== $post->post_type ) ) ) ) {
		return;
	}
	wp_enqueue_media();
	$directory = dirname( IOTNEST_CORE_FILE ) . '/assets/dist/';
	// Leitura de manifesto local, não de conteúdo remoto.
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$manifest = is_readable( $directory . 'manifest.json' ) ? json_decode( file_get_contents( $directory . 'manifest.json' ), true ) : array();
	foreach ( array( 'css', 'js' ) as $extension ) {
		$file = $manifest[ 'admin.' . $extension ] ?? '';
		if ( ! is_string( $file ) || ! preg_match( '/\Aadmin\.[a-f0-9]{12}\.' . $extension . '\z/', $file ) || ! is_readable( $directory . $file ) ) {
			continue;
		}
		$url     = plugins_url( 'assets/dist/' . $file, IOTNEST_CORE_FILE );
		$version = explode( '.', $file )[1];
		if ( 'css' === $extension ) {
			wp_enqueue_style( 'iotnest-admin', $url, array(), $version );
		} else {
			wp_enqueue_script( 'iotnest-admin', $url, array( 'jquery', 'media-editor' ), $version, true );
		}
	}
}
