<?php

/**
 * Class used for managing and displaying galleries
 *
 * @author Matthew Ruddy
 */
class RG_Gallery {

    /**
     * Constructor
     *
     * @param  array $data The gallery data
     * @return void
     */
    public function __construct( $data = array() ) {

        // Pre gallery filter
        $data = (object) apply_filters( 'rocketgalleries_pre_gallery', $data );

        // Set the data that's be provided
        $this->set( $data );

    }

    /**
     * Gets a gallery by the ID provided
     *
     * @param  int $id The gallery ID
     * @return RG_Gallery
     */
    public function get( $id ) {

        // Attempt to get the gallery data
        $data = RocketGalleries::get_instance()->database->get_row( $id );

        // Bail if we failed to get a gallery
        if ( ! $data ) {
            return $this;
        }

        // Set the gallery variables
        $this->set( $data );

        return $this;

    }

    /**
     * Sets the gallery data
     *
     * @param  array $data The gallery data
     * @return RG_Gallery
     */
    public function set( $data = array() ) {

        // Set the variables
        foreach ( $data as $key => $value ) {
            $this->$key = $value;
        }

        return $this;

    }

    /**
     * Displays a gallery based on the data provided
     *
     * @return void
     */
    public function display() {

        // For clarity in our templates, let's make $this equal to $gallery
        $gallery = &$this;

        // Before action
        do_action( 'rocketgalleries_before_display_gallery', $gallery );

        // Get our gallery template
        $template = RocketGalleries::get_instance()->template_loader->get_template_part( 'rocketgalleries', 'gallery', false );

        // Load our template (requiring it this way includes are variables within this function)
        require $template;

        // After action
        do_action( 'rocketgalleries_after_display_gallery', $gallery );
        
    }

}