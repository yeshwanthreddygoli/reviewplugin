<?php

class RP_Top_Reviews_Widget extends RP_Widget_Abstract {

	
	public function __construct() {
		parent::__construct(
			'rp_top_reviews_widget',
			__( 'Top Review Widget', 'wp-product-review' ),
			array(
				'description' => __( 'This widget displays the top reviews based on the rating.', 'wp-product-review' ),
			)
		);
	}

	
	public function register() {
		register_widget( 'RP_Top_Reviews_Widget' );
	}

	
	public function custom_order_by( $orderby ) {
		return 'mt1.meta_value DESC, mt2.meta_value+0 DESC';
	}

	
	public function widget( $args, $instance ) {

		$instance = parent::widget( $args, $instance );

		$reviews = new RP_Query_Model();
		$post    = array();
		if ( isset( $instance['cwp_tp_category'] ) && trim( $instance['cwp_tp_category'] ) !== '' ) {
			$array = explode( ':', $instance['cwp_tp_category'] );
			$post['category_name'] = $array[1];
			$post['taxonomy_name'] = $array[0];
		}

		$dates  = array('', '');
		if ( isset( $instance['cwp_timespan_from'] ) && ! empty( $instance['cwp_timespan_from'] ) ) {
			$dates[0] = $instance['cwp_timespan_from'];
		}
		if ( isset( $instance['cwp_timespan_to'] ) && ! empty( $instance['cwp_timespan_to'] ) ) {
			$dates[1] = $instance['cwp_timespan_to'];
		}
		$post['post_date_range'] = $dates;

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
			$instance['title'] = __( 'Top Reviews', 'wp-product-review' );
		}

		if ( ! isset( $instance['cwp_timespan_from'] ) || empty( $instance['cwp_timespan_from'] ) ) {
			$instance['cwp_timespan_from'] = '';
		}

		if ( ! isset( $instance['cwp_timespan_to'] ) || empty( $instance['cwp_timespan_to'] ) ) {
			$instance['cwp_timespan_to'] = '';
		}

		$instance = parent::form( $instance );

		include( RP_PATH . '/includes/admin/layouts/widget-admin-tpl.php' );
	}

	
	public function load_assets() {
		
	}

	
	public function load_admin_assets() {
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( RP_SLUG . '-jqueryui', RP_URL . '/assets/css/jquery-ui.css', array(), RP_LITE_VERSION );

		$deps        = array();
		$deps['js']  = array( 'jquery-ui-slider', 'jquery-ui-datepicker' );
		$deps['css'] = array( RP_SLUG . '-jqueryui' );
		return $deps;
	}

	
	public function update( $new_instance, $old_instance ) {
		$instance = parent::update( $new_instance, $old_instance );

		$instance['cwp_timespan_from'] = ( ! empty( $new_instance['cwp_timespan_from'] ) ) ? strip_tags( $new_instance['cwp_timespan_from'] ) : '';
		$instance['cwp_timespan_to'] = ( ! empty( $new_instance['cwp_timespan_to'] ) ) ? strip_tags( $new_instance['cwp_timespan_to'] ) : '';
		return $instance;
	}

}
