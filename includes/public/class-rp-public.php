<?php

class rp_Public {

	
	private $plugin_name;

	
	private $version;

	
	private $review;

	
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	
	public function setup_post() {

		global $post;
		$this->review = new RP_Review_Model( ! empty( $post ) ? $post->ID : 0 );
	}

	
	public function load_review_assets( $review = null ) {
		$load = false;
		if ( ! empty( $review ) ) {
			if ( $review->is_active() ) {

				$this->review = $review;
				$this->amp_support();
				$load = true;
			}
		} else {
			$review = $this->review;
			if ( empty( $review ) ) {
				$load = false;
			} elseif ( $review->is_active() ) {
				$load = true;
			}
		}

		if ( ! $load ) {
			return;
		}

		if ( $review->rp_get_option( 'cwppos_lighbox' ) === 'no' ) {
			wp_enqueue_script( $this->plugin_name . '-lightbox-js', RP_URL . '/assets/js/lightbox.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_style( $this->plugin_name . '-lightbox-css', RP_URL . '/assets/css/lightbox.css', array(), $this->version );
		}

		if ( $review->rp_get_option( 'cwppos_show_userreview' ) === 'yes' ) {
			$scale      = $review->rp_get_option( 'rp_use_5_rating_scale' );
			if ( empty( $scale ) ) {
				$scale  = 10;
			}
			$scale      = 10 * ( 10 / $scale );

			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'jquery-touch-punch' );
			wp_enqueue_script(
				$this->plugin_name . '-frontpage-js',
				RP_URL . '/assets/js/main.js',
				array(
					'jquery-ui-slider',
				),
				$this->version,
				true
			);
			wp_localize_script( $this->plugin_name . '-frontpage-js', 'rp_config', array( 'scale' => $scale ) );

			wp_enqueue_style( $this->plugin_name . 'jqueryui', RP_URL . '/assets/css/jquery-ui.css', array(), $this->version );
			wp_enqueue_style( $this->plugin_name . 'comments', RP_URL . '/assets/css/comments.css', array(), $this->version );
		}

		$this->load_template_css( $review );
	}

	
	function load_template_css( $review = null ) {
		if ( empty( $review ) ) {
			$review = $this->review;
		}

		wp_enqueue_style( $this->plugin_name . '-' . $review->get_template() . '-stylesheet', RP_URL . '/assets/css/' . $review->get_template() . '.css', array(), $this->version );
		wp_enqueue_style(
			$this->plugin_name . '-percentage-circle',
			RP_URL . '/assets/css/circle.css',
			array(),
			$this->version
		);
		wp_enqueue_style(
			$this->plugin_name . '-common',
			RP_URL . '/assets/css/common.css',
			array( 'dashicons' ),
			$this->version
		);

		$icon = $review->rp_get_option( 'cwppos_change_bar_icon' );

		
		if ( defined( 'RP_PRO_VERSION' ) && version_compare( RP_PRO_VERSION, '2.4', '<' ) && 'style1' !== $review->get_template() && ! empty( $icon ) ) {
			wp_enqueue_style( $this->plugin_name . 'fa', RP_URL . '/assets/css/font-awesome.min.css', array(), $this->version );
			wp_enqueue_style( $this->plugin_name . '-fa-compat', RP_URL . '/assets/css/fontawesome-compat.css', array(), $this->version );
		}

		$style = $this->generate_styles();

		$style = apply_filters( 'rp_global_style', $style );

		wp_add_inline_style( $this->plugin_name . '-common', $style );
	}

	
	public function amp_support() {
		if ( ! $this->review->is_active() ) {
			return;
		}
		if ( ! function_exists( 'ampforwp_is_amp_endpoint' ) || ! function_exists( 'is_amp_endpoint' ) ) {
			return;
		}
		if ( ! ampforwp_is_amp_endpoint() || ! is_amp_endpoint() ) {
			return;
		}

		$model = new RP_Query_Model();

		$icon = $model->rp_get_option( 'cwppos_change_bar_icon' );

		if ( 'yes' === $model->rp_get_option( 'rp_amp' ) ) {
			add_filter( 'rp_review_option_rating_css', array( $this, 'amp_width_support' ), 99, 2 );
			add_action( 'amp_post_template_css', array( $this, 'amp_styles' ), 999 );
		}
	}

	
	public function generate_styles() {

		$review             = new RP_Review_Model();
		$conditional_styles = '';
		if ( $review->rp_get_option( 'cwppos_show_icon' ) === 'yes' ) {
			$adverb         = is_rtl() ? 'after' : 'before';
			$direction      = is_rtl() ? 'left' : 'right';
			$conditional_styles .= '
                div.affiliate-button a span:' . $adverb . ', div.affiliate-button a:hover span:' . $adverb . ' {
					font-family: "dashicons";
                    content: "\f174";
					padding-' . $direction . ': 5px
                } 
                ';
		}

		if ( $review->rp_get_option( 'cwppos_show_userreview' ) === 'yes' ) {
			$conditional_styles .= '
                .commentlist .comment-body p {
                    clear: left;
                }
                ';
		}

		$style = '                   
                    .review-wu-grade .rp-c100,
                     .review-grade-widget .rp-c100 {
                        background-color: ' . $review->rp_get_option( 'cwppos_rating_chart_default' ) . ';
                    }
                    
                    .review-wu-grade .rp-c100.rp-weak span,
                     .review-grade-widget .rp-c100.rp-weak span {
                        color: ' . $review->rp_get_option( 'cwppos_rating_weak' ) . ';
                    }
                    
                    .review-wu-grade .rp-c100.rp-weak .rp-fill,
                    .review-wu-grade .rp-c100.rp-weak .rp-bar,
                     .review-grade-widget .rp-c100.rp-weak .rp-fill,
                    .review-grade-widget .rp-c100.rp-weak .rp-bar {
                        border-color: ' . $review->rp_get_option( 'cwppos_rating_weak' ) . ';
                    }
                    
                    .user-comments-grades .comment-meta-grade-bar.rp-weak .comment-meta-grade {
                        background: ' . $review->rp_get_option( 'cwppos_rating_weak' ) . ';
                    }
                    
                    #review-statistics .review-wu-grade .rp-c100.rp-not-bad span,
                     .review-grade-widget .rp-c100.rp-not-bad span {
                        color: ' . $review->rp_get_option( 'cwppos_rating_notbad' ) . ';
                    }
                    
                    .review-wu-grade .rp-c100.rp-not-bad .rp-fill,
                    .review-wu-grade .rp-c100.rp-not-bad .rp-bar,
                     .review-grade-widget .rp-c100.rp-not-bad .rp-fill,
                    .review-grade-widget .rp-c100.rp-not-bad .rp-bar {
                        border-color: ' . $review->rp_get_option( 'cwppos_rating_notbad' ) . ';
                    }
                    
                    .user-comments-grades .comment-meta-grade-bar.rp-not-bad .comment-meta-grade {
                        background: ' . $review->rp_get_option( 'cwppos_rating_notbad' ) . ';
                    }
                    
                    .review-wu-grade .rp-c100.rp-good span,
                     .review-grade-widget .rp-c100.rp-good span {
                        color: ' . $review->rp_get_option( 'cwppos_rating_good' ) . ';
                    }
                    
                    .review-wu-grade .rp-c100.rp-good .rp-fill,
                    .review-wu-grade .rp-c100.rp-good .rp-bar,
                     .review-grade-widget .rp-c100.rp-good .rp-fill,
                    .review-grade-widget .rp-c100.rp-good .rp-bar {
                        border-color: ' . $review->rp_get_option( 'cwppos_rating_good' ) . ';
                    }
                    
                    .user-comments-grades .comment-meta-grade-bar.rp-good .comment-meta-grade {
                        background: ' . $review->rp_get_option( 'cwppos_rating_good' ) . ';
                    }
                    
                    .review-wu-grade .rp-c100.rp-very-good span,
                     .review-grade-widget .rp-c100.rp-very-good span {
                        color: ' . $review->rp_get_option( 'cwppos_rating_very_good' ) . ';
                    }
                    
                    .review-wu-grade .rp-c100.rp-very-good .rp-fill,
                    .review-wu-grade .rp-c100.rp-very-good .rp-bar,
                     .review-grade-widget .rp-c100.rp-very-good .rp-fill,
                    .review-grade-widget .rp-c100.rp-very-good .rp-bar {
                        border-color: ' . $review->rp_get_option( 'cwppos_rating_very_good' ) . ';
                    }
                    
                    .user-comments-grades .comment-meta-grade-bar.rp-very-good .comment-meta-grade {
                        background: ' . $review->rp_get_option( 'cwppos_rating_very_good' ) . ';
                    }
                    
                    #review-statistics .review-wu-bars ul.rp-weak li.colored {
                        background: ' . $review->rp_get_option( 'cwppos_rating_weak' ) . ';
                        color: ' . $review->rp_get_option( 'cwppos_rating_weak' ) . ';
                    }
                    
