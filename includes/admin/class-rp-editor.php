<?php

class RP_Editor {

	
	private $plugin_name;

	
	private $version;

	
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	
	public function set_editor() {
		$back_compat_meta_box = '';
		if ( function_exists( 'register_block_type' ) ) {
			$current_screen = get_current_screen();

			$back_compat_meta_box = array(
				'__back_compat_meta_box' => true,
			);

			if ( ( class_exists( 'Classic_Editor' ) && ! $current_screen->is_block_editor() ) || ! $current_screen->is_block_editor() ) {
				$back_compat_meta_box = array(
					'__back_compat_meta_box' => false,
				);
			}
		}

		add_meta_box(
			'rp_editor_metabox',
			__( 'Product Review Extra Settings', 'wp-product-review' ),
			array(
				$this,
				'render_metabox',
			),
			$back_compat_meta_box
		);
	}

	
	public function render_metabox( $post ) {
		$editor = $this->get_editor_name( $post );
		wp_nonce_field( 'rp_editor_save.' . $post->ID, '_rp_nonce' );
		$render_controller = new RP_Admin_Render_Controller( $this->plugin_name, $this->version );
		$render_controller->render_editor_metabox( $editor->get_template(), $editor );
	}

	
	private function get_editor_name( $post ) {
		$editor_name = 'RP_' . str_replace( '-', '_', ucfirst( $post->post_type ) . '_Editor' );
		if ( class_exists( $editor_name ) ) {
			$editor = new $editor_name( $post );
		} else {
			$editor = new RP_Editor_Model( $post );
		}

		return $editor;
	}

	
	public function load_assets( $post ) {
		global $post;
		if ( is_a( $post, 'WP_Post' ) ) {
			$editor = $this->get_editor_name( $post );
			$assets = $editor->get_assets();
			if ( ! empty( $assets ) ) {
				if ( isset( $assets['js'] ) ) {
					foreach ( $assets['js'] as $handle => $data ) {
						if ( isset( $data['path'] ) ) {
							wp_enqueue_script( 'rp-' . $handle . '-js', $data['path'], $data['required'], $this->version, true );
						}
						if ( isset( $data['vars'] ) ) {
							wp_localize_script( 'rp-' . $handle . '-js', $handle . '_vars', $data['vars'] );
						}
					}
				}

				if ( isset( $assets['css'] ) ) {
					foreach ( $assets['css'] as $handle => $data ) {
						if ( isset( $data['path'] ) ) {
							wp_enqueue_style( 'rp-' . $handle . '-css', $data['path'], $data['required'], $this->version );
						}
					}
				}
			}
		}
	}

	
	public function editor_save( $post_id ) {
		$editor = $this->get_editor_name( get_post( $post_id ) );

		$is_autosave    = wp_is_post_autosave( $post_id );
		$is_revision    = wp_is_post_revision( $post_id );
		$nonce          = isset( $_REQUEST['_rp_nonce'] ) ? $_REQUEST['_rp_nonce'] : '';
		$is_valid_nonce = wp_verify_nonce( $nonce, 'rp_editor_save.' . $post_id );

		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		
		if ( 'rp_review' === get_post_type( $post_id ) ) {
			remove_action( 'save_post', array( $this, 'editor_save' ) );
			wp_update_post( array( 'ID' => $post_id, 'comment_status' => apply_filters( 'rp_cpt_comment_status', 'open' ) ) );
			add_action( 'save_post', array( $this, 'editor_save' ) );
		}

		$editor->save();
	}
}