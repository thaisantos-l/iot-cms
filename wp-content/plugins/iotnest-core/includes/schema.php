<?php
/**
 * Contratos editoriais versionados. Valores editoriais não são defaults do schema.
 *
 * @package Iotnest
 */

namespace Iotnest\Core;

defined( 'ABSPATH' ) || exit;

const SCHEMA_VERSION = 1;

/**
 * Campo tipado usado pelo registro, validação e UI nativa.
 *
 * @param string $type Tipo de interface.
 * @param string $label Rótulo editorial.
 * @param array  $extra Restrições adicionais.
 * @return array
 */
function field( $type, $label, $extra = array() ) {
	return array_merge(
		array(
			'kind'  => $type,
			'label' => $label,
		),
		$extra
	);
}

/**
 * Objeto com propriedades permitidas.
 *
 * @param string $label Rótulo.
 * @param array  $fields Propriedades.
 * @return array
 */
function group( $label, $fields ) {
	return field( 'object', $label, array( 'fields' => $fields ) );
}

/**
 * Repetidor com IDs persistentes, sem limite dependente de max_input_vars.
 *
 * @param string $label Rótulo.
 * @param array  $fields Propriedades de cada item.
 * @param int    $limit Limite documentado de itens.
 * @return array
 */
function repeat( $label, $fields, $limit = 50 ) {
	return field(
		'array',
		$label,
		array(
			'item'  => group( $label, array_merge( array( 'id' => field( 'id', 'Identificador' ) ), $fields ) ),
			'limit' => $limit,
		)
	);
}

/**
 * Contrato de destino, independente do texto do link.
 *
 * @return array
 */
function cta_schema() {
	return group(
		'Link / chamada',
		array(
			'label'      => field( 'text', 'Texto do link' ),
			'kind'       => field( 'select', 'Tipo de destino', array( 'choices' => array( '', 'page', 'section', 'url', 'blog', 'cases' ) ) ),
			'page_id'    => field(
				'page',
				'Página de destino',
				array(
					'min' => 0,
					'max' => PHP_INT_MAX,
				)
			),
			'section'    => field( 'select', 'Seção', array( 'choices' => array_merge( array( '' ), section_keys() ) ) ),
			'url'        => field( 'url', 'URL externa' ),
			'new_window' => field( 'boolean', 'Abrir em nova aba' ),
		)
	);
}

/**
 * Ordem da referência, incluindo blog previsto mas inicialmente vazio.
 *
 * @return string[]
 */
function section_keys() {
	return array( 'hero', 'features', 'solution', 'hardware', 'ai', 'assistant', 'defects', 'energy', 'reports', 'mes', 'calculator', 'comparison', 'cases', 'how_it_works', 'testimonials', 'blog', 'videos', 'about', 'faq', 'contact' );
}

/**
 * Rótulos do painel, independentes das chaves persistentes.
 *
 * @return array
 */
function section_labels() {
	return array_combine( section_keys(), array( 'Hero', 'Faixa de recursos', 'Solução', 'Hardware', 'Inteligência artificial', 'Assistente Dadá', 'Apontamento de defeitos', 'Energia', 'Relatórios', 'Módulos MES', 'Calculadora', 'Comparação', 'Cases', 'Como funciona', 'Depoimentos', 'Blog', 'Vídeos', 'Sobre', 'Perguntas frequentes', 'Contato' ) );
}

/**
 * Fontes de vídeo permitidas.
 *
 * @return array
 */
function video_schema() {
	return group(
		'Vídeo',
		array(
			'provider'  => field( 'select', 'Origem', array( 'choices' => array( '', 'local', 'youtube' ) ) ),
			'file'      => field( 'video', 'Arquivo principal' ),
			'alternate' => field( 'video', 'Formato alternativo' ),
			'youtube'   => field( 'youtube', 'ID do vídeo no YouTube (11 caracteres)' ),
			'start'     => field(
				'integer',
				'Iniciar no segundo',
				array(
					'min' => 0,
					'max' => 86400,
				)
			),
		)
	);
}

/**
 * Todos os campos de cada seção prevista.
 *
 * @return array
 */
