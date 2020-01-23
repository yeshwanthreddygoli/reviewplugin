<?php


class RP_Template {
	
	private $dirs = array();

	
	public function __construct() {
		$this->setup_locations();
	}

	
	private function setup_locations() {
		$this->dirs[]  = RP_PATH . '/includes/public/layouts/';
		$custom_paths  = apply_filters( 'rp_templates_dir', array() );
		$theme_paths   = array();
		$theme_paths[] = get_template_directory() . '/rp';
		$theme_paths[] = get_stylesheet_directory() . '/rp';
		$this->dirs    = array_merge( $this->dirs, $custom_paths, $theme_paths );
		$this->dirs    = array_map( 'trailingslashit', $this->dirs );

	}

	
	public function render( $template, $args = array(), $echo = true ) {
		$location = $this->locate_template( $template );
		if ( empty( $location ) ) {
			return '';
		}
		foreach ( $args as $name => $value ) {
			if ( is_numeric( $name ) ) {
				continue;
			}
			$$name = $value;
		}
		
		$cache_key = md5( $location . serialize( $args ) );
		$content   = wp_cache_get( $cache_key, 'rp' );
		if ( empty( $content ) ) {
			ob_start();
			require( $location );
			$content = ob_get_contents();
			ob_end_clean();

			wp_cache_set( $cache_key, $content, 'rp', 5 * 60 );
		}
		if ( ! $echo ) {
			return $content;
		}
		echo $content;

		return '';
	}


	public function locate_template( $template ) {
		$dirs     = array_reverse( $this->dirs );
		$template = str_replace( '.php', '', $template );
		$template = $template . '.php';
		foreach ( $dirs as $dir ) {
			if ( file_exists( $dir . $template ) ) {
				return $dir . $template;
			}
		}

		return '';
	}
}
