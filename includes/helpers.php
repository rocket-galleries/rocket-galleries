<?php

/**
 * Simple helper function for displaying a gallery.
 *
 * @author Matthew Ruddy
 * @param  array|int $atts The attributes, just like when using a shortcode. If an integer is provided, the gallery is fetched by ID.
 * @return void
 */
if ( ! function_exists( 'rocketgalleries' ) ) {
    function rocketgalleries( $atts ) {

        // Handle if just an ID is provided
        if ( is_int( $atts ) ) {
            $atts = array( 'id' => $atts );
        }

        // Display the gallery
        echo RocketGalleries::get_instance()->do_shortcode( $atts );

    }
}

/**
 * Returns the gallery ID string
 *
 * @param  object $gallery The gallery object
 * @return string
 */
if ( ! function_exists( 'rocketgalleries_get_the_gallery_id' ) ) {
	function rocketgalleries_get_the_gallery_id( $gallery ) {

		// The ID string
		$output = apply_filters( 'rocketgalleries_get_the_gallery_id', '' );

		// If an ID has been provided, generate the ID
		if ( isset( $gallery->id ) ) {
			$output = apply_filters( "rocketgalleries_get_the_gallery_{$gallery->id}_id", $output );
		}

		return $output;
		
	}
}

/**
 * Prints the gallery ID string
 *
 * @param  object $gallery The gallery object
 * @return void
 */
if ( ! function_exists( 'rocketgalleries_the_gallery_id' ) ) {
	function rocketgalleries_the_gallery_id( $gallery ) {

		echo rocketgalleries_get_the_gallery_id( $gallery );
		
	}
}

/**
 * Returns the gallery class string
 *
 * @param  object $gallery The gallery object
 * @return string
 */
if ( ! function_exists( 'rocketgalleries_get_the_gallery_class' ) ) {
	function rocketgalleries_get_the_gallery_class( $gallery ) {

		// The class string
		$output = apply_filters( 'rocketgalleries_get_the_gallery_class', 'rocketgalleries' );

		// Also filter specifically by ID if one has been provided.
		if ( isset( $gallery->id ) ) {
			$output = apply_filters( "rocketgalleries_get_the_gallery_{$gallery->id}_class", $output );
		}

		return $output;

	}
}

/**
 * Prints the gallery class string
 *
 * @param  object $gallery The gallery object
 * @return void
 */
if ( ! function_exists( 'rocketgalleries_the_gallery_class' ) ) {
	function rocketgalleries_the_gallery_class( $gallery ) {

		echo rocketgalleries_get_the_gallery_class( $gallery );

	}
}

/**
 * Checks if a gallery image has a link.
 *
 * @param  object $gallery The gallery object
 * @param  object $image   The image object
 * @return boolean
 */
if ( ! function_exists( 'rocketgalleries_has_image_link' ) ) {
	function rocketgalleries_has_image_link( $gallery, $image ) {

		return ( $gallery->general->link_to !== 'none' ) ? true : false;

	}
}

/**
 * Returns the appropriate image link, based on the gallery settings.
 *
 * @param  object $gallery The gallery object
 * @param  object $image   The image object
 * @return string|false
 */
if ( ! function_exists( 'rocketgalleries_get_the_image_link' ) ) {
	function rocketgalleries_get_the_image_link( $gallery, $image ) {

		// Bail if we don't have a link
		if ( ! rocketgalleries_has_image_link( $gallery, $image ) ) {
			return false;
		}

		// Get the link
		switch ( $gallery->general->link_to ) {
			default :
				$link = wp_get_attachment_url( $image->attachment_id );
				break;

			case 'post' :
				$link = get_attachment_link( $image->attachment_id );
				break;
		}

		// Apply a filter and return
		return apply_filters( 'rocketgalleries_get_the_image_link', $link, $gallery, $image );

	}
}

/**
 * Prints the appropriate image link, based on the gallery settings.
 *
 * @param  object $gallery The gallery object
 * @param  object $image   The image object
 * @return void
 */
if ( ! function_exists( 'rocketgalleries_the_image_link' ) ) {
	function rocketgalleries_the_image_link( $gallery, $image ) {

		// Bail if we don't have a link
		if ( ! rocketgalleries_has_image_link( $gallery, $image ) ) {
			return false;
		}

		echo rocketgalleries_get_the_image_link( $gallery, $image );

	}
}

/**
 * Returns the image src, based on the gallery settings.
 *
 * @param  object $gallery The gallery object
 * @param  object $image   The image object
 * @return string
 */
if ( ! function_exists( 'rocketgalleries_get_the_image_src' ) ) {
	function rocketgalleries_get_the_image_src( $gallery, $image ) {

		// Get the dimensions
		$dimensions = apply_filters( 'rocketgalleries_the_image_dimensions', array(
			'width' => 150,
			'height' => 150,
			'crop' => true
		) );

		// Get the resized image
		$resized_image = RocketGalleries::get_instance()->resize->resize(
			$image->url,
			$dimensions['width'],
			$dimensions['height'],
			$dimensions['crop']
		);

		return $resized_image['url'];
		
	}
}

/**
 * Prints the image src, based on the gallery settings.
 *
 * @param  object $gallery The gallery object
 * @param  object $image   The image object
 * @return string
 */
if ( ! function_exists( 'rocketgalleries_the_image_src' ) ) {
	function rocketgalleries_the_image_src( $gallery, $image ) {

		echo rocketgalleries_get_the_image_src( $gallery, $image );

	}
}