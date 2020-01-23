<?php

class RP_Admin {

	
	private $plugin_name;

	
	private $version;

	
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	
	public function enqueue_styles( $hook ) {

		
		switch ( $hook ) {
			case 'toplevel_page_rp':
				wp_enqueue_style( 'wp-color-picker' );
				
				
			case 'product-review_page_rp-support':
				
				break;
			case 'post.php':
				
			case 'post-new.php':
				$wp_scripts = wp_scripts();
				wp_enqueue_style( $this->plugin_name . '-jquery-ui', sprintf( '//ajax.googleapis.com/ajax/libs/jqueryui/%s/themes/smoothness/jquery-ui.css', $wp_scripts->registered['jquery-ui-core']->ver ), array(), $this->version );
				break;
		}
	}

	
	public function enqueue_scripts( $hook ) {

		

		switch ( $hook ) {
			case 'toplevel_page_rp':
				
				break;
			case 'post.php':
				
			case 'post-new.php':
				
				break;
		}

		$this->load_review_cpt();
	}

	
	public function menu_pages() {
		add_menu_page(
			__( 'WP Product Review', 'wp-product-review' ),
			__( 'Product Review', 'wp-product-review' ),
			'manage_options',
			'rp',
			array(
				$this,
				'page_settings',
			),
			'dashicons-star-half',
			'99.87414'
		);

		add_submenu_page(
			'rp',
			__( 'Support', 'wp-product-review' ),
			__( 'Support', 'wp-product-review' ) . '<span class="dashicons dashicons-editor-help more-features-icon" style="width: 17px; height: 17px; margin-left: 4px; color: #ffca54; font-size: 17px; vertical-align: -3px;"></span>',
			'manage_options',
			'rp-support',
			array(
				$this,
				'render_support',
			)
		);
	}

	
	public function page_settings() {
		$model  = new RP_Options_Model();
		$render = new RP_Admin_Render_Controller( $this->plugin_name, $this->version );
		$render->retrive_template( 'settings', $model );
	}


