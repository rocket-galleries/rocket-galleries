<?php
    /**
     * Template used to display a gallery.
     * 
     * This can be overriden by creating your own gallery.php file in your theme.
     * See the documentation for more information.
     */
?>

<div id="<?php rocketgalleries_the_gallery_id( $gallery ); ?>" class="<?php rocketgalleries_the_gallery_class( $gallery ); ?>">

    <?php foreach ( $gallery->images as $image ) : ?>

        <div class="rocketgalleries-item">
            <?php if ( rocketgalleries_has_image_link( $gallery, $image ) ) : ?>
                <a class="rocketgalleries-link" rel="<?php rocketgalleries_the_gallery_id( $gallery ); ?>" href="<?php rocketgalleries_the_image_link( $gallery, $image ); ?>">
            <?php endif; ?>
            
            <img class="rocketgalleries-image" src="<?php rocketgalleries_the_image_src( $gallery, $image ); ?>" alt="<?php rocketgalleries_the_image_src( $gallery, $image ); ?>">
            
            <?php if ( rocketgalleries_has_image_link( $gallery, $image ) ) : ?>
                </a>
            <?php endif; ?>
        </div>

    <?php endforeach; ?>

</div>