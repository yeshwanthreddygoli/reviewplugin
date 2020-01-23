<?php


_deprecated_file( __FILE__, '1.0', 'This is used to give reviews to the products' );
$output = number_format( floor( $review_object->get_rating() ) / 10, 1 );
