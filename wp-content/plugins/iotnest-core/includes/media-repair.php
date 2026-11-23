<?php
/**
 * Recuperação explícita das mídias institucionais após migrar o banco sem uploads.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Restaura somente arquivos conhecidos de um attachment existente.
 *
 * @param int    $id Attachment preservado na migração.
 * @param string $source Chave da mídia no pacote institucional.
 * @param array  $item Arquivo e checksum da origem.
 * @return string|\WP_Error restored, present ou skipped.
 */
function repair_seed_attachment( $id, $source, $item ) {
	$key = 'media:' . $source;
	if ( ! $id || 'attachment' !== get_post_type( $id ) || 'trash' === get_post_status( $id ) || get_post_meta( $id, '_iotnest_seed_key', true ) !== $key ) {
		return 'skipped';
	}
	$name = $item['file'] ?? '';
	if ( ! preg_match( '/\A[a-z0-9-]+\.(webp|webm|mp4)\z/', $name ) ) {
		return new \WP_Error( 'media_name', 'Nome de mídia inválido no pacote.' );
	}
	$original = dirname( IOTNEST_CORE_FILE ) . '/resources/seed/media/' . $name;
	if ( ! is_readable( $original ) || hash_file( 'sha256', $original ) !== ( $item['sha256'] ?? '' ) ) {
		return new \WP_Error( 'media_hash', 'Arquivo de origem ausente ou divergente: ' . $name );
	}
	$relative = get_post_meta( $id, '_wp_attached_file', true );
	// Nomes com sufixo numérico são criados pelo WordPress; imagens editadas não são recuperáveis por este pacote.
	$stem    = pathinfo( $name, PATHINFO_FILENAME );
	$ext     = pathinfo( $name, PATHINFO_EXTENSION );
	$pattern = '/\A(?:[a-zA-Z0-9_-]+\/)*' . preg_quote( $stem, '/' ) . '(?:-[0-9]+)?\.' . $ext . '\z/';
	if ( ! is_string( $relative ) || ! preg_match( $pattern, $relative ) ) {
		return new \WP_Error( 'media_path', 'Caminho alterado ou inválido; preservado attachment ' . $id );
	}
	$uploads = wp_upload_dir();
	if ( $uploads['error'] ) {
		return new \WP_Error( 'uploads', $uploads['error'] );
	}
	$base   = wp_normalize_path( realpath( $uploads['basedir'] ) );
	$target = $base . '/' . $relative;
	$parent = dirname( $target );
	$probe  = $parent;
	while ( ! file_exists( $probe ) && ! is_link( $probe ) ) {
		$probe = dirname( $probe );
	}
	$resolved = wp_normalize_path( (string) realpath( $probe ) );
	if ( ! $base || ( $resolved !== $base && ! str_starts_with( $resolved, $base . '/' ) ) || is_link( $target ) ) {
		return new \WP_Error( 'media_path', 'O destino deve permanecer dentro de uploads: ' . $name );
	}
	$restored = false;
	if ( file_exists( $target ) ) {
		// Um arquivo substituído pelo editor jamais é trocado pelo original do seed.
		if ( ! is_readable( $target ) || hash_file( 'sha256', $target ) !== $item['sha256'] ) {
			return 'skipped';
		}
	} else {
		if ( ! wp_mkdir_p( $parent ) ) {
			return new \WP_Error( 'media_directory', 'Não foi possível criar a pasta de ' . $name );
		}
		// Abertura exclusiva impede sobrescrever um arquivo criado durante a operação.
		// phpcs:disable WordPress.WP.AlternativeFunctions -- Recuperação local explícita, sem credenciais de filesystem remoto.
		$handle = fopen( $target, 'xb' );
		if ( ! $handle ) {
			return new \WP_Error( 'media_copy', 'Não foi possível criar ' . $name );
		}
		$bytes = fwrite( $handle, file_get_contents( $original ) );
		fclose( $handle );
		if ( filesize( $original ) !== $bytes || hash_file( 'sha256', $target ) !== $item['sha256'] ) {
			wp_delete_file( $target );
			return new \WP_Error( 'media_copy', 'Falha ao conferir a cópia de ' . $name );
		}
		chmod( $target, fileperms( $parent ) & 0000666 );
		// phpcs:enable WordPress.WP.AlternativeFunctions
		$restored = true;
	}
	if ( wp_attachment_is_image( $id ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$metadata = wp_get_attachment_metadata( $id );
		$missing  = false;
		foreach ( $metadata['sizes'] ?? array() as $size => $details ) {
			if ( ! is_readable( $parent . '/' . wp_basename( $details['file'] ) ) ) {
				unset( $metadata['sizes'][ $size ] );
				$missing = true;
			}
		}
		if ( $missing ) {
			wp_update_attachment_metadata( $id, $metadata );
		}
		// Preserva os tamanhos registrados que ainda existem no disco.
		$result = wp_update_image_subsizes( $id );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		if ( empty( $result['width'] ) || wp_get_missing_image_subsizes( $id ) ) {
			return new \WP_Error( 'media_sizes', 'Original disponível, mas não foi possível gerar todas as miniaturas de ' . $name );
		}
		$restored = $restored || $missing;
	}
	if ( $restored ) {
		queue_purge( 'media_repair', $id );
	}
	return $restored ? 'restored' : 'present';
}

/** Recuperação executada apenas pelo operador via wp iotnest repair-media. */
function repair_seed_media_command() {
	$path = dirname( IOTNEST_CORE_FILE ) . '/resources/seed/institutional.json';
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Manifesto local versionado.
	$seed = json_decode( file_get_contents( $path ), true );
	if ( ! is_array( $seed ) || 1 !== ( $seed['version'] ?? 0 ) || empty( $seed['media'] ) ) {
		\WP_CLI::error( 'Manifesto de mídias inválido.' );
	}
	if ( ! add_option( 'iotnest_seed_lock', time(), '', false ) ) {
		\WP_CLI::error( 'Já existe uma importação ou recuperação de mídias em execução.' );
	}
	$counts = array(
		'restored' => 0,
		'present'  => 0,
		'skipped'  => 0,
		'errors'   => 0,
	);
	try {
		foreach ( $seed['media'] as $source => $item ) {
			$result = repair_seed_attachment( seed_existing( 'media:' . $source ), $source, $item );
			if ( is_wp_error( $result ) ) {
				++$counts['errors'];
				\WP_CLI::warning( $result->get_error_message() );
			} else {
				++$counts[ $result ];
				\WP_CLI::log( $result . ': ' . $item['file'] );
			}
		}
	} finally {
		delete_option( 'iotnest_seed_lock' );
	}
	$message = sprintf( 'Mídias reparadas: %d; já presentes: %d; preservadas/sem registro: %d; erros: %d.', $counts['restored'], $counts['present'], $counts['skipped'], $counts['errors'] );
	if ( $counts['errors'] ) {
		\WP_CLI::error( $message );
	}
	\WP_CLI::success( $message );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'iotnest repair-media', __NAMESPACE__ . '\\repair_seed_media_command' );
}