function home_schema() {
	static $sections;
	if ( isset( $sections ) ) {
		return $sections;
	}
	$text     = field( 'text', 'Texto' );
	$rich     = field( 'rich', 'Conteúdo (HTML editorial permitido)' );
	$image    = field( 'image', 'Imagem da biblioteca' );
	$bullets  = repeat( 'Itens de texto', array( 'text' => $rich ) );
	$common   = array(
		'enabled'     => field( 'boolean', 'Exibir seção' ),
		'eyebrow'     => field( 'text', 'Texto acima do título' ),
		'title'       => field( 'heading', 'Título principal' ),
		'description' => field( 'rich', 'Descrição' ),
	);
	$extra    = array(
		'hero'         => array(
			'poster' => $image,
			'video'  => video_schema(),
			'ctas'   => repeat( 'Chamadas', cta_schema()['fields'], 4 ),
		),
		'features'     => array(
			'label'   => field( 'text', 'Rótulo acessível da faixa' ),
			'items'   => $bullets,
			'animate' => field( 'boolean', 'Animar faixa' ),
		),
		'solution'     => array(
			'body'    => $rich,
			'bullets' => $bullets,
			'image'   => $image,
			'cta'     => cta_schema(),
		),
		'hardware'     => array(
			'bullets'  => $bullets,
			'images'   => repeat( 'Imagens do hardware', array( 'image' => $image ) ),
			'subtitle' => field( 'heading', 'Título da telemetria' ),
			'image'    => $image,
			'groups'   => repeat(
				'Grupos de sinais',
				array(
					'icon'  => field( 'icon', 'Ícone' ),
					'title' => $text,
					'items' => $bullets,
				)
			),
		),
		'ai'           => array(
			'items' => repeat(
				'Recursos de IA',
				array(
					'text'  => $text,
					'image' => $image,
					'icon'  => field( 'icon', 'Ícone' ),
					'tone'  => field( 'select', 'Cor do ícone', array( 'choices' => array( 'orange', 'blue', 'green' ) ) ),
				)
			),
		),
		'assistant'    => array(
			'video'    => video_schema(),
			'poster'   => $image,
			'autoplay' => field( 'boolean', 'Reproduzir automaticamente' ),
			'loop'     => field( 'boolean', 'Repetir vídeo' ),
			'muted'    => field( 'boolean', 'Iniciar sem som' ),
		),
		'defects'      => array(
			'body'    => $rich,
			'bullets' => $bullets,
			'image'   => $image,
			'cta'     => cta_schema(),
		),
		'energy'       => array(
			'body'    => $rich,
			'bullets' => $bullets,
			'image'   => $image,
			'labels'  => $bullets,
		),
		'reports'      => array(
			'image'  => $image,
			'groups' => repeat( 'Colunas de recursos', array( 'items' => $bullets ) ),
		),
		'mes'          => array(
			'items' => repeat(
				'Módulos MES',
				array(
					'label'       => $text,
					'caption'     => $text,
					'title'       => $text,
					'description' => $rich,
					'image'       => $image,
				)
			),
		),
		'calculator'   => array(
			'labels'   => repeat(
				'Rótulos da calculadora',
				array(
					'key'  => $text,
					'text' => $text,
				),
				30
			),
			'messages' => repeat(
				'Mensagens da calculadora',
				array(
					'key'  => $text,
					'text' => $text,
				),
				30
			),
			'note'     => $rich,
			'cta'      => cta_schema(),
		),
		'comparison'   => array(
			'columns' => repeat(
				'Colunas (IDs preservam a associação das células)',
				array(
					'label'     => $text,
					'marker'    => $text,
					'highlight' => field( 'boolean', 'Destacar coluna' ),
				),
				6
			),
			'rows'    => repeat(
				'Linhas de comparação',
				array(
					'label' => $text,
					'cells' => repeat(
						'Células por ID de coluna',
						array(
							'column_id' => field( 'idref', 'Coluna' ),
							'state'     => field( 'select', 'Estado', array( 'choices' => array( 'full', 'partial', 'none' ) ) ),
						),
						6
					),
				)
			),
			'note'    => $rich,
		),
		'cases'        => array(
			'limit' => field(
				'integer',
				'Limite de cards (1–12)',
				array(
					'min' => 1,
					'max' => 12,
				)
			),
			'cta'   => cta_schema(),
		),
		'how_it_works' => array(
			'items' => repeat(
				'Etapas',
				array(
					'label' => $text,
					'title' => $text,
					'text'  => $rich,
					'image' => $image,
					'icon'  => field( 'icon', 'Ícone' ),
					'fit'   => field( 'select', 'Encaixe da imagem', array( 'choices' => array( '', 'cover', 'contain' ) ) ),
				)
			),
		),
		'testimonials' => array(
			'items' => repeat(
				'Depoimentos',
				array(
					'quote'    => $rich,
					'name'     => field( 'text', 'Nome' ),
					'role'     => field( 'text', 'Cargo' ),
					'company'  => field( 'text', 'Empresa' ),
					'avatar'   => $image,
					'initials' => field( 'text', 'Iniciais (sem foto)' ),
				)
			),
		),
		'blog'         => array(
			'limit'       => field(
				'integer',
				'Limite de cards (1–12)',
				array(
					'min' => 1,
					'max' => 12,
				)
			),
			'category_id' => field(
				'integer',
				'ID de categoria (zero = todas)',
				array(
					'min' => 0,
					'max' => PHP_INT_MAX,
				)
			),
			'cta'         => cta_schema(),
		),
		'videos'       => array(
			'cta'   => cta_schema(),
			'items' => repeat(
				'Vídeos',
				array(
					'title'       => $text,
					'description' => $rich,
					'video'       => video_schema(),
					'thumbnail'   => $image,
				)
			),
		),
		'about'        => array(
			'paragraphs' => repeat( 'Parágrafos', array( 'text' => $rich ) ),
			'image'      => $image,
			'bullets'    => $bullets,
			'number'     => field(
				'number',
				'Número do indicador',
				array(
					'min' => 0,
					'max' => 1000000000,
				)
			),
			'unit'       => field( 'text', 'Unidade' ),
			'label'      => field( 'text', 'Rótulo do indicador' ),
		),
		'faq'          => array(
			'cta_intro' => $text,
			'items'     => repeat(
				'Perguntas frequentes',
				array(
					'question' => field( 'text', 'Pergunta' ),
					'answer'   => field( 'rich', 'Resposta' ),
				),
				100
			),
			'cta'       => cta_schema(),
		),
		'contact'      => array(
			'caption_title' => $text,
			'caption_text'  => $text,
			'image'         => $image,
			'channels'      => repeat(
				'Canais de contato',
				array(
					'label' => $text,
					'value' => $rich,
					'url'   => field( 'url', 'Destino' ),
					'icon'  => field( 'icon', 'Ícone' ),
				)
			),
			'form_title'    => field( 'text', 'Título do formulário' ),
			'messages'      => repeat(
				'Mensagens do formulário',
				array(
					'key'  => $text,
					'text' => $text,
				),
				20
			),
		),
	);
	$sections = array();
	foreach ( section_keys() as $key ) {
		$sections[ $key ] = group( section_labels()[ $key ], array_merge( $common, $extra[ $key ] ) );
	}
	return $sections;
}

/**
 * Campos do case; narrativa permanece no editor nativo.
 *
 * @return array
 */
function case_schema() {
	return group(
		'Dados do case',
		array(
			'company'    => field( 'text', 'Empresa' ),
			'solution'   => field( 'text', 'Solução' ),
			'highlights' => repeat(
				'Indicadores',
				array(
					'value' => field( 'text', 'Valor' ),
					'label' => field( 'text', 'Descrição' ),
				)
			),
			'cta'        => cta_schema(),
		)
	);
}

/**
 * Schemas por chave de metadado.
 *
 * @param string $post_type Tipo do conteúdo.
 * @return array
 */
function meta_schemas( $post_type ) {
	if ( 'iot_case' === $post_type ) {
		return array( '_iotnest_case' => case_schema() );
	}
	if ( 'page' !== $post_type ) {
		return array();
	}
	$schemas = array();
	foreach ( home_schema() as $key => $schema ) {
		$schemas[ '_iotnest_home_' . $key ] = $schema;
	}
	$schemas['_iotnest_home_order'] = field(
		'array',
		'Ordem das seções',
		array(
			'item'  => field( 'select', 'Seção', array( 'choices' => section_keys() ) ),
			'limit' => 20,
		)
	);
	return $schemas;
}
