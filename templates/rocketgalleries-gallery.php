<?php
    /**
     * Template used to display a gallery.
     * 
     * This can be overriden by creating your own gallery.php file in your theme.
     * See the documentation for more information.
     */
?>

<div class="rocketgalleries-<?php echo esc_attr( $gallery->id ); ?>">
    <?php foreach ( $gallery->images as $image ) : ?>
        <?php
            // Resized image attributes, with a filter for good measure
            $image_atts = (object) apply_filters( 'rocketgalleries_gallery_image_attributes',
                array(
                    'width'  => 150,
                    'height' => 150,
                    'crop'   => true
                )
            );
            
            // Get the resized image
            $resized_image = (object) RocketGalleries::get( 'resize' )->resize( $image->url, $image_atts->width, $image_atts->height, $image_atts->crop );
        ?>
        <figure class="rocketgalleries-item">
            <a class="rocketgalleries-link" href="<?php echo get_attachment_link( $image->attachment_id ); ?>">
                <img class="rocketgalleries-image" src="<?php echo esc_attr( $resized_image->url ); ?>" alt="<?php echo esc_attr( $image->url ); ?>">
            </a>
        </figure>
    <?php endforeach; ?>
</div>