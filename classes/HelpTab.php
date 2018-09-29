<?php
namespace BetaFlags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HelpTab {
	private $id = '';
	private $title = '';
	private $html = '';
	private $kses = array(
		'a' => array(
			'href' => array(),
			'title' => array(),
			'target' => array(),
		),
		'img' => array(
			'src' => array(),
			'alt' => array(),
			'title' => array(),
			'width' => array(),
			'height' => array(),
			'style' => array(),
		),
		'h1' => array(),
		'h2' => array(),
		'h3' => array(),
		'p' => array(),
		'div' => array(),
		'span' => array(),
		'br' => array(),
		'em' => array(),
		'b' => array(),
		'i' => array(),
		'strong' => array(),
		'ol' => array(),
		'ul' => array(),
		'li' => array(),
		'blockquote' => array(),
	);

	function __construct( $id, $title, $template, $autocreate = false ) {
		$this->id = trim( $id );
		$this->title = trim( $title );
		$this->html = $this->get_html( $template );
		if ( true === (bool) $autocreate ) {
			$this->create();
		}
	}

	public function create() {
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id' => esc_attr( $this->id ),
			'title' => esc_html( $this->title ),
			'content' => wp_kses( __( $this->html ), $this->kses ),
		) );
	}

	private function get_html( $template ) {
		ob_start();
		load_template( FF_PLUGIN_PATH . 'helptabs/' . $template, true );
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
