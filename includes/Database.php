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
     * Returns true of false based on whether a string is JSON encoded
     * 
     * @param  string  $string The string to check
     * @return boolean
     */
    private function is_json( $string ) {

        // Decode the string
        $data = @json_decode( $string );

        // Return true or false based on if we have encountered any JSON errors
        return ( ! is_null( $data ) ) ? true : false;

    }

    /**
     * Decodes the row JSON values
     *
     * @param object $row The row object
     * @return object
     */
    private function decode_json( $row ) {

        // Loop through each row value and decode any JSON strings
        foreach ( $row as $key => $value ) {

            // Check if the value is JSON encoded. If so, decode it.
            if ( $this->is_json( $value ) ) {
                $row->$key = json_decode( $value );
            }

        }

        return $row;

    }

    /**
     * Rounds the provided number up using ceil function, but ensures that the minimum possible value is 1.
     * Used for calculating pagination numbers, where we can't have 0 pages.
     *
     * @param  int $int The number to round
     * @return int
     */
    private function round_up( $int ) {

        // Round the number up
        $round_up = ceil( $int );

        // Return one if the value rounded was 0
        if ( $round_up == 0 ) {
            return 1;
        }

        return $round_up;

    }

    /**
     * Returns the query we use for listing all of the database table rows
     *
     * @param boolean $paginate    Toggles pagination
     * @param int     $paginate_by How many results to paginate by
     */
    private function query_rows( $paginate = false, $paginate_by = 20 ) {

        global $wpdb;

        $table_name = $this->get_table_name();
        $table_columns = $this->get_table_columns();

        // Fallbacks for "Order By" and "Order" attributes
        $orderby = 'id';
        $order = 'asc';

        // If the "Order By" value has been specified, let's set it (if appropriate).
        if ( isset( $_GET['orderby'] ) && is_admin() && array_key_exists( $_GET['orderby'], $table_columns ) ) {
            $orderby = $_GET['orderby'];
        }

        // Do the same for the "Order" attribute
        if ( isset( $_GET['order'] ) && is_admin() ) {
            $order = $_GET['order'];
        }

        // Construct the database query
        $start = "SELECT * FROM $table_name ";
        $middle = ( isset( $_GET['s'] ) ) ? $wpdb->prepare( "WHERE INSTR(name, %s) > 0 ", $_GET['s'] ): " ";
        $end = "ORDER BY $orderby $order";

        // If pagination has been specified, add it to query
        if ( $paginate ) {

            // Calculate the pagination offset based on the current page
            $offset = ( isset( $_GET['paged'] ) ) ? ( $paginate_by * ( $_GET['paged'] - 1 ) ) : 0;

            // Add the pagination query
            $end .= " LIMIT {$paginate_by} OFFSET {$offset}";

        }

        // Execute the query & return the results, with some additional info.
        return (object) array(
            'rows'      => $wpdb->get_results( $start . $middle . $end ),
            'max_pages' => $this->round_up( $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" ) / $paginate_by )
        );

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
        $query = "CREATE TABLE IF NOT EXISTS $table_name ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT,";

        // Add the columns to the query
        foreach ( $this->get_table_columns() as $name => $type ) {
            $query .= "{$name} {$type} NOT NULL,";
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

        $table_name = self::get_table_name();
        $wpdb->query( "DROP TABLE $table_name" );

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
        $row->general = (object) array( 'randomize' => '' );

        return apply_filters( 'rocketgalleries_get_row_defaults', $row );

    }

    /**
     * Returns a row from the database table
     *
     * @param  int    $id The row to get
     * @return object
     */
    public function get_row( $id ) {
        
        global $wpdb;

        $table_name = $this->get_table_name();
        $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id );
        $results = $wpdb->get_row( $query );

        // Bail if no row was found
        if ( ! $results ) {
            return false;
        }

        // JSON decode and validate row
        $row = RocketGalleries::get_instance()->validate( $this->decode_json( $results ) );

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

        // Do some decoding & validation
        foreach ( $rows->rows as $index => $row ) {
            $rows->rows[ $index ] = RocketGalleries::get_instance()->validate( $this->decode_json( $row ) );
        }

        // Return the queried rows
        return apply_filters( 'rocketgalleries_all_rows', $rows->rows );

    }

    /**
     * Returns all the rows (paginated) from the database table
     *
     * @param  int   $paginate_by The number of results to paginate by
     * @return array
     */
    public function paginate_rows( $paginate_by = 20 ) {

        // Execute the query
        $rows = $this->query_rows( true, $paginate_by );

        // Do some decoding & validation
        foreach ( $rows->rows as $index => $row ) {
            $rows->rows[ $index ] = RocketGalleries::get_instance()->validate( $this->decode_json( $row ) );
        }

        // Return the queried rows
        return apply_filters( 'rocketgalleries_paginate_rows', $rows, $paginate_by );

    }

    /**
     * Adds or updates a row, depending on whether it already exists
     *
     * @param  int       $id The row ID
     * @return int|false
     */
    public function add_or_update_row( $id ) {

        global $wpdb;

        $table_name = $this->get_table_name();
        $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id );
        $results = $wpdb->get_row( $query );

        // Save or update row
        if ( $results !== null ){
            return $this->update_row( $id );
        }
        else {
            return $this->add_row();
        }

    }

    /**
     * Updates a row
     *
     * @param  int       $id     The row ID
     * @param  array     $values The updated row values
     * @return int|false
     */
    public function update_row( $id, $values = false ) {
        
        global $wpdb;

        // Allow user to specify values, otherwise get them from $_POST
        $request = ( is_array( $values ) ) ? $values : $_POST;

        // Update the row
        $table_name = $this->get_table_name();
        return $wpdb->update(
            $table_name,
            array(
                'name' => ( isset( $request['name'] ) ) ? stripslashes_deep( $request['name'] ) : '',
                'author' => ( isset( $request['author'] ) ) ? stripslashes_deep( $request['author'] ) : '',
                /**
                 * The images are already JSON encoded by the Javascript, so we don't encode them unlike other object values
                 */
                'images' => ( isset( $request['images'] ) ) ? stripslashes_deep( $request['images'] ) : '',
                'general' => ( isset( $request['general'] ) ) ? json_encode( stripslashes_deep( $request['general'] ) ) : ''
            ),
            array( 'id' => $id ),
            array( '%s', '%s', '%s', '%s', ),
            array( '%d' )
        );

    }

    /**
     * Adds a new row
     *
     * @param  array     $values The row values to add
     * @return int|false
     */
    public function add_row( $values = false ) {
        
        global $wpdb;

        // Get defaults
        $defaults = $this->get_row_defaults();

        // Allow user to specify values, otherwise get them from $_POST
        $request = ( is_array( $values ) ) ? $values : $_POST;

        // Get the table name
        $table_name = $this->get_table_name();

        // Add the row
        return $wpdb->insert( $table_name,
            array(
                'name' => ( isset( $request['name'] ) ) ? stripslashes_deep( $request['name'] ) : '',
                'author' => ( isset( $request['author'] ) ) ? stripslashes_deep( $request['author'] ) : '',
                /**
                 * The images are already JSON encoded by the Javascript, so we don't encode them unlike other object values
                 */
                'images' => ( isset( $request['images'] ) ) ? stripslashes_deep( $request['images'] ) : '',
                'general' => ( isset( $request['general'] ) ) ? json_encode( stripslashes_deep( $request['general'] ) ) : ''
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

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

        // Escape the HTML content of each slide to prevent errors
        if ( ! empty( $row->images ) ) {
            foreach ( $row->images as $index => $image ) {
                if ( ! empty( $image->content ) ) {
                    $row->images[ $index ]->content = mysql_real_escape_string( $image->content );
                }
            }
        }

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

        // Get the table name
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
        
        // Get the table name
        $table_name = $this->get_table_name();

        // Query the table status, which will allow us to get the next table index
        $query = "SHOW TABLE STATUS LIKE '$table_name'";
        $mysql_query = mysql_query( $query );
        $mysql_fetch_assoc = mysql_fetch_assoc( $mysql_query );

        // Return the next table index
        return $mysql_fetch_assoc['Auto_increment'];
    
    }

}