<?php

class RP_Editor_Model extends RP_Model_Abstract {


	public $post;

	
	public $review;


	private $previous;


	private $template_to_use = 'editor-default';


	public function __construct( $post ) {
		parent::__construct();

		if ( $post instanceof WP_Post ) {
			$this->post   = $post;
			$this->review = new RP_Review_Model( $this->post->ID );
		} else {
			$this->logger->error( 'No WP_Post provided = ' . var_export( $post, true ) );
		}
		$previous = $this->rp_get_option( 'last_review' );
		if ( ! empty( $previous ) ) {
			$this->previous = new RP_Review_Model( $previous );
		}
	}

	
	public function get_template() {
		return $this->template_to_use;
	}


	public function get_value( $key ) {
		switch ( true ) {
			case ( $key === 'rp-editor-button-text' ):
			case ( $key === 'rp-editor-button-link' ):
				if ( $this->review->is_active() ) {
					$links = $this->review->get_links();
					if ( ! empty( $links ) ) {
						if ( $key === 'rp-editor-button-link' ) {
							$values = array_values( $links );
						} else {
							$values = array_keys( $links );
						}

						return isset( $values[0] ) ? $values[0] : '';
					}
				} else {
					if ( ! empty( $this->previous ) ) {
						$links = $this->previous->get_links();
						if ( ! empty( $links ) ) {
							if ( $key === 'rp-editor-button-link' ) {
								$values = array_values( $links );
							} else {
								$values = array_keys( $links );
							}

							return isset( $values[0] ) ? $values[0] : '';
						}
					}
				}

				return '';
				break;
			case ( strpos( $key, 'rp-option-name-' ) !== false ):
			case ( strpos( $key, 'rp-option-value-' ) !== false ):
				$options = array();
				if ( $this->review->is_active() ) {
					$options = $this->review->get_options();
				} else {
					if ( ! empty( $this->previous ) ) {
						$options = $this->previous->get_options();
					}
				}
				$first_key = key( $options );
				$parts     = explode( '-', $key );
				$index     = $parts[ count( $parts ) - 1 ];
				$index     = intval( $index ) - ( $first_key === 0 ? 1 : 0 );
				$type      = $parts[ count( $parts ) - 2 ];

				return isset( $options[ $index ] ) ? $options[ $index ][ $type ] : '';
				break;
			case ( $key === 'rp-editor-link' ):
				if ( $this->review->is_active() ) {
					return $this->review->get_click();
				} else {
					if ( ! empty( $this->previous ) ) {
						return $this->previous->get_click();
					}
				}

				return 'image';
				break;
			default:
				return '';
		}// End switch().
	}

	
	public function save() {
		$data = $_POST;

		do_action( 'rp_before_save', $this->post, $data );
		$status = isset( $data['rp-review-status'] ) ? strval( $data['rp-review-status'] ) : 'no';

		$review = $this->review;
		if ( $status === 'yes' ) {

			$review->activate();
			$name  = isset( $data['rp-editor-product-name'] ) ? sanitize_text_field( $data['rp-editor-product-name'] ) : '';
			$image = isset( $data['rp-editor-image'] ) ? esc_url( $data['rp-editor-image'] ) : '';
			$click = isset( $data['rp-editor-link'] ) ? strval( sanitize_text_field( $data['rp-editor-link'] ) ) : 'image';
			$template = isset( $data['rp-review-template'] ) ? strval( sanitize_text_field( $data['rp-review-template'] ) ) : 'default';

			
			$link           = isset( $data['rp-editor-button-text'] ) ? strval( sanitize_text_field( $data['rp-editor-button-text'] ) ) : '';
			$text           = isset( $data['rp-editor-button-link'] ) ? strval( esc_url( $data['rp-editor-button-link'] ) ) : '';
			$link2          = isset( $data['rp-editor-button-text-2'] ) ? strval( sanitize_text_field( $data['rp-editor-button-text-2'] ) ) : '';
			$text2          = isset( $data['rp-editor-button-link-2'] ) ? strval( esc_url( $data['rp-editor-button-link-2'] ) ) : '';
			$price          = isset( $data['rp-editor-price'] ) ? sanitize_text_field( $data['rp-editor-price'] ) : 0;
			$options_names  = isset( $data['rp-editor-options-name'] ) ? $data['rp-editor-options-name'] : array();
			$options_values = isset( $data['rp-editor-options-value'] ) ? $data['rp-editor-options-value'] : array();
			$pros           = isset( $data['rp-editor-pros'] ) ? $data['rp-editor-pros'] : array();
			$cons           = isset( $data['rp-editor-cons'] ) ? $data['rp-editor-cons'] : array();
			$options        = array();
			foreach ( $options_names as $k => $op_name ) {
				if ( ! empty( $op_name ) ) {
					$options[ $k ] = array(
						'name'  => sanitize_text_field( $op_name ),
						'value' => sanitize_text_field( isset( $options_values[ $k ] ) ? ( empty( $options_values[ $k ] ) ? 0 : $options_values[ $k ] ) : 0 ),
					);

				}
			}
			if ( is_array( $pros ) ) {
				$pros = array_map( 'sanitize_text_field', $pros );
			} else {
				$pros = array();
			}
			if ( is_array( $cons ) ) {
				$cons = array_map( 'sanitize_text_field', $cons );
			} else {
				$cons = array();
			}
			$review->set_name( $name );
			$review->set_template( $template );
			$review->set_image( $image );
			if ( $click === 'image' || $click === 'link' ) {
				$review->set_click( $click );

			}
			$links = array();
			if ( ! empty( $link ) && ! empty( $text ) ) {
				$links[ $link ] = $text;
			}
			if ( ! empty( $link2 ) && ! empty( $text2 ) ) {
				$links[ $link2 ] = $text2;
			}
			$review->set_links( $links );
			$review->set_price( $price );
			$review->set_pros( $pros );
			$review->set_cons( $cons );
			$review->set_options( $options );

			$custom           = isset( $data['rp-editor-review-type-field'] ) ? $data['rp-editor-review-type-field'] : array();
			if ( is_array( $custom ) ) {
				$custom = array_map( 'sanitize_text_field', $custom );
			} else {
				$custom = array();
			}

			$custom_fields  = array();
			if ( $custom ) {
				foreach ( $custom as $field_name ) {
					$custom_fields[ $field_name ] = sanitize_text_field( $data[ $field_name ] );
				}
			}

			$review->set_type( $data['rp-editor-review-type'] );
			$review->set_custom_fields( $custom_fields );

			$this->rp_set_option( 'last_review', $review->get_ID() );
		} else {
			$review->deactivate();
		}// End if().
		do_action( 'rp_after_save', $this->post, $data );
	}

	
	public function get_assets() {
		$assets = array(
			'css' => array(
				'dashboard-styles' => array(
					'path'     => RP_URL . '/assets/css/dashboard_styles.css',
					'required' => array(),
				),
				'default-editor'   => array(
					'path'     => RP_URL . '/assets/css/editor.css',
					'required' => array(),
				),
			),
			'js'  => array(
				'editor' => array(
					'path'     => RP_URL . '/assets/js/admin-review.js',
					'required' => array( 'jquery' ),
					'vars'     => array(
						'image_title'  => __( 'Add a product image to the review', 'wp-product-review' ),
						'image_button' => __( 'Attach the image', 'wp-product-review' ),
					),
				),
			),
		);

		return $assets;
	}
}
