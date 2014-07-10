<?php

/*
    Plugin Name: Rocket Galleries
    Plugin URI: http://rocketgalleries.com/
    Version: 0.1.5
    Author: Matthew Ruddy
    Author URI: http://matthewruddy.com/
    Description: Rocket Galleries is the gallery manager WordPress never had. Easily create and manage galleries from one intuitive panel within WordPress. Simple, easy to use, and lightweight.
    License: GNU General Public License v2.0 or later
    License URI: http://www.opensource.org/licenses/gpl-license.php

    Copyright 2014 Matthew Ruddy

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Load all of the necessary class files for the plugin
spl_autoload_register( 'RocketGalleries::autoload' );

// Let's go!
if ( class_exists( 'RocketGalleries' ) ) {
    RocketGalleries::get_instance();
}

/**
 * Simple helper function for displaying a gallery.
 *
 * @author Matthew Ruddy
 * @param  int  $id The ID of the gallery you wish to display
 * @return void
 */
if ( ! function_exists( 'rocketgalleries' ) ) {
    function rocketgalleries( $id ) {
        echo RocketGalleries::get_instance()->do_shortcode( array( 'id' => $id ) );
    }
}

/**
 * Main plugin class
 *
 * @author Matthew Ruddy
 */
class RocketGalleries {

    /**
     * Class instance
     *
     * @var RocketGalleries
     */
    private static $instance;

    /**
     * String name of the main plugin file
     *
     * @var string
     */
    private static $file = __FILE__;

    /**
     * Our plugin version
     *
     * @var string
     */
    public static $version = '0.1.5';

    /**
     * Our array of Rocket Galleries admin pages. These are used to conditionally load scripts.
     *
     * @var array
     */
    public $whitelist = array();

    /**
     * Arrays of admin messages
     *
     * @var array
     */
    public $admin_messages = array();

    /**
     * Flag for indicating that we are on a RocketGalleries plugin page
     *
     * @var boolean
     */
    private $is_plugin_page = false;
    
    /**
     * PSR-0 compliant autoloader to load classes as needed.
     *
     * @return void
     */
    public static function autoload( $classname ) {
    
        if ( 'RG' !== substr( $classname, 0, 2 ) ) {
            return;
        }
            
        $filename = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . str_replace( 'RG_', '', $classname ) . '.php';

        if ( file_exists( $filename ) ) {
            require $filename;
        }
    
    }

    /**
     * Getter method for retrieving the class instance.
     *
     * @return RocketGalleries
     */
    public static function get_instance() {
    
        if ( ! self::$instance instanceof self ) {
            self::$instance = new self;
        }

        return self::$instance;
    
    }

    /**
     * Gets the main plugin file
     *
     * @return string
     */
    public static function get_file() {

        return self::$file;

    }
    
