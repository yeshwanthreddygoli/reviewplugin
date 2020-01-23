<?php

class RP_Latest_Products_Widget extends RP_Widget_Abstract {

	
	public function __construct() {
		parent::__construct(
			'cwp_latest_products_widget',
			__( 'Latest Reviews Widget', 'wp-product-review' ),
			array(
				'description' => __( 'This widget displays the latest reviews based on their rating.', 'wp-product-review' ),
			)
		);
	}

	
	public function register() {
		register_widget( 'RP_Latest_Products_Widget' );
	}

	
	public function widget( $args, $instance ) {
		$instance = parent::widget( $args, $instance );

		$reviews = new RP_Query_Model();
		$post    = array();
		if ( isset( $instance['cwp_tp_category'] ) && trim( $instance['cwp_tp_category'] ) !== '' ) {
			$post['category_name'] = $instance['cwp_tp_category'];
		}
		if ( isset( $instance['cwp_tp_post_types'] ) && ! empty( $instance['cwp_tp_post_types'] ) ) {
			$post['post_type'] = $instance['cwp_tp_post_types'];
		}
		$order         = array();
		$order['date'] = 'DESC';

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
			$instance['title'] = __( 'Latest Review', 'wp-product-review' );
		}

		$instance = parent::form( $instance );

		include( RP_PATH . '/includes/admin/layouts/widget-admin-tpl.php' );
	}

	
	public function load_assets() {
		
	}

	
	public function load_admin_assets() {
		
	}
}
