<?php

/**
 * Our custom image resizing class (no Timthumb here!)
 *
 * @author Matthew Ruddy
 */
class RG_Resize {

    /**
     * Class instance
     *
     * @var RG_Resize
     */
    private static $instance;

    /**
     * Getter method for retrieving the class instance
     *
     * @return RG_Resize
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

        // Ensure that resized images are deleted appropriately
        add_action( 'delete_attachment', array( $this, 'destroy' ) );

    }

    /**
     * Main resizing function
     *
     * @param string  $url    The image URL
     * @param int     $width  The desired width
     * @param int     $height The desired height
     * @param boolean $crop   Whether the function should crop the image to fit the specified dimensions
     * @param boolean $retina Doubles the outputted width and height to suit retina devices
     * @return array
     */
    public static function resize( $url, $width, $height, $crop = true, $retina = false ) {

        global $wpdb;

        if ( empty( $url ) ) {
            return new WP_Error( 'no_image_url', __( 'No image URL has been entered.' ), $url );
        }
              
        // Allow for different retina sizes
        $retina = $retina ? ( $retina === true ? 2 : $retina ) : 1;
            
        /**
         * Bail if this image isn't in the Media Library.
         * We only want to resize Media Library images, so we can be sure they get deleted correctly when appropriate.
         */
        $query = $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE guid='%s'", $url );
        $get_attachment = $wpdb->get_results( $query );
        if ( ! $get_attachment ) {
            return array( 'url' => $url, 'width' => $width, 'height' => $height );
        }

        // Get the image file path
        $file_path = parse_url( $url );
        $file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];
        
        // Check for Multisite
        if ( is_multisite() ) {
            global $blog_id;
            $blog_details = get_blog_details( $blog_id );
            $file_path = str_replace( $blog_details->path . 'files/', '/wp-content/blogs.dir/'. $blog_id .'/files/', $file_path );
        }

        // Destination width and height variables
        $dest_width = $width * $retina;
        $dest_height = $height * $retina;

        // File name suffix (appended to original file name)
        $suffix = "{$dest_width}x{$dest_height}";

        // Some additional info about the image
        $info = pathinfo( $file_path );
        $dir = $info['dirname'];
        $ext = $info['extension'];
        $name = wp_basename( $file_path, ".$ext" );

        // Suffix applied to filename
        $suffix = "{$dest_width}x{$dest_height}";

        // Get the destination file name
        $dest_file_name = "{$dir}/{$name}-{$suffix}.{$ext}";

        if ( !file_exists( $dest_file_name ) ) {

            // Load Wordpress Image Editor
            $editor = wp_get_image_editor( $file_path );
            if ( is_wp_error( $editor ) ) {
                return array( 'url' => $url, 'width' => $width, 'height' => $height );
            }

            // Get the original image size
            $size = $editor->get_size();
            $orig_width = $size['width'];
            $orig_height = $size['height'];

            $src_x = $src_y = 0;
            $src_w = $orig_width;
            $src_h = $orig_height;

            if ( $crop ) {

                $cmp_x = $orig_width / $dest_width;
                $cmp_y = $orig_height / $dest_height;

                // Calculate x or y coordinate, and width or height of source
                if ( $cmp_x > $cmp_y ) {
                    $src_w = round( $orig_width / $cmp_x * $cmp_y );
                    $src_x = round( ( $orig_width - ( $orig_width / $cmp_x * $cmp_y ) ) / 2 );
                }
                else if ( $cmp_y > $cmp_x ) {
                    $src_h = round( $orig_height / $cmp_y * $cmp_x );
                    $src_y = round( ( $orig_height - ( $orig_height / $cmp_y * $cmp_x ) ) / 2 );
                }

            }

            // Time to crop the image!
            $editor->crop( $src_x, $src_y, $src_w, $src_h, $dest_width, $dest_height );

            // Now let's save the image
            $saved = $editor->save( $dest_file_name );

            // Get resized image information
            $resized_url    = str_replace( basename( $url ), basename( $saved['path'] ), $url );
            $resized_width  = $saved['width'];
            $resized_height = $saved['height'];
            $resized_type   = $saved['mime-type'];

            /**
             * Add the resized dimensions to original image metadata
             * (so we can delete our resized images when the original image is delete from the Media Library)
             */
            $metadata = wp_get_attachment_metadata( $get_attachment[0]->ID );
            if ( isset( $metadata['image_meta'] ) ) {
                $metadata['image_meta']['resized_images'][] = $resized_width .'x'. $resized_height;
                wp_update_attachment_metadata( $get_attachment[0]->ID, $metadata );
            }

            // Create the image array
            $resized_image = array(
                'url'    => $resized_url,
                'width'  => $resized_width,
                'height' => $resized_height,
                'type'   => $resized_type
            );

        }
        else {
            $resized_image = array(
                'url'    => str_replace( basename( $url ), basename( $dest_file_name ), $url ),
                'width'  => $dest_width,
                'height' => $dest_height,
                'type'   => $ext
            );
        }

        // And we're done!
        return $resized_image;

    }

    /**
     * Deletes any resized image (from above function) when the original image is deleted from the Wordpress Media Library.
     *
     * @param int $post_id The attachment ID
     * @return void
     */
    public static function destroy( $post_id ) {

        // Get attachment image metadata
        $metadata = wp_get_attachment_metadata( $post_id );
        if ( ! $metadata ) {
            return;
        }

        // Do some bailing if we cannot continue
        if ( ! isset( $metadata['file'] ) || ! isset( $metadata['image_meta']['resized_images'] ) ) {
            return;
        }

        $pathinfo = pathinfo( $metadata['file'] );
        $resized_images = $metadata['image_meta']['resized_images'];

        // Get Wordpress uploads directory (and bail if it doesn't exist)
        $wp_upload_dir = wp_upload_dir();
        $upload_dir = $wp_upload_dir['basedir'];

        if ( ! is_dir( $upload_dir ) ) {
            return;
        }

        // Delete the resized images
        foreach ( $resized_images as $dims ) {

            // Get the resized images filename
            $file = $upload_dir .'/'. $pathinfo['dirname'] .'/'. $pathinfo['filename'] .'-'. $dims .'.'. $pathinfo['extension'];

            // Delete the resized image
            @unlink( $file );

        }

    }

}