    /**
     * Constructor
     *
     * @return void
     */
    private function __construct() {

        // Load plugin textdomain for language capabilities
        load_plugin_textdomain( 'rocketgalleries', false, dirname( plugin_basename( self::get_file() ) ) . '/languages' );

        // Activation and deactivation hooks. Static methods are used to avoid activation/uninstallation scoping errors.
        if ( is_multisite() ) {
            register_activation_hook( __FILE__, array( __CLASS__, 'do_network_activation' ) );
            register_uninstall_hook( __FILE__, array( __CLASS__, 'do_network_uninstall' ) );
        }
        else {
            register_activation_hook( __FILE__, array( __CLASS__, 'do_activation' ) );
            register_uninstall_hook( __FILE__, array( __CLASS__, 'do_uninstall' ) );
        }

        // Plugin shortcodes
        add_shortcode( 'rocketgalleries', array( $this, 'do_shortcode' ) );

        // Plugin actions
        add_action( 'init', array( $this, 'register_all_styles' ) );
        add_action( 'init', array( $this, 'register_all_scripts' ) );
        add_action( 'init', array( $this, 'enqueue_gallery_assets' ) );
        add_action( 'admin_menu', array( $this, 'add_menus' ) );
        add_action( 'admin_menu', array( $this, 'do_actions' ) );
        add_action( 'admin_menu', array( $this, 'queue_action_messages' ) );
        add_action( 'admin_footer', array( $this, 'display_media_thickbox' ) );
        add_action( 'media_buttons', array( $this, 'add_media_button' ), 11 );
        add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_links' ), 999 );
        add_action( 'after_setup_theme', array( $this, 'check_theme_for_templates' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'print_media_templates', array( $this, 'print_backbone_templates' ) );

        // Some hooks for our own custom actions
        add_action( 'rocketgalleries_add_gallery_actions', array( $this, 'do_gallery_actions' ) );
        add_action( 'rocketgalleries_edit_galleries_actions', array( $this, 'do_gallery_actions' ) );
        add_action( 'rocketgalleries_edit_settings_actions', array( $this, 'do_settings_actions' ) );
        add_action( 'rocketgalleries_theme_has_template', array( $this, 'queue_has_template_class' ) );

        // Initialization hook for adding external functionality
        do_action_ref_array( 'rocketgalleries', array( $this ) );

    }

    /**
     * Getter method for getting our library classes
     *
     * @param  string $class The library class to get
     * @return mixed
     */
    public static function get( $class ) {

        // Generate the class name, applying a filter to allow users to extend and hook their own classes instead
        $classname = apply_filters( "rocketgalleries_get_{$class}", 'RG_'. str_replace( ' ', '', ucwords( str_replace( '_', ' ', $class ) ) ) );

        // Get the class instance, if it exists
        if ( class_exists( $classname ) ) {
            return call_user_func( array( $classname, 'get_instance' ) );
        }
        
    }
    
    /**
     * Executes a network activation
     *
     * @return void
     */
    public static function do_network_activation() {

        self::get_instance()->network_activate();

    }
    
    /**
     * Executes a network uninstall
     *
     * @return void
     */
    public static function do_network_uninstall() {

        self::get_instance()->network_uninstall();

    }
    
    /**
     * Executes an activation
     *
     * @return void
     */
    public static function do_activation() {

        self::get_instance()->activate();

    }
    
    /**
     * Executes an uninstall
     *
     * @return void
     */
    public static function do_uninstall() {

        self::get_instance()->uninstall();

    }
    
    /**
     * Network activation hook
     *
     * @return void
     */
    public function network_activate() {

        // Do plugin version check
        if ( ! $this->version_check() ) {
            return;
        }

        // Get all of the blogs
        $blogs = $this->get_multisite_blogs();

        // Execute acivation for each blog
        foreach ( $blogs as $blog_id ) {
            switch_to_blog( $blog_id );
            $this->activate();
            restore_current_blog();
        }

        // Trigger hooks
        do_action_ref_array( 'rocketgalleries_network_activate', array( $this ) );

    }
    
    /**
     * Network uninstall hook
     *
     * @return void
     */
    public function network_uninstall() {

        // Get all of the blogs
        $blogs = $this->get_multisite_blogs();

        // Execute uninstall for each blog
        foreach ( $blogs as $blog_id ) {
            switch_to_blog( $blog_id );
            $this->uninstall();
            restore_current_blog();
        }

        // Trigger hooks
        do_action_ref_array( 'rocketgalleries_network_uninstall', array( $this ) );

    }
    
    /**
     * Activation hook
     *
     * @return void
     */
    public function activate() {

        // Do plugin version check
        if ( ! $this->version_check() ) {
            return;
        }

        // Create our database table
        $this->get( 'database' )->create_table();

        // Add "wp_options" table options
        add_option( 'rocketgalleries_version', self::$version );
        add_option( 'rocketgalleries_settings',
            array(
                'assets' => 'compatibility'
            )
        );
        add_option( 'rocketgalleries_disable_welcome_panel', false );

        // Add user capabilities
        $this->manage_capabilities( 'add' );

        // Trigger hooks
        do_action_ref_array( 'rocketgalleries_activate', array( $this ) );

    }
    
    /**
     * Uninstall Hook
     *
     * @return void
     */
    public function uninstall() {

        // Delete our database table
        $this->get( 'database' )->delete_table();

        // Delete "wp_options" table options
        delete_option( 'rocketgalleries_version' );
        delete_option( 'rocketgalleries_gallery' );
        delete_option( 'rocketgalleries_settings' );
        delete_option( 'rocketgalleries_disable_welcome_panel' );

        // Remove user capabilities
        $this->manage_capabilities( 'remove' );

        // Trigger hooks
        do_action_ref_array( 'rocketgalleries_uninstall', array( $this ) );

    }
    
    /**
     *  Does a plugin version check, making sure the current Wordpress version is supported. If not, the plugin is deactivated and an error message is displayed.
     *
     *  @return boolean
     */
    public function version_check() {

        global $wp_version;

        if ( version_compare( $wp_version, '3.8', '<' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( __( sprintf( 'Sorry, but your version of WordPress, <strong>%s</strong>, is not supported. The plugin has been deactivated. <a href="%s">Return to the Dashboard.</a>', $wp_version, admin_url() ), 'rocketgalleries' ) );
            return false;
        }

        return true;

    }
    
    /**
     * Returns the ids of the various multisite blogs. Returns false if not a multisite installation.
     *
     * @return array|boolean
     */
    public function get_multisite_blogs() {

        global $wpdb;

        // Bail if not multisite
        if ( ! is_multisite() ) {
            return false;
        }

        // Get the blogs ids from database
        $query = "SELECT blog_id from $wpdb->blogs";
        $blogs = $wpdb->get_col($query);

        // Push blog ids to array
        $blog_ids = array();
        foreach ( $blogs as $blog ) {
            $blog_ids[] = $blog;
        }

        // Return the multisite blog ids
        return $blog_ids;

    }

    /**
     * Imports all WordPress galleries into the plugin
     *
     * @return void
     */
    public function import_wordpress_galleries() {

        global $wp_query;

        // Query the posts
        $posts = $wp_query->posts;

        // Get the shortcode regex pattern
        $pattern = get_shortcode_regex();

        // Loop through each post and find matches
        foreach ( $posts as $post ) {

            // Match the shortcodes
            if ( preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )&& array_key_exists( 3, $matches ) && in_array( 'gallery', $matches[2] ) ) {
            
                // Extract the ID's from the matches
                $ids = $matches[3][0];

                //

            }

        }
    
    }

    /**
     * Returns the plugin capabilities
     *
     * @return array
     */
    public function capabilities() {

        // Our plugin capabilities
        $capabilities = array(
            'rocketgalleries_add_gallery',
            'rocketgalleries_edit_galleries',
            'rocketgalleries_edit_settings'
        );

        // Allow user to filter in their own capabilities
        $capabilities = apply_filters( 'rocketgalleries_capabilities', $capabilities );

        return $capabilities;

    }
    
    /**
     * Manages (adds or removes) user capabilities
     *
     * @param  string $action The action we are currently doing; adding or removing the capabilities
     * @return void
     */
    public function manage_capabilities( $action ) {

        global $wp_roles;

        // Get the capabilities
        $capabilities = $this->capabilities();
        
        // Add capability for each applicable user role
        foreach ( $wp_roles->roles as $role => $info ) {

            // Get the user role object
            $user_role = get_role( $role );

            foreach ( $capabilities as $capability ) {
                if ( $action == 'add' ) {
                    $this->add_capability( $capability, $user_role );
                }
                elseif ( $action == 'remove' ) {
                    $this->remove_capability( $capability, $user_role );
                }
            }

        }

    }
    
    /**
     * Adds a user capability
     *
     * @param  string $capability The capability to add
     * @param  string $role       The user role to add the capability too
     * @return void
     */
    public function add_capability( $capability, $role ) {

        if ( $role->has_cap( 'edit_plugins' ) ) {
            $role->add_cap( $capability );
        }

    }
    
    /**
     * Removes a user capability
     *
     * @param  string $capability The capability to remove
     * @param  string $role       The user role to remove the capability from
     * @return void
     */
    public function remove_capability( $capability, $role ) {

        if ( $role->has_cap( $capability ) ) {
            $role->remove_cap( $capability );
        }

    }
    
    /**
     * Adds the admin menus
     *
     * @return void
     */
    public function add_menus() {

        global $menu;

        // Hook suffixs for admin menus
        $pages = apply_filters( 'rocketgalleries_menus', array(
            'rocketgalleries_add_gallery',
            'rocketgalleries_edit_galleries',
            'rocketgalleries_edit_settings'
        ) );

        // Default menu positioning
        $position = '100.1';

        // If enabled, relocate the plugin menus higher
        if ( apply_filters( 'rocketgalleries_relocate_menus', __return_true() ) ) {

            for ( $position = '40.1'; $position <= '100.1'; $position += '0.1' ) {

                // Ensure there is a space before and after each position we are checking, leaving room for our separators.
                $before = $position - '0.1';
                $after  = $position + '0.1';

                // Do the checks for each position. These need to be strings, hence the quotation marks.
                if ( isset( $menu[ "$position" ] ) ) {
                    continue;
                }
                if ( isset( $menu[ "$before" ] ) ) {
                    continue;
                }
                if ( isset( $menu[ "$after" ] ) ) {
                    continue;
                }

                // If we've successfully gotten this far, break the loop. We've found the position we need.
                break;

            }

        }

        // Toplevel menu
        $this->whitelist[] = add_menu_page(
            __( 'Galleries', 'rocketgalleries' ),
            __( 'Galleries', 'rocketgalleries' ),
            'rocketgalleries_edit_galleries',
            'rocketgalleries_edit_galleries',
            null,
            'dashicons-images-alt',
            /**
             * We need to ensure this is a string to be able to use decimal values for positioning
             */
            "$position"
        );

        // Submenus
        $this->whitelist[] = add_submenu_page(
            'rocketgalleries_edit_galleries',
            __( 'Galleries', 'rocketgalleries' ),
            __( 'All Galleries', 'rocketgalleries' ),
            'rocketgalleries_edit_galleries',
            'rocketgalleries_edit_galleries',
            array( $this, 'display_editor_view' )
        );
        $this->whitelist[] = add_submenu_page(
            'rocketgalleries_edit_galleries',
            __( 'Add New Gallery', 'rocketgalleries' ),
            __( 'Add New', 'rocketgalleries' ),
            'rocketgalleries_add_gallery',
            'rocketgalleries_add_gallery',
            array( $this, 'display_add_new_view' )
        );
        $this->whitelist[] = add_submenu_page(
            'rocketgalleries_edit_galleries',
            __( 'Edit Settings', 'rocketgalleries' ),
            __( 'Settings', 'rocketgalleries' ),
            'rocketgalleries_edit_settings',
            'rocketgalleries_edit_settings',
            array( $this, 'display_settings_view' )
        );

        // Add the menu separators if menus have been relocated (they are by default). Quotations marks ensure these are strings.
        if ( apply_filters( 'rocketgalleries_relocate_menus', __return_true() ) ) {
            $this->add_menu_separator( "$before" );
            $this->add_menu_separator( "$after" );
        }

        // Set flag if we are on one of our own plugin pages
        if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $pages ) ) {
            $this->is_plugin_page = true;
        }

    }
    
    /**
     * Create a separator in the admin menus, above and below our plugin menus
     *
     * @param  string $position The menu position to insert the separator
     * @return void
     */
    public function add_menu_separator( $position = '40.1' ) {

        global $menu;

        $index = 0;
        foreach ( $menu as $offset => $section ) {

            if ( substr( $section[2], 0, 9 ) == 'separator' ) {
                $index++;
            }

            if ( $offset >= $position ) {

                // Quotation marks ensures the position is a string. Integers won't work if we are using decimal values.
                $menu[ "$position" ] = array( '', 'read', "separator{$index}", '', 'wp-menu-separator' );
                break;

            }
            
        }
        ksort( $menu );
        
    }

    /**
     * Adds a media button (for inserting a gallery) to the Post Editor
     *
     * @param  int  $editor_id The editor ID
     * @return void
     */
    function add_media_button( $editor_id ) {

        // Print the button's HTML and CSS
        ?>
        <a href="#TB_inline?width=480&amp;inlineId=select-gallery" class="button thickbox insert-gallery" data-editor="<?php echo esc_attr( $editor_id ); ?>" title="<?php _e( 'Add a Gallery', 'awesomgallery' ); ?>"><?php echo '<span class="wp-media-buttons-icon dashicons dashicons-format-gallery"></span>' . __( ' Add Gallery', 'awesomgallery' ); ?></a>
        <?php

    }

    /**
     * Modal for "Add a Gallery" media button
     *
     * @return void
     */
    public function display_media_thickbox() {

        global $pagenow;

        // Bail if not in the post/page editor
        if ( $pagenow != 'post.php' && $pagenow != 'post-new.php' )
            return;

        // Get all of the slideshows
        $galleries = $this->get( 'database' )->all_rows();

        // Content HTML
        ?>
        <style type="text/css">
            .section {
                padding: 15px 15px 0 15px;
            }
        </style>
        <script type="text/javascript">
            function insertGallery() {

                // Get selected gallery ID
                var id = jQuery('#gallery').val();

                // Display alert and bail if no gallery was selected
                if ( id === '-1' )
                    return alert("<?php _e( 'Please select a gallery', 'rocketgalleries' ); ?>");

                // Send shortcode to editor
                send_to_editor('[rocketgalleries id="'+ id +'"]');

                // Close thickbox
                tb_remove();

            }
        </script>
        <div id="select-gallery" style="display: none;">
            <div class="section">
                <h2><?php _e( 'Add a gallery', 'rocketgalleries' ); ?></h2>
                <span><?php _e( 'Select a gallery to insert from the box below.', 'rocketgalleries' ); ?></span>
            </div>

            <div class="section">
                <select name="gallery" id="gallery">
                    <option value="-1"><?php _e( 'Select a gallery', 'rocketgalleries' ); ?></option>
                    <?php
                        foreach ( $galleries as $gallery ) {
                            echo "<option value='{$gallery->id}'>{$gallery->name} (ID #{$gallery->id})</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="section">
                <button id="insert-gallery" class="button-primary" onClick="insertGallery();"><?php _e( 'Insert Gallery', 'rocketgalleries' ); ?></button>
                <button id="close-gallery-thickbox" class="button-secondary" style="margin-left: 5px;" onClick="tb_remove();"><?php _e( 'Close', 'rocketgalleries' ); ?></a>
            </div>
        </div>
        <?php

    }

    /**
     * Adds our links to the admin bar
     *
     * @param  array $wp_admin_bar The current admin bar links
     * @return void
     */
    public function add_admin_bar_links( $wp_admin_bar ) {

        $args = array(
            'id'     => 'rocketgalleries_add_gallery',
            'title'  => __( 'Gallery', 'rocketgalleries' ),
            'parent' => 'new-content',
            'href'   => get_admin_url() . 'admin.php?page=rocketgalleries_add_gallery',
            'meta'   => array( 'class' => 'rocketgalleries-add-gallery' )
        );

        $wp_admin_bar->add_node( $args );

    }

    /**
     * Checks the theme for templates.
     * 
     * If any templates our found, the plugin will hide some settings that have now become inappropriate.
     * It will also prevent the plugin from loading it's own CSS and JS.
     *
     * @return void
     */
    public function check_theme_for_templates() {

        // Get the current template path. We'll use this to check if the template directory is the same as the theme directory.
        $template = $this->get( 'template_loader' )->get_template_part( 'rocketgalleries', 'gallery', false );

        // Get the template directory
        $template_dirname = pathinfo( $template, PATHINFO_DIRNAME );

        // If a template directory is the theme, a template has been found.
        if ( $template_dirname == get_stylesheet_directory() ) {

            // Remove everything that is now not needed as the theme is using it's own front-end
            remove_action( 'init', array( $this, 'enqueue_gallery_assets' ) );

            // An action for good measure
            add_action( 'init', create_function( '', 'do_action( \'rocketgalleries_theme_has_template\' );' ) );
            
        }
        
    }

    /**
     * Function used to enqueue the "admin_theme_has_template_classes" function correctly.
     * Only fired when the plugin has detected that the theme has a custom front-end template for the plugin.
     *
     * This function should be called using the "after_setup_theme" action.
     *
     * @return void
     */
    public function queue_has_template_class() {

        // Only load the action if we're on one of our own plugin pages
        add_filter( 'admin_body_class', array( $this, 'admin_has_template_class' ) );

    }

    /**
     * Adds a class to the admin body classes the tells us that the theme being used has a custom front-end template for our plugin.
     *
     * @return array
     */
    public function admin_has_template_class( $class ) {

        // Add the class if we are on one of our own plugin pages
        if ( $this->is_plugin_page ) {
            $class .= ' rocketgalleries-theme-has-template ';
        }

        return $class;

    }
    
    /**
     * Queues an admin message to be displayed
     *
     * @param string $text The message text string
     * @param string $type The type of message queued; error, etc.
     * @return void
     */
    public function queue_message( $text, $type ) {

        if ( ! $this->is_plugin_page ) {
            return;
        }

        // Parse the message HTML
        $message = "<div class='message $type'><p>$text</p></div>";

        // Queue the message via actions
        add_action( 'admin_notices', create_function( '', 'echo "'. $message .'";' ) );

    }
    
    /**
     * Queues messages for actions that have been completed (generally through a redirect).
     *
     * @return void
     */
    public function queue_action_messages() {

        // Bail if we aren't on a Rocket Galleries page
        if ( ! $this->is_plugin_page ) {
            return;
        }

        if ( isset( $_GET['message'] ) ) {

            // Get the message
            $message = $_GET['message'];

            // Display appropriate message
            switch ( $message ) {

                // Bulk action responses
                case 'galleries_duplicated' :
                    $this->queue_message( __( 'Galleries have been <strong>duplicated</strong> successfully.', 'rocketgalleries' ), 'updated' );
                    break;

                case 'galleries_not_duplicated' :
                    $this->queue_message( __( 'Failed to duplicate galleries. An error has occurred. Please try again or contact support.', 'rocketgalleries' ), 'error' );
                    break;

                case 'galleries_deleted' :
                    $this->queue_message( __( 'Galleries have been <strong>deleted</strong> successfully.', 'rocketgalleries' ), 'updated' );
                    break;

                case 'galleries_not_deleted' :
                    $this->queue_message( __( 'Failed to delete galleries. An error has occurred. Please try again or contact support.', 'rocketgalleries' ), 'error' );
                    break;

                // Single action responses
                case 'gallery_duplicated' :
                    $this->queue_message( __( 'Gallery has been <strong>duplicated</strong> successfully.', 'rocketgalleries' ), 'updated' );
                    break;

                case 'gallery_not_duplicated' :
                    $this->queue_message( __( 'Failed to duplicate gallery. An error has occurred. Please try again or contact support.', 'rocketgalleries' ), 'error' );
                    break;

                case 'gallery_deleted' :
                    $this->queue_message( __( 'Gallery has been <strong>deleted</strong> successfully.', 'rocketgalleries' ), 'updated' );
                    break;

                case 'gallery_not_deleted' :
                    $this->queue_message( __( 'Failed to delete slideshow. An error has occurred. Please try again or contact support.', 'rocketgalleries' ), 'error' );
                    break;

            }

        }

    }

    /**
     * Does security nonce checks
     *
     * @param  string  $action The action being carried out
     * @param  string  $page   The current plugin page slug
     * @return boolean
     */
    public function security_check( $action, $page ) {

        if ( check_admin_referer( "rocketgalleries-{$action}_{$page}", "rocketgalleries-{$action}_{$page}" ) ) {
            return true;
        }

        return false;

    }

    /**
     * Does validation, ensuring integer are integers and booleans are booleans, etc.
     *
     * @param  array $values The values to validate
     * @return array
     */
    public function validate( $values ) {

        // Object flag
        $is_object = ( is_object( $values ) ) ? true : false;

        // Convert objects to arrays
        if ( $is_object ) {
            $values = (array) $values;
        }

        // Get settings and do some validation
        foreach ( $values as $key => $value ) {

            // Validators
            if ( is_numeric( $value ) )
                $values[ $key ] = filter_var( $value, FILTER_VALIDATE_INT );
            elseif ( $value === 'true' || $value === 'false' )
                $values[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );

            // Recurse if necessary
            if ( is_object( $value ) || is_array( $value ) )
                $values[ $key ] = $this->validate( $value );

        }

        // Convert back to an object
        if ( $is_object ) {
            $values = (object) $values;
        }

        return stripslashes_deep( $values );

    }
    
    /**
     * Does admin actions (if appropriate)
     *
     * @return void
     */
    public function do_actions() {

        // Bail if we aren't on a RocketGalleries page
        if ( ! $this->is_plugin_page ) {
            return;
        }

        // Do admin actions
        do_action( "{$_GET['page']}_actions", $_GET['page'] );

    }
    
    /**
     * Gallery based actions
     *
     * @param  string $page The current plugin page slug
     * @return string
     */
    public function do_gallery_actions( $page ) {

        // Disable welcome panel if it is dismissed
        if ( isset( $_GET['disable_welcome_panel'] ) ) {
            update_option( 'rocketgalleries_disable_welcome_panel', filter_var( $_GET['disable_welcome_panel'], FILTER_VALIDATE_BOOLEAN ) );
        }

        // Save or update a slideshow. Whichever is appropriate.
        if ( isset( $_POST['save'] ) ) {

            // Security check. Page is hardcoded to prevent errors when adding a new slidesow)
            if ( ! $this->security_check( 'save', $page ) ) {
                wp_die( __( 'Security check has failed. Save has been prevented. Please try again.', 'rocketgalleries' ) );
                exit();
            }

            // Saves or add the row, returning the response
            $response = $this->get( 'database' )->add_or_update_row( $_GET['edit'] );

            // Check for false response explicity to prevent incorrect error reports. MySQL returns 0 if save is successful but no rows were affected.
            if ( $response === false ) {
                return $this->queue_message( __( 'Failed to save gallery. An error has occurred. Please try again or contact support.', 'rocketgalleries' ), 'error' );
            }
            else {
                return $this->queue_message( __( 'Gallery has been <strong>saved</strong> successfully.', 'rocketgalleries' ), 'updated' );
            }

        }

        // Bulk actions
        if ( isset( $_GET['action'] ) && isset( $_GET['action2'] ) ) {

            // Top bulk actions option always takes preference. If both actions are set, we bail to avoid confusion
            if ( $_GET['action'] !== '-1' && $_GET['action2'] !== '-1' ) {
                wp_redirect( "admin.php?page={$page}" );
                return;

            }
            elseif ( $_GET['action'] !== '-1' ) {
                $action = $_GET['action'];
            }
            elseif ( $_GET['action2'] !== '-1' ) {
                $action = $_GET['action2'];
            }
            else {

                // Get the current page
                $paged = ( isset( $_GET['paged'] ) ) ? $_GET['paged'] : 1;

                // Redirect
                wp_redirect( "admin.php?page={$page}&paged={$paged}" );
                return;

            }

            // Bail if IDs aren't an array
            if ( ! isset( $_GET['id'] ) || ! is_array( $_GET['id'] ) ) {
                wp_redirect( "admin.php?page={$page}" );
                return;
            }

            // Security check. Page is hardcoded to prevent errors when adding a new gallery
            if ( ! $this->security_check( 'bulk', $page ) ) {
                wp_die( __( 'Security check has failed. Bulk action has been prevented. Please try again.', 'rocketgalleries' ) );
                exit();
            }

            // Do appropriate action
            if ( $action == 'duplicate' ) {

                // Duplicate gallery
                foreach ( $_GET['id'] as $id ) {
                    $response = $this->get( 'database' )->duplicate_row( $id );
                }

                // Check for success or failure
                if ( $response === false ) {
                    wp_redirect( "admin.php?page={$page}&message=galleries_not_duplicated" );
                    return;
                }
                else {
                    wp_redirect( "admin.php?page={$page}&message=galleries_duplicated" );
                    return;
                }

            }
            elseif ( $action == 'delete' ) {

                // Delete galleries
                foreach ( $_GET['id'] as $id ) {
                    $response = $this->get( 'database' )->delete_row( $id );
                }

                // Check for success or failure
                if ( $response === false ) {
                    wp_redirect( "admin.php?page={$page}&message=galleries_not_deleted" );
                    return;
                }
                else {
                    wp_redirect( "admin.php?page={$page}&messagegalleriess_deleted" );
                    return;
                }

            }

        }
        // Single actions
        elseif ( isset( $_GET['action'] ) ) {

            // Bail if no gallery ID has been specified
            if ( ! isset( $_GET['id'] ) ) {
                return;
            }

            // Do appropriate action
            if ( $_GET['action'] == 'duplicate' ) {

                // Security check. Page is hardcoded to prevent errors when adding a new gallery)
                if ( ! $this->security_check( 'duplicate', $page ) ) {
                    wp_die( __( 'Security check has failed. Duplicate has been prevented. Please try again.', 'rocketgalleries' ) );
                    exit();
                }

                // Duplicate gallery
                $response = $this->get( 'database' )->duplicate_row( $_GET['id'] );

                // Check for success or failure
                if ( $response === false ) {
                    wp_redirect( "admin.php?page={$page}&message=gallery_not_duplicated" );
                    return;
                }
                else {
                    wp_redirect( "admin.php?page={$page}&message=gallery_duplicated" );
                    return;
                }

            }
            elseif ( $_GET['action'] == 'delete' ) {

                // Security check. Page is hardcoded to prevent errors when adding a new gallery
                if ( ! $this->security_check( 'delete', $page ) ) {
                    wp_die( __( 'Security check has failed. Delete has been prevented. Please try again.', 'rocketgalleries' ) );
                    exit();
                }

                // Delete gallery
                $response = $this->get( 'database' )->delete_row( $_GET['id'] );

                // Check for success or failure
                if ( $response === false ) {
                    wp_redirect( "admin.php?page={$page}&message=gallery_not_deleted" );
                    return;
                }
                else {
                    wp_redirect( "admin.php?page={$page}&message=gallery_deleted" );
                    return;
                }

            }

        }

    }
    
    /**
     * Settings page actions
     *
     * @param  string $page The current plugin page slug
     * @return string
     */
    public function do_settings_actions( $page ) {

        // Reset plugin
        if ( isset( $_POST['reset'] ) ) {

            // Security check
            if ( ! $this->security_check( 'reset', $page ) ) {
                wp_die( __( 'Security check has failed. Reset has been prevented. Please try again.', 'rocketgalleries' ) );
                exit();
            }

            // Do reset
            $this->uninstall();
            $this->activate();

            // Queue message
            return $this->queue_message( __( 'Plugin has been reset successfully.', 'rocketgalleries' ), 'updated' );

        }

        // Save the settings
        if ( isset( $_POST['save'] ) ) {

            // Security check
            if ( ! $this->security_check( 'save', $page ) ) {
                wp_die( __( 'Security check has failed. Save has been prevented. Please try again.', 'rocketgalleries' ) );
                exit();
            }

            // Get settings and do some validation
            $settings = $this->validate( $_POST['settings'] );

            // Update database option and get the response
            update_option( 'rocketgalleries_settings', stripslashes_deep( $settings ) );

            // Show update message
            return $this->queue_message( __( 'Settings have been <strong>saved</strong> successfully.', 'rocketgalleries' ), 'updated' );

        }

    }
    
    /**
     * Executes a shortcode handler
     *
     * @param  array  $atts The shortcode attributes
     * @return string
     */
    public function do_shortcode( $atts ) {

        // Extract shortcode attributes
        extract(
            shortcode_atts(
                array( 'id' => false ),
                $atts
            )
        );

        // Display error message if no ID has been entered
        if ( ! $id ) {
            return __( 'Looks like you\'ve forgotten to add a gallery ID to this shortcode. Oh dear!', 'rocketgalleries' );
        }

        // Get the gallery
        $gallery = $this->get( 'database' )->get_row( $id );

        // Bail if no valid gallery was found
        if ( ! isset( $gallery->id ) ) {
            return sprintf( '<p style="background-color: #ffebe8; border: 1px solid #c00; border-radius: 4px; padding: 8px !important;">' . __( 'The gallery specified (ID #%d) does not appear to exist.', 'rocketgalleries' ) . '</p>', $id );
        }

        /**
         * Print the gallery HTML, catch the output and return it.
         * We do this to avoid a nasty WordPress formatting bug that would cause the gallery
         * to always be printed at the top of the post/page unlesss the HTML was returned.
         */
        ob_start();
        $this->get( 'gallery' )->display( $gallery );
        return ob_get_clean();

    }
    
    /**
     * Register all admin stylesheets
     *
     * @return void
     */
    public function register_all_styles() {

        // Get the extension
        $ext = ( apply_filters( 'rocketgalleries_debug_styles', __return_false() ) ) ? '.css' : '.min.css';

        // Register styles
        wp_register_style( 'rg-admin', plugins_url( dirname( plugin_basename( self::get_file() ) ) . DIRECTORY_SEPARATOR .'css'. DIRECTORY_SEPARATOR .'admin'. $ext ), false, self::$version );
        wp_register_style( 'rg-gallery', plugins_url( dirname( plugin_basename( self::get_file() ) ) . DIRECTORY_SEPARATOR .'css'. DIRECTORY_SEPARATOR .'gallery'. $ext ), false, self::$version );

    }
    
    /**
     * Register all admin scripts
     *
     * @since void
     */
    public function register_all_scripts() {

        // Get the extension
        $ext = ( apply_filters( 'rocketgalleries_debug_scripts', __return_false() ) ) ? '.js' : '.min.js';

        // Register scripts
        wp_register_script( 'rg-admin',  plugins_url( dirname( plugin_basename( self::get_file() ) ) . DIRECTORY_SEPARATOR .'js'. DIRECTORY_SEPARATOR .'admin'. $ext ), array( 'jquery', 'jquery-ui-sortable', 'backbone' ), self::$version, true );

    }

    /**
     * Queues gallery CSS and JS for loading in either the header or footer, depending on the plugin settings.
     *
     * @since void
     */
    public function enqueue_gallery_assets() {

        // Get the settings
        $settings = get_option( 'rocketgalleries_settings' );

        // Determine the hook based on the settings
        $hook = ( $settings['assets'] == 'optimized' ) ? 'wp_footer' : 'wp_enqueue_scripts';

        /**
         * If we've set the settings to "Optimized", we need to prevent this function from loading the code on every page's footer.
         * Instead, we want to conditionally load it only on pages where a gallery is being used, which is much more efficient and "optimized".
         * To do this, we're simply going to check that the "wp_head" action has been fired.
         * This will ensure that the next time this code is fired, it's when we are displaying gallery.
         * To trigger this function again, we will hook into our "rocketgalleries_after_display_gallery" action.
         */
        if ( $settings['assets'] == 'optimized' && ! did_action( 'wp_head' ) ) {
            add_action( 'rocketgalleries_after_display_gallery', create_function( '', 'RocketGalleries::get_instance()->enqueue_gallery_assets();' ) );
            return;
        }

        // Enqueue the assets appropriately
        add_action( $hook, create_function( '', 'wp_enqueue_style( \'rg-gallery\' );' ) );
        add_action( $hook, create_function( '', 'do_action( \'rocketgalleries_enqueue_gallery_assets\' );' ) );

    }
    
    /**
     * Loads admin stylesheets
     *
     * @param string $hook The page hook, used to check the current page against our whitelist.
     * @since void
     */
    public function enqueue_admin_styles( $hook ) {

        // Bail if not an Rocket Galleries page
        if ( ! in_array( $hook, $this->whitelist ) ) {
            return;
        }

        // Load styles
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'rg-admin' );

        // Allow developers to hook their own styles into our pages
        do_action( 'rocketgalleries_enqueue_admin_styles' );

    }
    
    /**
     * Loads admin javascript files
     *
     * @param string $hook The page hook, used to check the current page against our whitelist.
     * @since void
     */
    public function enqueue_admin_scripts( $hook ) {

        // Bail if not one of our plugin pages
        if ( ! in_array( $hook, $this->whitelist ) ) {
            return;
        }

        // Print localized variables
        wp_localize_script( 'rg-admin', 'rocketgalleries', $this->localizations() );

        // Load scripts
        wp_enqueue_media();
        wp_enqueue_script( 'rg-admin' );

        // Allow developers to hook their own scripts into our pages
        do_action( 'rocketgalleries_enqueue_admin_scripts' );

    }
    
    /**
     * Translations localized via Javascript
     *
     * @since array
     */
    public function localizations() {

        return array(
            'plugin_url'    => '/wp-content/plugins/'. dirname( plugin_basename( self::get_file() ) ) .'/',
            'warn'          => __( 'Are you sure you wish to do this? This cannot be reversed.', 'rocketgalleries' ),
            'delete_image'  => __( 'Are you sure you wish to delete this image? This cannot be reversed.', 'rocketgalleries' ),
            'delete_images' => __( 'Are you sure you wish to delete all of this galleries images? This cannot be reversed.', 'rocketgalleries' ),
            'media_upload'  => array(
                'title'           => __( 'Add Images to Gallery', 'rocketgalleries' ),
                'button'          => __( 'Insert into gallery', 'rocketgalleries' ),
                'change'          => __( 'Use this image', 'rocketgalleries' ),
                'discard_changes' => __( 'Are you sure you wish to discard changes?', 'rocketgalleries' )
            )
        );

    }
    
    /**
     * Prints the backbone templates used in the admin area
     *
     * @since void
     */
    public function print_backbone_templates() {

        // Bail if not a RocketGalleries page
        if ( ! $this->is_plugin_page ) {
            return;
        }

        // Load the "Edit Gallery" thumbnail template
        echo '<script type="text/html" id="tmpl-gallery-thumbnail">';
        require dirname( self::get_file() ) . DIRECTORY_SEPARATOR .'backbone'. DIRECTORY_SEPARATOR .'gallery-thumbnail.php';
        echo '</script>';

        // Load the media sidebar for the "Add Image" template
        echo '<script type="text/html" id="tmpl-gallery-media-sidebar">';
        require dirname( self::get_file() ) . DIRECTORY_SEPARATOR .'backbone'. DIRECTORY_SEPARATOR .'gallery-media-sidebar.php';
        echo '</script>';
        
    }
    
    /**
     * Displays the appropriate editor view (either the editor itself or a list view).
     *
     * @since void
     */
    public function display_editor_view() {

        // Get the page
        $page = $_GET['page'];

        // Show the appropriate view
        if ( isset( $_GET['edit'] ) ) {

            // Get the gallery ID
            $gallery = $this->get( 'database' )->get_row( $_GET['edit'] );

            // Display the editor
            require dirname( self::get_file() ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'edit-gallery.php';

        }
        else {

            // Get the number of results to paginate by, with a handy filter to make it easy to change.
            $paginate_by = apply_filters( 'rocketgalleries_list_galleries_paginate_by', 20 );

            // Get the paginated galleries
            $paginated_results = $this->get( 'database' )->paginate_rows( $paginate_by );

            // Get the maximum number of pages
            $max_pages = $paginated_results->max_pages;

            // Get the galleries
            $galleries = $paginated_results->rows;

            // Display the list
            require dirname( self::get_file() ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'list-galleries.php';

        }

    }
    
    /**
     * Display the "Add New" view. Here we just display the editor view,except with default gallery values.
     * This is far more straightforward than having two separate views with all of the same HTML (less duplication for the win).
     *
     * @since void
     */
    public function display_add_new_view() {

        /**
         * Here we're hardcoding the page to force the "Edit Gallery" actions to occur.
         * Essentially, this page's slug is just an alias for "rocketgalleries_edit_galleries" with an ID that doesn't yet exist.
         */
        $page = 'rocketgalleries_edit_galleries';

        // Get the default gallery values
        $gallery = $this->get( 'database' )->get_row_defaults();

        // Display the editor view
        require dirname( self::get_file() ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'edit-gallery.php';

    }
    
    /**
     * Display the "Settings" view.
     *
     * @since void
     */
    public function display_settings_view() {

        // Get the page
        $page = $_GET['page'];

        // Get the settings
        $settings = get_option( 'rocketgalleries_settings' );

        // Display the settings
        require dirname( self::get_file() ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'edit-settings.php';

    }

}