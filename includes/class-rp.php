<?php

class RP {

	
	protected $loader;

	
	protected $plugin_name;

	
	protected $version;

	
	public function __construct() {
		$this->plugin_name = 'RP';
	

		$this->load_dependencies();
		$this->set_locale();
		$this->define_common_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	
	private function load_dependencies() {
		$this->loader = new RP_Loader();
	}

	
	private function set_locale() {
		$plugin_i18n = new RP_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	
	private function define_common_hooks() {
		$this->loader->add_action( 'init', $this, 'register_cpt', 11 );
	}

	
	private function define_admin_hooks() {

		$plugin_admin = new RP_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'menu_pages' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_update_options', $plugin_admin, 'update_options' );
		$this->loader->add_action( 'wp_ajax_get_taxonomies', $plugin_admin, 'get_taxonomies' );
		$this->loader->add_action( 'wp_ajax_get_categories', $plugin_admin, 'get_categories' );
		$this->loader->add_action( 'wp_ajax_reset_comment_ratings', $plugin_admin, 'reset_comment_ratings' );
		$this->loader->add_action( 'load-edit.php', $plugin_admin, 'get_additional_fields' );
		$this->loader->add_action( 'RP_settings_section_upsell', $plugin_admin, 'settings_section_upsell', 10, 1 );
		$this->loader->add_action( 'after_setup_theme', $plugin_admin, 'add_image_size' );
		$this->loader->add_action( 'wp_ajax_get_categories', $plugin_admin, 'get_categories' );
		$this->loader->add_action( 'activated_plugin', $plugin_admin, 'on_activation', 10, 1 );

		$plugin_editor = new RP_Editor( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $plugin_editor, 'set_editor' );
		add_action( 'save_post', array( $plugin_editor, 'editor_save' ) );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_editor, 'load_assets' );

		$plugin_widget_latest = new RP_Latest_Products_Widget();
		$this->loader->add_action( 'widgets_init', $plugin_widget_latest, 'register' );

		$plugin_widget_old_top = new RP_Top_Products_Widget();
		$this->loader->add_action( 'widgets_init', $plugin_widget_old_top, 'register' );

		$plugin_widget_top = new RP_Top_Reviews_Widget();
		$this->loader->add_action( 'widgets_init', $plugin_widget_top, 'register' );

	}

	
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	
	public function get_version() {
		return $this->version;
	}

	
	private function define_public_hooks() {

		$plugin_public = new RP_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'comment_post', $plugin_public, 'save_comment_fields', 1 );

		if ( is_admin() ) {
			return;
		}
		$this->loader->add_action( 'wp', $plugin_public, 'setup_post' );
		$this->loader->add_action( 'wp', $plugin_public, 'amp_support', 11 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'load_review_assets' );
		$this->loader->add_action( 'comment_form_logged_in_after', $plugin_public, 'add_comment_fields' );
		$this->loader->add_action( 'comment_form_after_fields', $plugin_public, 'add_comment_fields' );
		$this->loader->add_filter( 'comment_text', $plugin_public, 'show_comment_ratings' );
		$current_theme = wp_get_theme();
		if ( $current_theme->get( 'Name' ) !== 'Bookrev' && $current_theme->get( 'Name' ) !== 'Book Rev Lite' ) {

			$this->loader->add_filter( 'the_content', $plugin_public, 'display_on_front' );
		}

		$this->loader->add_filter( 'rp_rating_circle_bar_styles', $plugin_public, 'rating_circle_bar_styles', 10, 2 );
		$this->loader->add_filter( 'rp_rating_circle_fill_styles', $plugin_public, 'rating_circle_fill_styles', 10, 2 );
		$this->loader->add_action( 'rp_load_template_css', $plugin_public, 'load_template_css', 10, 1 );
	}

	
	public function get_loader() {
		return $this->loader;
	}

	
	public function run() {
		$this->loader->run();
	}
	
