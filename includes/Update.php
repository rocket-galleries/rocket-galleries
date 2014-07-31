<?php

/**
 * Class that allows us to gracefully update our plugin data as the verrsions progress.
 *
 * @author Matthew Ruddy
 */
class RG_Update {

    /**
     * Class instance
     *
     * @var RG_TemplateLoader
     */
    private static $instance;

    /**
     * Getter method for retrieving the class instance
     *
     * @return RG_TemplateLoader
     */
    public static function get_instance() {

        if ( ! self::$instance instanceof self ) {
            self::$instance = new self;
        }

        return self::$instance;

    }

    /**
     * Constructor
     *
     * @return void
     */
    private function __construct() {

        // Get the current plugin version
        $version = get_option( 'rocketgalleries_version' );

        // Hook all of our update actions
        add_action( 'rocketgalleries_do_updates', array( $this, 'update_to_0_2' ), 1 );
        
        // Update the plugin version
        do_action( 'rocketgalleries_do_updates', $version );

        // Set the new plugin version
        if ( ! version_compare( $version, RocketGalleries::$version, '=' ) ) {
            update_option( 'rocketgalleries_version', RocketGalleries::$version );
        }

    }

    /**
     * Update plugin to v0.2.0.
     *
     * @param  string $current_version The current plugin version
     * @return void
     */
    public function update_to_0_2( $current_version ) {

        // Bail if the current version is v2.0.6 or greater
        if ( ! version_compare( $current_version, '0.2', '<' ) ) {
            return;
        }

        // Get the database connection
        $database = RocketGalleries::get_instance()->database;

        // Get all of the galleries
        $galleries = $database->all_rows();

        // Loop through and update each gallery
        foreach ( $galleries as $gallery ) {

            // Add "Image Source" option
            if ( ! isset( $gallery->general->source ) ) {
                $gallery->general->source = 'default';
            }

            // Add "Link To" option
            if ( ! isset( $gallery->general->link_to ) ) {
                $gallery->general->link_to = 'post';
            }

            // Save the updated gallery
            $database->update_row( $gallery->id, $gallery );

        }

    }

}