<?php

class RP_Admin_Render_Controller {

	
	private $plugin_name;

	
	private $version;

	
	private $html_helper;


	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->html_helper = new RP_Html_Fields();
	}


	public function retrive_template( $name, $model = false ) {
		
		if ( file_exists( get_stylesheet_directory() . '/rp' ) ) {
			if ( file_exists( get_stylesheet_directory() . '/rp/' . $name . '.css' ) ) {
				wp_enqueue_style( $this->plugin_name . '-' . $name . '-custom', get_stylesheet_directory_uri() . '/rp/' . $name . '.css', array(), $this->version );
			}
			if ( file_exists( get_stylesheet_directory() . '/rp/' . $name . '.php' ) ) {
				include_once( get_stylesheet_directory() . '/rp/' . $name . '.php' );
				return;
			}
		}

		if ( file_exists( RP_PATH . '/includes/admin/layouts/css/' . $name . '.css' ) ) {
			wp_enqueue_style( $this->plugin_name . '-' . $name . '-css', '/includes/admin/layouts/css/' . $name . '.css', array(), $this->version );
		}
		
	}

	
	public function render_editor_metabox( $template, $model = false ) {
		if ( ! file_exists( $template ) ) {
			$template = RP_PATH . '/includes/admin/layouts/' . $template . '-tpl.php';
		}
		$html_helper = new RP_Html_Fields();
		include_once( $template );
	}

	
	public function add_element( $field ) {
		$output = '';
		if ( 'hidden' !== $field['type'] ) {
			$output = '
				<div class="controls">
					<div class="explain"><h4>' . $field['title'] . '</h4></div>
					<div class="controls-content">
			';
		}
		switch ( $field['type'] ) {
			case 'input_text':
				$output .= $this->html_helper->text( $field );
				break;
			default:
				$method = $field['type'];
				$output .= $this->html_helper->$method( $field );
				break;
		}
		if ( 'hidden' !== $field['type'] ) {
			$output .= '<p class="field_description">' . $field['description'] . '</p></div></div><hr/>';
		}
		echo $output;

		if ( isset( $errors ) ) {
			return $errors; }
	}
}