	public function render_support() {
		$render = new RP_Admin_Render_Controller( $this->plugin_name, $this->version );
		$render->retrive_template( 'support' );
	}

	
	public function reset_comment_ratings() {
		$data  = $_POST['cwppos_options'];

		$nonce = $data[ count( $data ) - 1 ];
		if ( ! isset( $nonce['name'] ) ) {
			die( 'invalid nonce field' );
		}
		if ( $nonce['name'] !== 'rp_nonce_settings' ) {
			die( 'invalid nonce name' );
		}
		if ( wp_verify_nonce( $nonce['value'], 'rp_save_global_settings' ) !== 1 ) {
			die( 'invalid nonce value' );
		}

		$model = new RP_Query_Model();

		$comment_influence = intval( $model->rp_get_option( 'cwppos_infl_userreview' ) );

		if ( 0 === $comment_influence ) {
			die();
		}

		$ids    = $model->find_all_reviews();
		foreach ( $ids as $id ) {
			$review = new RP_Review_Model( $id );
			$review->update_comments_rating();
		}

		die();
	}

	
	public function update_options() {
		$model = new RP_Options_Model();
		$data  = $_POST['cwppos_options'];

		$nonce = $data[ count( $data ) - 1 ];
		if ( ! isset( $nonce['name'] ) ) {
			die( 'invalid nonce field' );
		}
		if ( $nonce['name'] !== 'rp_nonce_settings' ) {
			die( 'invalid nonce name' );
		}
		if ( wp_verify_nonce( $nonce['value'], 'rp_save_global_settings' ) !== 1 ) {
			die( 'invalid nonce value' );
		}

		foreach ( $data as $option ) {
			$model->rp_set_option( $option['name'], $option['value'] );
		}

		
		$templates = apply_filters( 'rp_review_templates', array( 'default', 'style1', 'style2' ) );
		foreach ( $templates as $template ) {
			delete_transient( '_rp_amp_css_' . str_replace( '.', '_', $this->version ) . '_' . $template );
		}
		die();
	}

	
	public function get_taxonomies() {
		check_ajax_referer( RP_SLUG, 'nonce' );

		if ( isset( $_POST['type'] ) ) {
			echo wp_send_json_success( array( 'categories' => self::get_taxonomy_and_terms_for_post_type( $_POST['type'] ) ) );
		}
		wp_die();
	}

	
	public function get_categories() {
		check_ajax_referer( RP_SLUG, 'nonce' );

		if ( isset( $_POST['type'] ) ) {
			echo wp_send_json_success( array( 'categories' => self::get_category_for_post_type( $_POST['type'] ) ) );
		}
		wp_die();
	}

	
	public static function get_taxonomy_and_terms_for_post_type( $post_type ) {
		$tax_terms = array();
		if ( $post_type ) {
			$categories = get_taxonomies(
				array( 'object_type' => array( $post_type ), 'hierarchical' => true ),
				'objects'
			);
			$tags = get_taxonomies(
				array( 'object_type' => array( $post_type ), 'hierarchical' => false ),
				'objects'
			);
			$taxonomies = array_merge( $categories, $tags );
			if ( $taxonomies ) {
				foreach ( $taxonomies as $tax ) {
					$terms = get_terms(
						$tax->name,
						array(
							'hide_empty' => false,
						)
					);
					if ( empty( $terms ) ) {
						continue;
					}
					$categories = array();
					foreach ( $terms as $term ) {
						
						$categories[ $term->taxonomy . ':' . $term->slug ] = $term->name;
					}
					$tax_terms[ $tax->label ] = $categories;
				}
			}
		}

		return $tax_terms;
	}

	
	public static function get_category_for_post_type( $post_type ) {
		$categories = array();
		if ( $post_type ) {
			$taxonomies = get_taxonomies(
				array( 'object_type' => array( $post_type ),
												 'hierarchical' => true,
				),
				'objects'
			);
			if ( $taxonomies ) {
				foreach ( $taxonomies as $tax ) {
					$terms = get_terms(
						$tax->name,
						array(
							'hide_empty' => false,
						)
					);
					if ( empty( $terms ) ) {
						continue;
					}
					foreach ( $terms as $term ) {
						$categories[ $term->slug ] = $term->name;
					}
				}
			}
		}

		return $categories;
	}

	
	public function get_additional_fields() {
		
		add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ), 10, 2 );
		add_filter( 'parse_query', array( $this, 'show_only_review_posts' ), 10 );

		
		$post_types     = apply_filters( 'rp_post_types_custom_columns', array() );
		if ( $post_types ) {
			foreach ( $post_types as $post_type ) {
				$type   = in_array( $post_type, array( 'post', 'page' ), true ) ? "{$post_type}s" : "{$post_type}_posts";
				add_filter( "manage_{$type}_columns", array( $this, 'manage_posts_columns' ), 10, 1 );
				add_action( "manage_{$type}_custom_column", array( $this, 'manage_posts_custom_column' ), 10, 2 );
				add_action( "manage_edit-{$post_type}_sortable_columns", array( $this, 'sort_posts_custom_column' ), 10, 1 );
			}
		}

		$this->get_additional_fields_for_cpt();
	}


	public function restrict_manage_posts( $post_type, $which ) {
		$post_types     = apply_filters( 'rp_post_types_custom_filter', array( 'post', 'page' ) );
		if ( ! $post_types || ! in_array( $post_type, $post_types, true ) ) {
			return;
		}

		echo "<select name='rp_filter' id='rp_filter' class='postform'>";
		echo "<option value=''>" . __( 'Show All', 'wp-product-review' ) . '</option>';
		$selected   = isset( $_REQUEST['rp_filter'] ) && 'only-rp' === $_REQUEST['rp_filter'] ? 'selected' : '';
		echo "<option value='only-rp' $selected>" . __( 'Show only Reviews', 'wp-product-review' ) . '</option>';
		echo '</select>';
	}

	
	public function show_only_review_posts( $query ) {
		if ( ! ( is_admin() && $query->is_main_query() ) ) {
			return $query;
		}

		if ( ! isset( $_REQUEST['rp_filter'] ) || 'only-rp' !== $_REQUEST['rp_filter'] ) {
			return $query;
		}

		$post_types     = apply_filters( 'rp_post_types_custom_filter', array( 'post', 'page' ) );
		if ( ! in_array( $query->query['post_type'], $post_types, true ) ) {
			return $query;
		}

		$query->query_vars['meta_query'] = array(
			array(
				'field'     => 'cwp_meta_box_check',
				'value'     => 'Yes',
				'compare'   => '=',
				'type'      => 'CHAR',
			),
		);

		return $query;
	}

	
	public function manage_posts_columns( $columns ) {
		$columns['rp_review']    = __( 'Review Rating', 'wp-product-review' );
		return $columns;
	}

	
	public function sort_posts_custom_column( $columns ) {
		$columns['rp_review'] = 'rp_review';
		return $columns;
	}
	
	public function manage_posts_custom_column( $column, $id ) {
		switch ( $column ) {
			case 'rp_review':
				$model = new RP_Review_Model( $id );
				echo $model->get_rating();
				break;
		}
	}

	
	public function load_review_cpt() {
		$current_screen = get_current_screen();

		if ( ! isset( $current_screen->id ) ) {
			return;
		}
		if ( $current_screen->id !== 'rp_review' ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name . '-cpt-js',
			RP_URL . '/assets/js/cpt.js',
			array(
				'jquery',
			),
			$this->version
		);

		wp_localize_script(
			$this->plugin_name . '-cpt-js',
			'rp',
			array(
				'i10n' => array(
					'title_placeholder' => __( 'Enter Review Title', 'wp-product-review' ),
				),
			)
		);
	}

	
	private function get_additional_fields_for_cpt() {
		$model = new RP_Query_Model();
		if ( 'yes' !== $model->rp_get_option( 'rp_cpt' ) ) {
			return;
		}

		add_filter( 'manage_rp_review_posts_columns', array( $this, 'manage_cpt_columns' ), 10, 1 );
		add_action( 'manage_rp_review_posts_custom_column', array( $this, 'manage_cpt_custom_column' ), 10, 2 );
		add_filter( 'manage_edit-rp_review_sortable_columns', array( $this, 'sort_cpt_custom_column' ), 10, 1 );
		add_action( 'pre_get_posts', array( $this, 'sort_cpt_custom_column_order') );
	}

	
	public function manage_cpt_columns( $columns ) {
		$custom     = array(
			'rp_price' => __( 'Product Price', 'wp-product-review' ),
			'rp_rating' => __( 'Rating', 'wp-product-review' ),
		);

		
		return array_slice( $columns, 0, -1, true ) + $custom + array_slice( $columns, -1, null, true );
	}

	
	public function manage_cpt_custom_column( $column, $id ) {
		switch ( $column ) {
			case 'rp_price':
				$model = new RP_Review_Model( $id );
				echo $model->get_price();
				break;
			case 'rp_rating':
				$model = new RP_Review_Model( $id );
				
				add_filter(
					'rp_rating', function( $rating, $id ) {
					update_post_meta( $id, '_rp_rating_num_temp', $rating );
					return $rating;
					}, 10, 2
				);
				echo rp_layout_get_rating( $model, 'stars', '' );
				break;
		}
	}

	
	public function sort_cpt_custom_column( $columns ) {
		$columns['rp_rating'] = 'rp_rating_num';
		return $columns;
	}

	
	public function sort_cpt_custom_column_order( $query ) {
		if ( ! is_admin() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		switch ( $orderby ) {
			case 'rp_rating_num':
				$query->set( 'meta_key', '_rp_rating_num_temp' );
				$query->set( 'orderby', 'meta_value_num' );
				break;
		}
	}


	
	public function settings_section_upsell( $section ) {
		if ( 'general' === $section ) {
			echo '<label class="rp-upsell-label"> You can display the review using the <b>[P_REVIEW]</b> shortcode. You can read more about it <a href="https://docs.themeisle.com/article/449-wp-product-review-shortcode-documentation" target="_blank">here</a></label>';
		}
	}

	
	public function add_image_size() {
		add_image_size( 'rp-widget', 50, 50 );
	}

	
	public function on_activation( $plugin ) {
		if ( defined( 'TI_UNIT_TESTING' ) ) {
			return;
		}

		if ( $plugin === RP_BASENAME ) {
			wp_redirect( admin_url( 'admin.php?page=rp-support&tab=help#shortcode' ) );
			exit();
		}
	}


}
