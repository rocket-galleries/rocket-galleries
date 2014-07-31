<?php

/**
 * Database connection singleton
 *
 * @author Matthew Ruddy
 */
class RG_Database {

    /**
     * Class instance
     *
     * @var RG_Database
     */
    private static $instance;

    /**
     * Plugin database table
     *
     * @var string
     */
    private static $db_table = 'rocketgalleries';

    /**
     * Plugin database columns
     *
     * @var array
     */
    private static $db_columns = array(
        'id'      => 'bigint(20) unsigned NOT NULL AUTO_INCREMENT',
        'name'    => 'varchar(200) NOT NULL',
        'author'  => 'varchar(100) NOT NULL',
        'images'  => 'longtext NOT NULL',
        'general' => 'longtext NOT NULL',
    );

    /**
     * Getter method for retrieving the class instance
     *
     * @return RG_Database
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

        // Actions & Filters
        add_filter( 'rocketgalleries_query_row', array( $this, 'decode_json' ) );
        add_filter( 'rocketgalleries_add_row', array( $this, 'encode_json' ) );
        add_filter( 'rocketgalleries_update_row', array( $this, 'encode_json' ) );
        add_filter( 'rocketgalleries_pre_gallery', array( $this, 'merge_defaults' ) );

    }

    /**
     * Returns our database table name (prefixed)
     *
     * @return string
     */
    public function get_table_name() {

        global $wpdb;

        return $wpdb->prefix . self::$db_table;

    }

    /**
     * Returns our database table columns
     *
     * @return array
     */
    public function get_table_columns() {

        return self::$db_columns;

    }