                    #review-statistics .review-wu-bars ul.rp-not-bad li.colored {
                        background: ' . $review->rp_get_option( 'cwppos_rating_notbad' ) . ';
                        color: ' . $review->rp_get_option( 'cwppos_rating_notbad' ) . ';
                    }
                    
                    #review-statistics .review-wu-bars ul.rp-good li.colored {
                        background: ' . $review->rp_get_option( 'cwppos_rating_good' ) . ';
                        color: ' . $review->rp_get_option( 'cwppos_rating_good' ) . ';
                    }
                    
                    #review-statistics .review-wu-bars ul.rp-very-good li.colored {
                        background: ' . $review->rp_get_option( 'cwppos_rating_very_good' ) . ';
                        color: ' . $review->rp_get_option( 'cwppos_rating_very_good' ) . ';
                    }
                    
                    #review-statistics .review-rp-up div.cwpr-review-top {
                        border-top: ' . $review->rp_get_option( 'cwppos_reviewboxbd_width' ) . 'px solid ' . $review->rp_get_option( 'cwppos_reviewboxbd_color' ) . ';
                    }
            
                    .user-comments-grades .comment-meta-grade-bar,
                    #review-statistics .review-wu-bars ul li {
                        background: ' . $review->rp_get_option( 'cwppos_rating_default' ) . ';
                        color: ' . $review->rp_get_option( 'cwppos_rating_default' ) . ';
                    }
           
            
                    #review-statistics .review-wrap-up .review-wu-right ul li, 
                    #review-statistics .review-wu-bars h3, 
                    .review-wu-bars span, 
                    #review-statistics .review-wrap-up .cwpr-review-top .cwp-item-category a {
                        color: ' . $review->rp_get_option( 'cwppos_font_color' ) . ';
                    }
            
                    #review-statistics .review-wrap-up .review-wu-right .pros h2 {
                        color: ' . $review->rp_get_option( 'cwppos_pros_color' ) . ';
                    }
            
                    #review-statistics .review-wrap-up .review-wu-right .cons h2 {
                        color: ' . $review->rp_get_option( 'cwppos_cons_color' ) . ';
                    }
                
                    div.affiliate-button a {
                        border: 2px solid ' . $review->rp_get_option( 'cwppos_buttonbd_color' ) . ';
                    }
            
                    div.affiliate-button a:hover {
                        border: 2px solid ' . $review->rp_get_option( 'cwppos_buttonbh_color' ) . ';
                    }
            
                    div.affiliate-button a {
                        background: ' . $review->rp_get_option( 'cwppos_buttonbkd_color' ) . ';
                    }
            
                    div.affiliate-button a:hover {
                        background: ' . $review->rp_get_option( 'cwppos_buttonbkh_color' ) . ';
                    }
            
                    div.affiliate-button a span {
                        color: ' . $review->rp_get_option( 'cwppos_buttontxtd_color' ) . ';
                    }
            
                    div.affiliate-button a:hover span {
                        color: ' . $review->rp_get_option( 'cwppos_buttontxth_color' ) . ';
                    }
                    
                    ' . $conditional_styles . '
               
            ';

		
		$style .= ' 
			.rp-template-1 .rp-review-grade-option-rating.rp-very-good.rtl,
			.rp-template-2 .rp-review-grade-option-rating.rp-very-good.rtl {
					background: ' . $review->rp_get_option( 'cwppos_rating_very_good' ) . ';
			}
			.rp-template-1 .rp-review-grade-option-rating.rp-good.rtl,
			.rp-template-2 .rp-review-grade-option-rating.rp-good.rtl {
					background: ' . $review->rp_get_option( 'cwppos_rating_good' ) . ';
			}
			.rp-template-1 .rp-review-grade-option-rating.rp-not-bad.rtl,
			.rp-template-2 .rp-review-grade-option-rating.rp-not-bad.rtl {
					background: ' . $review->rp_get_option( 'cwppos_rating_notbad' ) . ';
			}
			.rp-template-1 .rp-review-grade-option-rating.rp-weak.rtl,
			.rp-template-2 .rp-review-grade-option-rating.rp-weak.rtl {
					background: ' . $review->rp_get_option( 'cwppos_rating_weak' ) . ';
			}

			.rp-template-1    .rp-review-grade-option .rp-very-good {
					background: ' . ( is_rtl() ? $review->rp_get_option( 'cwppos_rating_default' ) : $review->rp_get_option( 'cwppos_rating_very_good' ) ) . ';
			}
			.rp-template-2    .rp-review-rating .rp-very-good {
					background: ' . $review->rp_get_option( 'cwppos_rating_very_good' ) . ';
			} 
			.rp-template-1    .rp-review-grade-option .rp-good {
					background: ' . ( is_rtl() ? $review->rp_get_option( 'cwppos_rating_default' ) : $review->rp_get_option( 'cwppos_rating_good' ) ) . ';
			}
			.rp-template-2     .rp-review-rating  .rp-good {
					background: ' . $review->rp_get_option( 'cwppos_rating_good' ) . ';
			} 
			.rp-template-1    .rp-review-grade-option .rp-not-bad {
					background: ' . ( is_rtl() ? $review->rp_get_option( 'cwppos_rating_default' ) : $review->rp_get_option( 'cwppos_rating_notbad' ) ) . ';
			}
			.rp-template-2    .rp-review-rating .rp-not-bad {
					background: ' . $review->rp_get_option( 'cwppos_rating_notbad' ) . ';
			}
			 
			.rp-template-1    .rp-review-grade-option .rp-weak {
					background: ' . ( is_rtl() ? $review->rp_get_option( 'cwppos_rating_default' ) : $review->rp_get_option( 'cwppos_rating_weak' ) ) . ';
			}
			.rp-template-2    .rp-review-rating  .rp-weak {
					background: ' . $review->rp_get_option( 'cwppos_rating_weak' ) . ';
			}  
			.rp-template-1    .rp-review-grade-option .rp-default,
			.rp-template-2   .rp-review-rating  .rp-default{
					background: ' . $review->rp_get_option( 'cwppos_rating_default' ) . ';
			} 
			
			
			
			.rp-template-1    .rp-review-grade-number .rp-very-good,
			.rp-template-1    .rp-review-stars .rp-very-good,
			.rp-template-2    .rp-review-option-rating .rp-very-good{
					color: ' . $review->rp_get_option( 'cwppos_rating_very_good' ) . ';
			}
			.rp-template-1    .rp-review-grade-number .rp-good,
			.rp-template-1    .rp-review-stars .rp-good,
			.rp-template-2    .rp-review-option-rating  .rp-good{
					color: ' . $review->rp_get_option( 'cwppos_rating_good' ) . ';
			}
			
			.rp-template-1    .rp-review-grade-number .rp-not-bad,
			.rp-template-1    .rp-review-stars .rp-not-bad,
			.rp-template-2  .rp-review-option-rating .rp-not-bad{
					color: ' . $review->rp_get_option( 'cwppos_rating_notbad' ) . ';
					color: ' . $review->rp_get_option( 'cwppos_rating_notbad' ) . ';
			}
			.rp-template-1    .rp-review-grade-number .rp-weak,
			.rp-template-1    .rp-review-stars .rp-weak,
			.rp-template-2  .rp-review-option-rating  .rp-weak{
					color: ' . $review->rp_get_option( 'cwppos_rating_weak' ) . ';
			} 
			.rp-template-1    .rp-review-grade-number .rp-default,
			.rp-template-1    .rp-review-stars .rp-default,
			.rp-review-option-rating  .rp-default{
					color: ' . $review->rp_get_option( 'cwppos_rating_default' ) . ';
			} 
			
			
			.rp-template .rp-review-name{
					color: ' . $review->rp_get_option( 'cwppos_font_color' ) . ';
			} 
			.rp-template h3.rp-review-cons-name{
					color: ' . $review->rp_get_option( 'cwppos_cons_color' ) . ';
			} 
			.rp-template h3.rp-review-pros-name{
					color: ' . $review->rp_get_option( 'cwppos_pros_color' ) . ';
			} 
		';

		$scale      = $review->rp_get_option( 'rp_use_5_rating_scale' );
		
		if ( 5 == $scale ) {
			
			$style  .= '
				#review-statistics .review-wu-bars ul li {
					width: 18%;
				}';
		}

		return $style;
	}

	
	public function display_on_front( $content ) {
		if ( empty( $this->review ) ) {
			return $content;
		}

		if ( $this->review->is_active() && is_singular() ) {
			$output        = '';
			$review_object = $this->review;
			$template      = new RP_Template();
			$output        .= $template->render(
				$review_object->get_template(),
				array(
					'review_object' => $review_object,
				),
				false
			);

			$output .= $template->render(
				'rich-json-ld',
				array(
					'review_object' => $review_object,
				),
				false
			);

			$review_position_before_content = $this->review->rp_get_option( 'cwppos_show_reviewbox' );
			if ( $review_position_before_content === 'yes' ) {
				$content = $content . $output;
			} elseif ( $review_position_before_content === 'no' ) {
				$content = $output . $content;
			}
		}

		return $content;
	}


	
	function add_comment_fields() {
		if ( ! $this->review->is_active() ) {
			return '';
		}
		if ( $this->review->rp_get_option( 'cwppos_show_userreview' ) !== 'yes' ) {
			return '';
		}

		if ( apply_filters( 'rp_disable_comments', false, $this->review ) ) {
			return '';
		}

		switch ( $this->review->rp_get_option( 'rp_comment_rating' ) ) {
			case 'star':
				include_once RP_PATH . '/includes/public/layouts/comment-rating-star-tpl.php';
				break;
			default:
				include_once RP_PATH . '/includes/public/layouts/comment-rating-slider-tpl.php';
				break;
		}

	}

	
	public function save_comment_fields( $comment_id ) {
		$comment = get_comment( $comment_id );
		if ( empty( $comment ) ) {
			return;
		}
		$review = new RP_Review_Model( $comment->comment_post_ID );
		if ( empty( $review ) ) {
			return;
		}
		if ( ! $review->is_active() ) {
			return;
		}
		if ( $review->rp_get_option( 'cwppos_show_userreview' ) !== 'yes' ) {
			return;
		}

		$options      = $review->get_options();
		$option_names = wp_list_pluck( $options, 'name' );
		$valid_review = false;
		foreach ( $option_names as $k => $value ) {
			if ( isset( $_POST[ 'rp-slider-option-' . $k ] ) && ! empty( $_POST[ 'rp-slider-option-' . $k ] ) ) {
				$valid_review = true;
				break;
			}
		}
		if ( ! $valid_review ) {
			return;
		}

		$scale      = $review->rp_get_option( 'rp_use_5_rating_scale' );
		if ( empty( $scale ) ) {
			$scale  = 10;
		}

		
		$multiplier = ( 10 / $scale );

		switch ( $review->rp_get_option( 'rp_comment_rating' ) ) {
			case 'star':
				
				$multiplier = 2;
				break;
		}

		foreach ( $option_names as $k => $value ) {
			if ( isset( $_POST[ 'rp-slider-option-' . $k ] ) ) {
				$option_value = wp_filter_nohtml_kses( $_POST[ 'rp-slider-option-' . $k ] );
				$option_value = $multiplier * ( empty( $value ) ? 0 : $option_value );
				update_comment_meta( $comment_id, 'meta_option_' . $k, $option_value );
			}
		}
		$review->update_comments_rating();
	}

	
	public function show_comment_ratings( $text ) {

		if ( empty( $this->review ) ) {
			return $text;
		}
		if ( ! $this->review->is_active() ) {
			return $text;
		}
		if ( $this->review->rp_get_option( 'cwppos_show_userreview' ) !== 'yes' ) {
			return $text;
		}

		global $comment;

		if ( ! $comment ) {
			return $text;
		}

		$options = $this->review->get_comment_options( $comment->comment_ID );
		if ( empty( $options ) ) {
			return $text;
		}

		
		$display    = $this->review->rp_get_option( 'rp_use_5_rating_scale' );
		if ( empty( $display ) ) {
			$display    = 10;
		}

		$return = '';
		$return .= '<div class="user-comments-grades">';
		foreach ( $options as $k => $option ) {
			$value  = $option['value'];
			$int_grade = intval( $value * 10 );
			
			$value  = round( floatval( $value / ( 10 / $display ) ), 2 );
			$return   .= '<div class="comment-meta-option">
                            <p class="comment-meta-option-name">' . $option['name'] . '</p>
                            <p class="comment-meta-option-grade">' . $value . '</p>
                            <div class="cwpr_clearfix"></div>
                            <div class="comment-meta-grade-bar ' . $this->review->get_rating_class( $int_grade ) . '">
                                <div class="comment-meta-grade" style="width: ' . $int_grade . '%"></div>
                            </div><!-- end .comment-meta-grade-bar -->
                        </div><!-- end .comment-meta-option -->
					';
		}
		$return .= '</div>';

		return $return . $text . '<div class="cwpr_clearfix"></div>';
	}

	
	public function amp_width_support( $value, $width ) {
		return 'min-width:' . esc_attr( $width ) . '%';
	}

	
	public function amp_styles() {
		if ( empty( $this->review ) ) {
			return;
		}
		$template_style = $this->review->get_template();

		$amp_cache_key  = '_rp_amp_css_' . str_replace( '.', '_', $this->version ) . '_' . $template_style;
		$output         = get_transient( $amp_cache_key );
		if ( empty( $output ) ) {

			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			WP_Filesystem();
		
			global $wp_filesystem;

			$exclude = apply_filters( 'rp_amp_exclude_stylesheets', array() );
			$output = '';
			$output .= $wp_filesystem->get_contents( RP_PATH . '/assets/css/common.css' );

			if ( ! in_array( 'circle', $exclude, true ) ) {
				$output .= $wp_filesystem->get_contents( RP_PATH . '/assets/css/circle.css' );
			}
			if ( $wp_filesystem->is_readable( RP_PATH . '/assets/css/' . $template_style . '.css' ) ) {
				$output .= $wp_filesystem->get_contents( RP_PATH . '/assets/css/' . $template_style . '.css' );
			}
			if ( ! in_array( 'dashicons', $exclude, true ) ) {
				$output .= $wp_filesystem->get_contents( ABSPATH . '/wp-includes/css/dashicons.min.css' );
			}
			$output .= $this->generate_styles();
			$output .= $wp_filesystem->get_contents( RP_PATH . '/assets/css/rating-amp.css' );
			$output = apply_filters( 'rp_global_style', $output, $this->review );
			$output = $this->minify_amp_css( $output );

			set_transient( $amp_cache_key, $output, HOUR_IN_SECONDS );
		}
		echo apply_filters( 'rp_add_amp_css', $output, $this->review );
	}

	
	function minify_amp_css( $css ) {
		
		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
		
		preg_match_all( '/(\'[^\']*?\'|"[^"]*?")/ims', $css, $hit, PREG_PATTERN_ORDER );
		for ( $i = 0; $i < count( $hit[1] ); $i ++ ) {
			$css = str_replace( $hit[1][ $i ], '##########' . $i . '##########', $css );
		}
	
		$css = preg_replace( '/;[\s\r\n\t]*?}[\s\r\n\t]*/ims', "}\r\n", $css );
		
		$css = preg_replace( '/;[\s\r\n\t]*?([\r\n]?[^\s\r\n\t])/ims', ';$1', $css );
		
		$css = preg_replace( '/[\s\r\n\t]*:[\s\r\n\t]*?([^\s\r\n\t])/ims', ':$1', $css );
		
		$css = preg_replace( '/[\s\r\n\t]*,[\s\r\n\t]*?([^\s\r\n\t])/ims', ',$1', $css );
		
		$css = preg_replace( '/[\s\r\n\t]*{[\s\r\n\t]*?([^\s\r\n\t])/ims', '{$1', $css );
		
		$css = preg_replace( '/([\d\.]+)[\s\r\n\t]+(px|em|pt|%)/ims', '$1$2', $css );
		
		$css = preg_replace( '/([^\d\.]0)(px|em|pt|%)/ims', '$1', $css );
		
		$css = preg_replace( '/\p{Zs}+/ims', ' ', $css );
		
		$css = str_replace( array( "\r\n", "\r", "\n" ), '', $css );
		$css = str_replace( '!important', '', $css );
		
		for ( $i = 0; $i < count( $hit[1] ); $i ++ ) {
			$css = str_replace( '##########' . $i . '##########', $hit[1][ $i ], $css );
		}

		return $css;
	}

	
	public function rating_circle_bar_styles( $styles, $rating ) {
		$degress    = ( is_rtl() ? ( $rating - 100 ) : $rating ) * 3.6;
		return "
		-webkit-transform: rotate({$degress}deg);
		-ms-transform: rotate({$degress}deg);
		transform: rotate({$degress}deg);
		";
	}

	
	public function rating_circle_fill_styles( $styles, $rating ) {
		if ( is_rtl() ) {
			return '
            -webkit-transform: rotate(0deg);
            -ms-transform: rotate(0deg);
            transform: rotate(0deg);
            ';
		}
		return $styles;
	}

}
