<?php

/**
 * Class used for displaying in the front-end.
 *
 * @author Matthew Ruddy
 */
class RG_Gallery {

    /**
     * Class instance
     *
     * @var RG_Database
     */
    private static $instance;

    /**
     * Getter method for retrieving the class instance
     *
     * @return RG_Gallery
     */
    public static function get_instance() {

        if ( ! self::$instance instanceof self ) {
            self::$instance = new self;
        }

        return self::$instance;

    }

    /**
     * Displays a gallery based on the data provided
     *
     * @param  object $gallery The gallery data
     * @return string
     */
    public function display( $gallery ) {

        // Before action
        do_action( 'rocketgalleries_before_display_gallery', $gallery );

        // Get our gallery template
        $template = RocketGalleries::get( 'template_loader' )->get_template_part( 'rocketgalleries', 'gallery', false );

        // Load our template (requiring it this way includes are variables within this function)
        require $template;

        // After action
        do_action( 'rocketgalleries_after_display_gallery', $gallery );
        
    }

}