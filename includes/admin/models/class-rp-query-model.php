<?php

class RP_Query_Model extends RP_Model_Abstract {

	
	protected $review;


	private $db;

	
	public function __construct() {
		parent::__construct();

		global $wpdb;
		$this->db = $wpdb;
	}

	
	public function find_by_cat_id( $cat_id, $limit = 20, $filter = array(), $order = array() ) {
		return $this->find(
			array(
				'category_id' => $cat_id,
			),
			$limit,
			$filter,
			$order
		);
	}

	
	public function find(
		$post = array(
			'category_id'           => false,
			'category_name'         => false,
			'post_type'             => array( 'post', 'page' ),
			'post_date_range_weeks' => false,
		),
		$limit = 20,
		$filter = array(
			'name'   => false,
			'price'  => false,
			'rating' => false,
		),
		$order = array(
			'rating' => false,
			'price'  => false,
			'date'   => false,
		)
	) {
		if ( ! is_numeric( $limit ) && $limit >= 0 ) {
			$limit = 20;
		}
		if ( ! isset( $post['post_type'] ) ) {
			$types          = array( 'post', 'page' );
			if ( 'yes' === $this->rp_get_option( 'rp_cpt' ) ) {
				$types[]    = 'rp_review';
			}
			$post['post_type'] = $types;
		}
		$sub_query_posts = $this->get_sub_query_posts( $post );

		$order_by         = $this->get_order_by( $order );
		$conditions       = $this->get_query_conditions( $post, $filter );
		$conditions_where = '';
		if ( isset( $conditions['where'] ) ) {
			$conditions_where = $conditions['where'];
		}
		$conditions_having = '';
		if ( isset( $conditions['having'] ) ) {
			$conditions_having = $conditions['having'];
		}

		$final_rating       = '`rating`';
		$comment_influence = intval( $this->rp_get_option( 'cwppos_infl_userreview' ) );
		if ( $comment_influence > 0 ) {
			$final_rating   = "IF(`comment_rating` = 0, `rating`, (`comment_rating` * 10 * ( $comment_influence / 100 ) + `rating` * ( ( 100 - $comment_influence ) / 100 ) ) )";
		}

		$final_order        = isset( $order['rating'] ) && in_array( $order['rating'], array( 'ASC', 'DESC' ), true ) ? " ORDER BY `final_rating` {$order['rating']}" : '';

		$query   = " 
		SELECT ID, post_date, post_title, `check`, `name`, `price`, `rating`, `comment_rating`, FORMAT($final_rating, 2) as 'final_rating' FROM
		(
        SELECT 
			ID,
			post_date,
			post_title,
            GROUP_CONCAT( DISTINCT IF( `meta_key` = 'cwp_meta_box_check', `meta_value`, '' ) SEPARATOR '' ) AS 'check', 
            GROUP_CONCAT( DISTINCT IF( `meta_key` = 'cwp_rev_product_name', `meta_value`, '' ) SEPARATOR '' ) AS 'name',   
            GROUP_CONCAT( DISTINCT IF( `meta_key` = 'cwp_rev_price', FORMAT( `meta_value`, 2 ), '' ) SEPARATOR '' ) AS 'price', 
			GROUP_CONCAT( DISTINCT IF( `meta_key` = 'rp_rating', IF(FORMAT(`meta_value`, 2) = '100.00','99.99', FORMAT(`meta_value`, 2) ), '') SEPARATOR '' ) AS 'rating',
            GROUP_CONCAT( DISTINCT IF( `meta_key` = 'rp_comment_rating', `meta_value`, '') SEPARATOR '' ) AS 'comment_rating'
        FROM {$this->db->postmeta} m INNER JOIN {$this->db->posts} p on p.ID = m.post_ID
        
        {$sub_query_posts}
        where p.post_status = 'publish' 
         {$conditions_where}
        GROUP BY `ID` 
        HAVING `check` = 'Yes' 
        {$conditions_having}
        ORDER BY 
        {$order_by}
        `name` ASC
        LIMIT {$limit}
		) T1 $final_order
        ";

		do_action( 'themeisle_log_event', RP_SLUG, sprintf( 'post = %s, limit = %s, filter = %s, order = %s and query = %s', print_r( $post, true ), $limit, print_r( $filter, true ), print_r( $order, true ), $query ), 'debug', __FILE__, __LINE__ );

		$key     = hash( 'sha256', $query );
		$results = wp_cache_get( $key, 'rp' );
		if ( ! is_array( $results ) ) {
			$results = $this->db->get_results( $query, ARRAY_A );
			if ( ! RP_CACHE_DISABLED ) {
				wp_cache_set( $key, $results, 'rp', ( 60 * 60 ) );
			}
		}// End if().

		return $results;
	}


	
	private function get_sub_query_posts( $post ) {
		
		if ( ! isset( $post['category_name'] ) && ! isset( $post['category_id'] ) ) {
			return '';
		}

		$category   = 'yes' === $this->rp_get_option( 'rp_cpt' ) ? 'rp_category' : 'category';
		if ( isset( $post['taxonomy_name'] ) ) {
			$category = $post['taxonomy_name'];
		}
		$sub_selection_query = "INNER JOIN {$this->db->term_relationships } wtr ON wtr.object_id = p.ID
	            INNER JOIN {$this->db->term_taxonomy} wtt on wtt.term_taxonomy_id = wtr.term_taxonomy_id AND wtt.taxonomy = '$category'
	            INNER JOIN {$this->db->terms} wt
	            ON wt.term_id = wtt.term_id";

		return $sub_selection_query;
	}

	
	private function get_order_by( $order ) {
		$order_by = '';
		if ( isset( $order['rating'] ) && in_array( $order['rating'], array( 'ASC', 'DESC' ), true ) ) {
			$column = 'rating';
			
			$comment_influence = intval( $this->rp_get_option( 'cwppos_infl_userreview' ) );
			if ( $comment_influence > 0 ) {
				$column = 'comment_rating';
			}
			$order_by .= "`$column` {$order['rating']}, ";
		}

		if ( isset( $order['price'] ) && in_array( $order['price'], array( 'ASC', 'DESC' ), true ) ) {
			$order_by .= "`price` {$order['price']}, ";
		}
		if ( isset( $order['date'] ) && in_array( $order['date'], array( 'ASC', 'DESC' ), true ) ) {
			$order_by .= "`post_date` {$order['date']}, ";
		}

		$order_by       .= apply_filters( 'rp_order_by_clause', '', $order );

		return $order_by;
	}


