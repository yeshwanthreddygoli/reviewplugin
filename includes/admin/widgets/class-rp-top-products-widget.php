<?php

class RP_Top_Products_Widget extends RP_Widget_Abstract {

	
	public function __construct() {
		parent::__construct(
			'cwp_top_products_widget',
			__( 'Top Products Widget (Deprecated)', 'wp-product-review' ),
			array(
				'description' => __( 'This widget displays the top products based on their rating. This widget is deprecated and will be removed in a future release.', 'wp-product-review' ),
			)
		);
	}


	public function register() {
		register_widget( 'RP_Top_Products_Widget' );
	}

	
	public function custom_order_by( $orderby ) {
		return 'mt1.meta_value DESC, mt2.meta_value+0 DESC';
	}

	
	public function widget( $args, $instance ) {

		$instance = parent::widget( $args, $instance );

		$reviews = new RP_Query_Model();
		$post    = array();
		if ( isset( $instance['cwp_tp_category'] ) && trim( $instance['cwp_tp_category'] ) !== '' ) {
			$post['category_name'] = $instance['cwp_tp_category'];
		}

		if ( isset( $instance['cwp_timespan'] ) && trim( $instance['cwp_timespan'] ) !== '' ) {
			$min_max = explode( ',', $instance['cwp_timespan'] );
			$min     = intval( reset( $min_max ) );
			$max     = intval( end( $min_max ) );
			if ( 0 === $min && 0 === $max ) {
				$post['post_date_range_weeks'] = false;
			} else {
				$post['post_date_range_weeks'] = array( $min, $max );
			}
		}
		if ( isset( $instance['cwp_tp_post_types'] ) && ! empty( $instance['cwp_tp_post_types'] ) ) {
			$post['post_type'] = $instance['cwp_tp_post_types'];
		}
		$order           = array();
		$order['rating'] = 'DESC';

		$results = $reviews->find( $post, $instance['no_items'], array(), $order );
		if ( ! empty( $results ) ) {
			$first  = reset( $results );
			$first  = isset( $first['ID'] ) ? $first['ID'] : 0;
			$review = new RP_Review_Model( $first );

			$this->assets( $review );
		}
		
		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		}
		$template = new RP_Template();
		$template->render(
			'widget/' . $instance['cwp_tp_layout'],
			array(
				'results'      => $results,
				'title_length' => self::RESTRICT_TITLE_CHARS,
				'instance'     => $instance,
			)
		);
		echo $args['after_widget'];
	}

	
	public function form( $instance ) {
		$this->adminAssets();
		if ( ! isset( $instance['title'] ) ) {
			$instance['title'] = __( 'Top Products', 'wp-product-review' );
		}

		if ( ! isset( $instance['cwp_timespan'] ) || empty( $instance['cwp_timespan'] ) ) {
			$instance['cwp_timespan'] = '0,0';
		}

		$instance = parent::form( $instance );

		include( RP_PATH . '/includes/admin/layouts/widget-admin-tpl.php' );
	}

	
	public function load_assets() {
		
	}

	
	public function load_admin_assets() {
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_style( RP_SLUG . '-jqueryui', RP_URL . '/assets/css/jquery-ui.css', array(), RP_LITE_VERSION );

		$deps        = array();
		$deps['js']  = array( 'jquery-ui-slider' );
		$deps['css'] = array( RP_SLUG . '-jqueryui' );
		return $deps;
	}

	
	public function update( $new_instance, $old_instance ) {
		$instance = parent::update( $new_instance, $old_instance );

		$instance['cwp_timespan'] = ( ! empty( $new_instance['cwp_timespan'] ) ) ? strip_tags( $new_instance['cwp_timespan'] ) : '';
		return $instance;
	}

}
