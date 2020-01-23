<?php
/**
 * Class for functionalities related to Gutenberg.
 *
 * Defines the functions that need to be used for Gutenberg,
 * and REST router.
 *
 * @package    wp-product-review
 * @subpackage wp-product-review/includes/guteneberg
 * @author     Themeisle <friends@themeisle.com>
 */
class RP_Gutenberg {

	/**
	 * A reference to an instance of this class.
	 *
	 * @var RP_Gutenberg The one RP_Gutenberg instance.
	 */
	private static $instance;

	/**
	 * WP Product Review version.
	 *
	 * @var string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Returns an instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new RP_Gutenberg();
		}
		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {
		$plugin        = new RP();
		$this->version = $plugin->get_version();
		// Add a filter to load functions when all plugins have been loaded
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_gutenberg_scripts' ) );
		add_action( 'wp_loaded', array( $this, 'register_endpoints' ) );
		add_action( 'rest_api_init', array( $this, 'update_posts_endpoints' ) );
		add_filter( 'rest_post_query', array( $this, 'post_meta_request_params' ), 99, 2 );
		add_filter( 'rest_page_query', array( $this, 'post_meta_request_params' ), 99, 2 );
		add_filter( 'rest_rp_review_query', array( $this, 'post_meta_request_params' ), 99, 2 );
	}

	/**
	 * Enqueue editor JavaScript and CSS
	 */
	public function enqueue_gutenberg_scripts() {
		if ( RP_CACHE_DISABLED ) {
			$version = filemtime( RP_PATH . '/includes/gutenberg/build/sidebar.js' );
		} else {
			$version = $this->version;
		}

		if ( defined( 'RP_PRO_VERSION' ) ) {
			$isPro = true;
		} else {
			$isPro = false;
		}

		$model = new RP_Query_Model();
		$length = $model->rp_get_option( 'cwppos_option_nr' );

		// Enqueue the bundled block JS file
		wp_enqueue_script( 'rp-gutenberg-block-js', RP_URL . '/includes/gutenberg/build/sidebar.js', array( 'wp-i18n', 'wp-edit-post', 'wp-element', 'wp-editor', 'wp-components', 'wp-compose', 'wp-data', 'wp-plugins', 'wp-edit-post', 'wp-api' ), $version );

		wp_localize_script(
			'rp-gutenberg-block-js',
			'rpguten',
			array(
				'isPro' => $isPro,
				'path'  => RP_URL,
				'length' => $length,
			)
		);

		// Enqueue editor block styles
		wp_enqueue_style( 'rp-gutenberg-block-css', RP_URL . '/includes/gutenberg/build/sidebar.css', '', $version );
	}

	/**
	 * Hook server side rendering into render callback
	 */
	public function update_posts_endpoints() {
		register_rest_route(
			'wp-product-review',
			'/update-review',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'update_review_callback' ),
				'args'     => array(
					'id' => array(
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	/**
	 * Rest Callbackk Method
	 */
	public function update_review_callback( $data ) {
		if ( ! empty( $data['id'] ) ) {
			$review = new RP_Review_Model( $data['id'] );
			if ( $data['cwp_meta_box_check'] === 'Yes' ) {
				$review->activate();

				if ( $data['postType'] === 'rp_review' ) {
					$name = get_the_title( $data['id'] );
				} else {
					$name = isset( $data['cwp_rev_product_name'] ) ? sanitize_text_field( $data['cwp_rev_product_name'] ) : '';
				}
				$image      = isset( $data['cwp_rev_product_image'] ) ? esc_url( $data['cwp_rev_product_image'] ) : '';
				$click      = isset( $data['cwp_image_link'] ) ? strval( sanitize_text_field( $data['cwp_image_link'] ) ) : 'image';
				$template   = isset( $data['_rp_review_template'] ) ? strval( sanitize_text_field( $data['_rp_review_template'] ) ) : 'default';
				$affiliates = isset( $data['rp_links'] ) ? $data['rp_links'] : array( '' => '' );
				$price      = isset( $data['cwp_rev_price'] ) ? sanitize_text_field( $data['cwp_rev_price'] ) : 0;
				$options    = isset( $data['rp_options'] ) ? $data['rp_options'] : array();
				$pros       = isset( $data['rp_pros'] ) ? $data['rp_pros'] : array();
				$cons       = isset( $data['rp_cons'] ) ? $data['rp_cons'] : array();

				foreach ( $affiliates as $key => $option ) {
					if ( $option === '' ) {
						unset( $affiliates[ $key ] );
					}
				}

				foreach ( $options as $key => $option ) {
					// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					if ( $option['name'] === '' && $option['value'] == 0 ) {
						unset( $options[ $key ] );
					}
				}

				$review->set_name( $name );
				$review->set_image( $image );
				$review->set_click( $click );
				$review->set_template( $template );
				$review->set_links( $affiliates );
				$review->set_price( $price );
				$review->set_options( $options );
				$review->set_pros( $pros );
				$review->set_cons( $cons );
			} else {
				$review->deactivate();
			}

			return new \WP_REST_Response( array( 'message' => __( 'Review updated.', 'wp-product-review' ) ), 200 );
		}
	}

	/**
	 * Register Rest Field
	 */
	public function register_endpoints() {
		$args = array(
			'public'   => true,
		);

		$output = 'names';
		$operator = 'and';

		$post_types = get_post_types( $args, $output, $operator );

		register_rest_field(
			$post_types,
			'rp_data',
			array(
				'get_callback'    => array( $this, 'get_post_meta' ),
				'schema'          => null,
			)
		);
	}

	/**
	 * Get Post Meta Fields
	 */
	public function get_post_meta( $post ) {
		$data = array();
		$post_id = $post['id'];
		$post_type = $post['type'];
		$options = array(
			'cwp_meta_box_check',
			'cwp_rev_product_name',
			'_rp_review_template',
			'cwp_rev_product_image',
			'cwp_image_link',
			'rp_links',
			'cwp_rev_price',
			'rp_pros',
			'rp_cons',
			'rp_rating',
			'rp_options',
		);
		foreach ( $options as $option ) {
			if ( get_post_meta( $post_id, $option ) ) {
				$object = get_post_meta( $post_id, $option );
				$object = $object[0];
				$data[ $option ] = $object;
			}
		}

		return $data;
	}

	/**
	 * Allow querying posts by meta in REST API
	 */
	public function post_meta_request_params( $args, $request ) {
		$args += array(
			'meta_key'   => $request['meta_key'],
			'meta_value' => $request['meta_value'],
			'meta_query' => $request['meta_query'],
		);
		return $args;
	}

}