    /**
     * Creates the plugin's database table
     *
     * @return void
     */
    public function create_table() {

        global $wpdb;

        // Get charset & collation
        if ( ! empty( $wpdb->charset ) ) {
            $charset_collation = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if ( ! empty( $wpdb->collation ) ) {
            $charset_collation .= " COLLATE $wpdb->collate";
        }

        // Get the database table name
        $table_name = $this->get_table_name();

        // Start building the database query
        $query = "CREATE TABLE IF NOT EXISTS $table_name (";

        // Add the columns to the query
        foreach ( $this->get_table_columns() as $name => $type ) {
            $query .= "`{$name}` {$type} NOT NULL,";
        }

        // Finish the query
        $query .= "PRIMARY KEY (id) ) $charset_collation;";
        
        // Run the WordPress upgrade schema script and execute the query
        require_once( ABSPATH .'wp-admin/includes/upgrade.php' );
        dbDelta( $query );

    }

    /**
     * Deletes the plugin's database table
     *
     * @return void
     */
    public function delete_table() {
        
        global $wpdb;

        $table_name = $this->get_table_name();
        $wpdb->query( "DROP TABLE $table_name" );

    }

    /**
     * Returns true of false based on whether a string is JSON encoded
     * 
     * @param  string  $string The string to check
     * @return boolean
     */
    public function is_json( $string ) {

        // Decode the string
        $data = @json_decode( $string );

        // Return true or false based on if we have encountered any JSON errors
        return ( ! is_null( $data ) ) ? true : false;

    }

    /**
     * Encodes arrays and objects as JSON
     *
     * @param  array|object $data The data object
     * @return array|object
     */
    public function encode_json( $data ) {

        // Object flag
        $is_object = is_object( $data );

        // Ensure data is an array for the purposes of encoding
        $data = (array) $data;

        // Loop through each value
        foreach ( $data as $key => $value ) {

            // Check if the value is JSON encoded & is an array/object. If not, encode it.
            if ( ! $this->is_json( $value ) && ( is_array( $value ) OR is_object( $value ) ) ) {
                $data[ $key ] = json_encode( $value );
            }

        }

        return ( $is_object ) ? (object) $data : (array) $data;

    }

    /**
     * Decodes the JSON values provided
     *
     * @param  object $data The data object
     * @return object
     */
    public function decode_json( $data ) {

        // Object flag
        $is_object = is_object( $data );

        // Ensure data is an array for the purposes of encoding
        $data = (array) $data;

        // Loop through each value and decode any JSON strings
        foreach ( $data as $key => $value ) {

            // Check if the value is JSON encoded. If so, decode it.
            if ( $this->is_json( $value ) ) {
                $data[ $key ] = json_decode( $value );
            }

        }

        return ( $is_object ) ? (object) $data : (array) $data;

    }

    /**
     * Rounds the provided number up using ceil function, but ensures that the minimum possible value is 1.
     * Used for calculating pagination numbers, where we can't have 0 pages.
     *
     * @param  int $int The number to round
     * @return int
     */
    public function round_up( $int ) {

        // Round the number up
        $round_up = ceil( $int );

        // Return one if the value rounded was 0
        if ( $round_up == 0 ) {
            return 1;
        }

        return $round_up;

    }

    /**
     * Merges the default database values into the provided object
     *
     * @param  array $data The data provided
     * @return array
     */
    public function merge_defaults( $data = array() ) {

        return array_merge( (array) $this->get_row_defaults(), $data );
        
    }

    /**
     * Returns the query we use for listing a database table row
     *
     * @param  string      $attribute The attribute to query by
     * @param  mixed       $value     The attribute value
     * @return array|false
     */
    public function query_row( $attribute, $value ) {
        
        global $wpdb;

        $table_name    = $this->get_table_name();
        $table_columns = $this->get_table_columns();

        // Ensure attribute is within whitelist
        if ( ! array_key_exists( $attribute, $table_columns ) ) {
            return false;
        }

        // Prepare the query
        $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE `$attribute` = %s", $value );

        // Get the row
        $row = $wpdb->get_row( $query );

        // Return & filter
        return apply_filters( 'rocketgalleries_query_row', $row );

    }

    /**
     * Returns the query we use for listing all of the database table rows
     *
     * @param  boolean $paginate    Toggles pagination
     * @param  int     $paginate_by How many results to paginate by
     * @param  string  $attribute   The attribute to query by
     * @param  mixed   $value       The attribute value
     * @return array
     */
    public function query_rows( $args = array() ) {

        global $wpdb;

        $table_name    = $this->get_table_name();
        $table_columns = $this->get_table_columns();

        // Establish default arguments
        $defaults = array(
            'orderby'    => 'id',
            'order'      => 'asc',
            'filterby'   => false,
            'filter'     => false,
            's'          => false,
            'paginate'   => false,
            'paginateby' => 20,
            'paged'      => false,
            'max_pages'  => 1
        );

        // Merge defaults with arguments passed to the method
        $args = array_merge( $defaults, $args );

        // Construct the beginning of the query
        $query[] = "SELECT * FROM $table_name";

        // Search, if appropriate.
        if ( $args['s'] ) {
            $query[] = $wpdb->prepare( "WHERE INSTR(email, %s) > 0", $args['s'] );
        }

        // Filter, if appropriate.
        if ( $args['filterby'] && $args['filter'] ) {

            // Whitelist to prevent SQL injection attacks
            if ( array_key_exists( $args['filterby'], $table_columns ) ) {

                // Add "And" if we already have a where query
                if ( $args['s'] ) {
                    $query[] = "AND";
                }

                $query[] = $wpdb->prepare( "WHERE `{$args['filterby']}` = %s", $args['filter'] );

            }
        }

        // Order, if appropriate
        if ( $args['orderby'] && $args['order'] ) {

            // Whitelist to prevent ordering by invalid attributes
            if ( array_key_exists( $args['orderby'], $table_columns ) ) {
                $query[] = "ORDER BY `{$args['orderby']}` {$args['order']}";
            }

        }

        // Paginate, if appropriate.
        if ( $args['paginateby'] && $args['paginate'] ) {

            // Calculate the pagination offset based on the current page
            $offset = ( $args['paged'] ) ? ( $args['paginateby'] * ( $args['paged'] - 1 ) ) : 0;

            // Add the pagination query
            $query[] = $wpdb->prepare( "LIMIT %d OFFSET %d", $args['paginateby'], $offset );

            // Set max number of pages
            $args['max_pages'] = $this->round_up( $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" ) / $args['paginateby'] );

        }

        // Get the rows
        $rows = $wpdb->get_results( implode( ' ', $query ) );

        // Run each row through the query row filter
        foreach ( $rows as $key => $row ) {
            $rows[ $key ] = apply_filters( 'rocketgalleries_query_row', $row );
        }

        // Return & filter for good measure
        return (object) array(
            'rows'      => $rows,
            'max_pages' => $args['max_pages']
        );

    }

    /**
     * Returns default column values
     *
     * @return object
     */
    public function get_row_defaults() {

        // Get the current WordPress user
        $current_user = wp_get_current_user();

        $row          = new stdClass();
        $row->id      = $this->next_index();
        $row->name    = __( 'New Gallery', 'rocketgalleries' );
        $row->author  = $current_user->user_login;
        $row->images  = array();
        $row->general = (object) array( 
            'randomize' => '',
            'source'    => 'default',
            'link_to'   => 'post'
        );

        return apply_filters( 'rocketgalleries_get_row_defaults', $row );

    }

    /**
     * Returns a row from the database table
     *
     * @param  int    $id The row to get
     * @return object
     */
    public function get_row( $id ) {
        
        // Get the row
        $row = $this->query_row( 'id', $id );

        // Bail if no row was found
        if ( ! $row ) {
            return false;
        }

        // Return row JSON decoded and validated
        return apply_filters( 'rocketgalleries_get_row', $row, $id );

    }

    /**
     * Returns all the rows from the database table
     *
     * @return array
     */
    public function all_rows() {

        // Execute the query
        $rows = $this->query_rows();

        // Return the queried rows
        return apply_filters( 'rocketgalleries_all_rows', $rows->rows );

    }

    /**
     * Adds or updates a row, depending on whether it already exists
     *
     * @param  int       $id     The row ID
     * @param  array     $values The updated row values
     * @return int|false
     */
    public function add_or_update_row( $id, $values ) {

        global $wpdb;

        $table_name = $this->get_table_name();

        // Attempt to get the row
        $row = $this->get_row( $id );

        /**
         * If we've managed to successfully get the row, it exists. Update it.
         * Otherwise, add it.
         */
        if ( $row ) {
            return $this->update_row( $id, $values );
        }
        else {
            return $this->add_row( $values );
        }

    }

    /**
     * Updates a row
     *
     * @param  int       $id     The row ID
     * @param  array     $values The updated row values
     * @return int|false
     */
    public function update_row( $id, $values ) {
        
        global $wpdb;

        $table_name = $this->get_table_name();

        // Filter values for good measure (allows for validation through filtering, etc).
        $values = (array) apply_filters( 'rocketgalleries_update_row', $values, $id );

        // Update the row
        return $wpdb->update( $table_name, $values, array( 'id' => $id ) );

    }

    /**
     * Adds a new row
     *
     * @param  array     $values The row values to add
     * @return int|false
     */
    public function add_row( $values ) {
        
        global $wpdb;

        $table_name = $this->get_table_name();

        // Filter values for good measure (allows for validation through filtering, etc).
        $values = (array) apply_filters( 'rocketgalleries_add_row', $values );

        // Add the row
        return $wpdb->insert( $table_name, $values );

    }

    /**
     * Duplicates a row
     *
     * @param  int   $id The ID of the row to duplicate
     * @return mixed
     */
    public function duplicate_row( $id ) {

        // Get the row to be duplicated
        $row = $this->get_row( $id );

        // Remove the row ID
        unset( $row->id );

        // Append 'Copy' to the row name
        $row->name = $row->name . __( ' Copy', 'rocketgalleries' );

        // Re-encode the images into JSON (when we get a row they are decoded for ease of use)
        $row->images = json_encode( $row->images );

        // Convert row object to array (necessary for database insertion)
        $row = get_object_vars( $row );

        // Add the new duplicated row
        return $this->add_row( $row );

    }

    /**
     * Deletes a row
     *
     * @param  int   $id The row ID
     * @return mixed
     */
    public function delete_row( $id ) {

        global $wpdb;

        $table_name = $this->get_table_name();

        // Delete the row from the database
        $query = $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d", $id );
        $results = $wpdb->query( $query );

        return $results;

    }

    /**
     * Returns the next table index
     *
     * @return int
     */
    public function next_index() {

        global $wpdb;
        
        $table_name = $this->get_table_name();

        // Query the table status, which will allow us to get the next table index.
        $query = $wpdb->get_results( "SHOW TABLE STATUS LIKE '$table_name'", ARRAY_A );

        // Return the next index
        return $query[0]['Auto_increment'];
    
    }

}