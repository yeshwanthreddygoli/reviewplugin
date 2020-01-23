<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class RP_Schema_Model extends RP_Model_Abstract {

	
	const _URL = 'https://schema.org/?.jsonld';


	private $types;

	
	private $data_types_allowed;

	
	public function __construct( array $things ) {
		parent::__construct();

		$this->setup_types( $things );
	}

	
	private function setup_types( array $things ) {
		
		$this->data_types_allowed   = apply_filters( 'rp_schema_data_types_allowed', array( 'schema:Text', 'schema:URL', 'schema:Date', 'schema:Number', 'schema:Boolean', 'schema:DateTime', 'schema:Time', 'schema:Integer', 'schema:Float', 'schema:False', 'schema:True', 'schema:QuantitativeValue', 'schema:Distance' ), $things );
		$things     = apply_filters( 'rp_schema_things', $things );
		$types      = get_transient( 'rp_schema_types_' . md5( json_encode( $things ) ) );
		if ( $types ) {
			$this->types    = $types;
			return;
		}

		$types  = array();
		$categories = array();
		foreach ( $things as $parent => $type ) {
			foreach ( $type as $t ) {
				$categories[ $t ] = $parent;
			}
		}

		foreach ( $categories as $type => $parent ) {
			$subtypes   = array();

			$this->data_types_allowed_for_type = apply_filters( 'rp_schema_data_types_allowed_for_' . $type, $this->data_types_allowed );

			
			$subtypes   = $this->parse( array( $type ), '@id', array( 'rdfs:subClassOf > @id' => "schema:{$type}" ) );

			
			$subtypes   = array_merge( $subtypes, $this->parse( array( $type ), '@id', array( 'rdfs:subClassOf > @id' => "schema:{$parent}" ) ) );

			foreach ( $subtypes as $subtype ) {
				if ( 'schema:Thing' === $subtype ) {
					continue;
				}

				$subtype    = str_replace( 'schema:', '', $subtype );

				$attributes = $this->parse( array( $subtype ), null, array( '@type' => 'rdf:Property' ) );
				if ( ! $attributes ) {
					continue;
				}

				$fields = array();
				foreach ( $attributes as $attribute ) {
					$fields[ str_replace( 'schema:', '', $attribute['@id'] ) ]  = array(
						'desc'      => $attribute['rdfs:comment'],
						'label'     => $attribute['rdfs:label'],
					);
				}

				ksort( $fields );

				$types[ $subtype ]  = apply_filters( 'rp_schema_fields_for_' . $type, $fields, $subtype );
			}
		}

		ksort( $types );

		$this->types    = $types;

		set_transient( 'rp_schema_types_' . md5( json_encode( $things ) ), $types, YEAR_IN_SECONDS );

	}

	
	private function parse( $types, $extract, $if ) {
		$elements   = array();

		foreach ( $types as $type ) {
			$url        = str_replace( '?', $type, self::_URL );
			$json       = json_decode( wp_remote_retrieve_body( wp_remote_get( $url ) ), true );
			$if         = apply_filters( 'rp_schema_extract_if', $if, $type );

			if ( $json && isset( $json['@graph'] ) ) {
				foreach ( $json['@graph'] as $element ) {
					foreach ( $if as $key => $val ) {
						$extracted = $this->extract_if( $element, $extract, $key, $val );
						if ( $extracted !== null ) {
							$elements[] = $extracted;
						}
					}
				}
			}
		}
		return $elements;
	}

	
	private function extract_if( $element, $extract, $key, $val ) {
		$leaf       = $element;
		$extracted  = null;
		foreach ( array_map( 'trim', explode( '>', $key ) ) as $k ) {
			if ( isset( $leaf[ $k ] ) ) {
				$leaf   = $leaf[ $k ];
			}
		}
		if ( $val === $leaf ) {
			if ( is_null( $extract ) ) {
				$extracted  = $element;
			} else {
				$extracted = $element[ $extract ];
			}
		}

		
		if ( is_array( $extracted ) ) {
			$range_includes = $extracted['schema:rangeIncludes'];
			$data_types     = array_values( $range_includes );
			if ( count( $data_types ) > 1 ) {
				$data_types     = array_values( wp_list_pluck( $data_types, '@id' ) );
			}
			$common = array_intersect( $this->data_types_allowed_for_type, $data_types );
			if ( empty( $common ) ) {
				$extracted = null;
			}
		}

		return $extracted;
	}

	
	public function get_types() {
		return apply_filters( 'rp_schema_types', $this->types );
	}

}
