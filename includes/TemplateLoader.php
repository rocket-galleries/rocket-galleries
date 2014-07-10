<?php

/**
 * Template loader.
 *
 * @author Matthew Ruddy
 */
class RG_TemplateLoader {

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
	 * Retrieve a template part.
	 *
	 * @param  string  $slug
	 * @param  string  $name Optional. Default null.
	 * @param  bool    $load Optional. Default true.
	 * @return string
	 */
	public function get_template_part( $slug, $name = null, $load = true ) {

		// Execute code for this part
		do_action( 'get_template_part_' . $slug, $slug, $name );

		// Get files names of templates, for given slug and name.
		$templates = $this->get_template_file_names( $slug, $name );

		// Return the part that is found
		return $this->locate_template( $templates, $load, false );

	}

	/**
	 * Given a slug and optional name, create the file names of templates.
	 *
	 * @param string  $slug
	 * @param string  $name
	 * @return array
	 */
	protected function get_template_file_names( $slug, $name ) {

		$templates = array();

		if ( isset( $name ) ) {
			$templates[] = $slug . '-' . $name . '.php';
		}

		$templates[] = $slug . '.php';

		/**
		 * Allow template choices to be filtered.
		 *
		 * The resulting array should be in the order of most specific first, to least specific last.
		 * e.g. 0 => recipe-instructions.php, 1 => recipe.php
		 */
		return apply_filters( 'rocketgalleries_get_template_part', $templates, $slug, $name );

	}

	/**
	 * Retrieve the name of the highest priority template file that exists.
	 *
	 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
	 * inherit from a parent theme can just overload one file. If the template is
	 * not found in either of those, it looks in the theme-compat folder last.
	 *
	 * @param string|array $template_names Template file(s) to search for, in order.
	 * @param bool         $load           If true the template file will be loaded if it is found.
	 * @param bool         $require_once   Whether to require_once or require. Default true. Has no effect if $load is false.
	 * @return string The template filename if one is located.
	 */
	public function locate_template( $template_names, $load = false, $require_once = true ) {

		// No file found yet
		$located = false;

		// Remove empty entries
		$template_names = array_filter( (array) $template_names );
		$template_paths = $this->get_template_paths();

		// Try to find a template file
		foreach ( $template_names as $template_name ) {

			// Trim off any slashes from the template name
			$template_name = ltrim( $template_name, '/' );

			// Try locating this template file by looping through the template paths
			foreach ( $template_paths as $template_path ) {
				if ( file_exists( $template_path . $template_name ) ) {
					$located = $template_path . $template_name;
					break 2;
				}
			}

		}

		if ( $load && $located ) {
			load_template( $located, $require_once );
		}

		return $located;

	}

	/**
	 * Return a list of paths to check for template locations.
	 *
	 * Default is to check in a child theme (if relevant) before a parent theme, so that themes which inherit from a
	 * parent theme can just overload one file. If the template is not found in either of those, it looks in the
	 * theme-compat folder last.
	 *
	 * @return mixed|void
	 */
	protected function get_template_paths() {

		$theme_directory = trailingslashit( apply_filters( 'rocketgalleries_theme_template_directory', '' ) );

		$file_paths = array(
			10  => trailingslashit( get_template_directory() ) . $theme_directory,
			100 => $this->get_templates_dir()
		);

		// Only add this conditionally, so non-child themes don't redundantly check active theme twice.
		if ( is_child_theme() ) {
			$file_paths[1] = trailingslashit( get_stylesheet_directory() ) . $theme_directory;
		}

		// Allow ordered list of template paths to be amended
		$file_paths = apply_filters( 'rocketgalleries_template_paths', $file_paths );

		// Sort the file paths based on priority
		ksort( $file_paths, SORT_NUMERIC );

		return array_map( 'trailingslashit', $file_paths );

	}

	/**
	 * Return the path to the templates directory in this plugin.
	 *
	 * @return string
	 */
	protected function get_templates_dir() {

		return trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) . 'templates';
		
	}
}