	public function register_cpt() {
		$model = new RP_Query_Model();
		if ( 'yes' !== $model->rp_get_option( 'rp_cpt' ) ) {
			return;
		}

		$labels = array(
			'name'               => _x( 'Reviews', 'post type general name', 'wp-product-review' ),
			'singular_name'      => _x( 'Review', 'post type singular name', 'wp-product-review' ),
			'menu_name'          => _x( 'Reviews', 'admin menu', 'wp-product-review' ),
			'name_admin_bar'     => _x( 'Review', 'add new on admin bar', 'wp-product-review' ),
			'add_new'            => _x( 'Add New', 'review', 'wp-product-review' ),
			'add_new_item'       => __( 'Add New Review', 'wp-product-review' ),
			'new_item'           => __( 'New Review', 'wp-product-review' ),
			'edit_item'          => __( 'Edit Review', 'wp-product-review' ),
			'view_item'          => __( 'View Review', 'wp-product-review' ),
			'all_items'          => __( 'All Reviews', 'wp-product-review' ),
			'search_items'       => __( 'Search Reviews', 'wp-product-review' ),
			'parent_item_colon'  => __( 'Parent Review:', 'wp-product-review' ),
			'not_found'          => __( 'No review found.', 'wp-product-review' ),
			'not_found_in_trash' => __( 'No reviews found in Trash.', 'wp-product-review' ),
		);
		$args   = array(
			'labels'             => $labels,
			'description'        => __( 'Reviews from WP Product Review', 'wp-product-review' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_in_nav_menus' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'has_archive'        => true,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
			'taxonomies'    => array( 'rp_category' ),
			'can_export'    => true,
			'capability_type'    => 'post',
			'show_in_rest'          => true,
			'rest_base'             => 'rp_review',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);
		register_post_type( 'rp_review', $args );

		register_taxonomy(
			'rp_category',
			'rp_review',
			array(
				'hierarchical'          => true,
				'labels'                => array(
					'name'                => __( 'Review Category', 'wp-product-review' ),
					'singular_name'       => __( 'Review Category', 'wp-product-review' ),
					'search_items'        => __( 'Search Review Categories', 'wp-product-review' ),
					'all_items'           => __( 'All Review Categories', 'wp-product-review' ),
					'parent_item'         => __( 'Parent Review Category', 'wp-product-review' ),
					'parent_item_colon'   => __( 'Parent Review Category', 'wp-product-review' ) . ':',
					'edit_item'           => __( 'Edit Review Category', 'wp-product-review' ),
					'update_item'         => __( 'Update Review Category', 'wp-product-review' ),
					'add_new_item'        => __( 'Add New Review Category', 'wp-product-review' ),
					'new_item_name'       => __( 'New Review Category', 'wp-product-review' ),
					'menu_name'           => __( 'Review Categories', 'wp-product-review' ),
				),
				'show_admin_column'     => true,
				'public'                => true,
				'show_in_menu'          => true,
				'rewrite'               => array( 'slug' => 'rpcategory', 'with_front' => true ),
			)
		);

		register_taxonomy(
			'rp_tag',
			'rp_review',
			array(
				'hierarchical'          => false,
				'labels'                => array(
					'name'                => __( 'Review Tag', 'wp-product-review' ),
					'singular_name'       => __( 'Review Tag', 'wp-product-review' ),
					'search_items'        => __( 'Search Review Tags', 'wp-product-review' ),
					'all_items'           => __( 'All Review Tags', 'wp-product-review' ),
					'parent_item'         => __( 'Parent Review Tag', 'wp-product-review' ),
					'parent_item_colon'   => __( 'Parent Review Tag', 'wp-product-review' ) . ':',
					'edit_item'           => __( 'Edit Review Tag', 'wp-product-review' ),
					'update_item'         => __( 'Update Review Tag', 'wp-product-review' ),
					'add_new_item'        => __( 'Add New Review Tag', 'wp-product-review' ),
					'new_item_name'       => __( 'New Review Tag', 'wp-product-review' ),
					'menu_name'           => __( 'Review Tags', 'wp-product-review' ),
				),
				'show_admin_column'     => true,
				'public'                => true,
				'show_in_menu'          => true,
				'rewrite'               => array( 'slug' => 'rptag', 'with_front' => true ),
			)
		);

		flush_rewrite_rules();

	}
}