	private function get_query_conditions( $post, $filter ) {
		$conditions          = array( 'where' => '', 'having' => '' );
		$conditions['where'] = $this->get_sub_query_conditions( $post );
		if ( isset( $filter['name'] ) && $filter['name'] !== false ) {
			$conditions['having'] .= $this->db->prepare( ' AND `name` LIKE %s ', '%' . $filter['name'] . '%' );
		}

		
		if ( isset( $filter['price'] ) && $filter['price'] !== false && is_numeric( $filter['price'] ) ) {
			$conditions['having'] .= $this->db->prepare( ' AND `price` > FORMAT( %d, 2 ) ', $filter['price'] );
		}
		
		if ( isset( $filter['rating'] ) && $filter['rating'] !== false && is_numeric( $filter['rating'] ) ) {
			$conditions['having'] .= $this->db->prepare( ' AND `rating`  > %f ', $filter['rating'] );
		}

		$conditions     = apply_filters( 'rp_where_clause', $conditions, $post, $filter );

		return $conditions;
	}

	
	private function get_sub_query_conditions( $post ) {
		$sub_query_conditions = '';
		if ( isset( $post['category_id'] ) && $post['category_id'] !== false && is_numeric( $post['category_id'] ) && $post['category_id'] > 0 ) {
			$sub_query_conditions .= $this->db->prepare( " AND wt.term_id = '%d' ", $post['category_id'] );
		}

		if ( isset( $post['category_name'] ) && $post['category_name'] !== false ) {
			$sub_query_conditions .= $this->db->prepare( ' AND wt.slug = %s ', $post['category_name'] );
		}
		
		if ( isset( $post['post_type'] ) && is_array( $post['post_type'] ) ) {
			$filter_post_type      = array_fill( 0, count( $post['post_type'] ), ' p.post_type = %s ' );
			$filter_post_type      = implode( ' OR ', $filter_post_type );
			$filter_post_type      = ' AND ( ' . $filter_post_type . ' ) ';
			$sub_query_conditions .= $this->db->prepare( $filter_post_type, $post['post_type'] );
		}

		if ( isset( $post['post_date_range_weeks'] ) && ! is_bool( $post['post_date_range_weeks'] ) && is_array( $post['post_date_range_weeks'] ) ) {
			$min                   = reset( $post['post_date_range_weeks'] );
			$max                   = end( $post['post_date_range_weeks'] );
			$sub_query_conditions .= $this->db->prepare( ' AND p.post_date >= DATE_ADD(now(), INTERVAL %d WEEK) AND p.post_date <= DATE_ADD(now(), INTERVAL %d WEEK) ', $min, $max );
		}

		if ( isset( $post['post_date_range'] ) && ! is_bool( $post['post_date_range'] ) && is_array( $post['post_date_range'] ) ) {
			$min                   = reset( $post['post_date_range'] );
			$max                   = end( $post['post_date_range'] );
			if ( ! empty( $min ) ) {
				$sub_query_conditions .= $this->db->prepare( ' AND p.post_date >= %s ', $min );
			}
			if ( ! empty( $max ) ) {
				$sub_query_conditions .= $this->db->prepare( ' AND p.post_date <= %s ', $max );
			}
		}

		$sub_query_conditions       .= apply_filters( 'rp_where_sub_clause', '', $post );

		return $sub_query_conditions;
	}


	public function find_by_category( $category, $limit = 20, $filter = array(), $order = array() ) {
		return $this->find(
			array(
				'category_name' => $category,
			),
			$limit,
			$filter,
			$order
		);
	}


	public function find_by_name( $name, $limit = 20 ) {
		return $this->find(
			false,
			$limit,
			array(
				'name' => $name,
			)
		);
	}

	
	public function find_by_price( $price, $limit = 20 ) {
		return $this->find(
			false,
			$limit,
			array(
				'price' => $price,
			)
		);
	}

	
	public function find_by_rating( $rating, $limit = 20 ) {
		return $this->find(
			false,
			$limit,
			array(
				'rating' => $rating,
			)
		);
	}


	public function find_all_reviews() {
		$type   = apply_filters( 'rp_find_all_reviews_post_types', ( 'yes' === $this->rp_get_option( 'rp_cpt' ) ? array( 'rp_review' ) : array( 'post', 'page' ) ) );
		$query  = new WP_Query(
			apply_filters(
				'rp_find_all_reviews', array(
					'post_type'     => $type,
					'post_status'   => 'publish',
					'fields'        => 'ids',
					'nopaging'      => true,
					'posts_per_page'   => 300,
					'meta_query'    => array(
						array(
							'key'   => 'cwp_meta_box_check',
							'value' => 'Yes',
						),
					),
				)
			)
		);

		$reviews    = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$reviews[]  = $query->post;
			}
			wp_reset_postdata();
		}
		return $reviews;
	}
}
