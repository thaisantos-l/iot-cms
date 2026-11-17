<?php
/**
 * Walker de apresentação do menu primário.
 *
 * @package Iotnest
 */

namespace Iotnest\Theme;

defined( 'ABSPATH' ) || exit;

/** Walker preserva a estrutura visual do menu primário, inclusive submenu. */
class Primary_Menu extends \Walker_Nav_Menu {
	/**
	 * ID do último pai para aria-controls.
	 *
	 * @var int
	 */
	private $parent_id = 0;

	/**
	 * Abre submenu com ID associado ao botão.
	 *
	 * @param string $output HTML acumulado.
	 * @param int    $depth Nível.
	 * @param object $args Argumentos.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="nav__panel" id="iotnest-submenu-' . absint( $this->parent_id ) . '">';
	}

	/**
	 * Abre item preservando legenda, destino e estado atual.
	 *
	 * @param string $output HTML acumulado.
	 * @param object $data_object Item.
	 * @param int    $depth Nível.
	 * @param object $args Argumentos.
	 * @param int    $current_object_id Objeto atual.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item = $data_object;
		if ( $depth ) {
			$output .= '<li>';
		} elseif ( $this->has_children ) {
			$output .= '<div class="nav__dropdown">';
		}
		$label   = esc_html( $item->title );
		$current = $item->current ? ' aria-current="page"' : '';
		if ( $item->url ) {
			$output .= '<a' . ( ! $depth ? ' class="nav__link"' : '' ) . ' href="' . esc_url( $item->url ) . '"' . $current . ( '_blank' === $item->target ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . $label . '</a>';
		}
		if ( $this->has_children ) {
			$this->parent_id = $item->ID;
			$output         .= '<button class="nav__link nav__dropdown-btn" type="button" aria-expanded="false" aria-controls="iotnest-submenu-' . absint( $item->ID ) . '"' . ( $item->url ? ' aria-label="Abrir submenu: ' . esc_attr( $item->title ) . '"' : '' ) . '>' . ( $item->url ? '' : $label ) . '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M3 4.5 6 7.5 9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
		}
	}

	/**
	 * Fecha item conforme seu nível.
	 *
	 * @param string $output HTML acumulado.
	 * @param object $data_object Item.
	 * @param int    $depth Nível.
	 * @param object $args Argumentos.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		if ( $depth ) {
			$output .= '</li>';
		} elseif ( in_array( 'menu-item-has-children', $data_object->classes, true ) ) {
			$output .= '</div>';
		}
	}
}
