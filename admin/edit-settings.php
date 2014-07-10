<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
    <h2><?php _e( 'Edit Settings', 'rocketgalleries' ); ?></h2>

    <form name="post" action="admin.php?page=<?php echo $page; ?>" method="post">
        <?php

            /**
             * Security nonce field
             */
            wp_nonce_field( "rocketgalleries-save_{$page}", "rocketgalleries-save_{$page}", false );
            wp_nonce_field( "rocketgalleries-reset_{$page}", "rocketgalleries-reset_{$page}", false );
        
            /**
             * Before actions
             */
            do_action( 'rocketgalleries_settings_before', $settings, $page );

        ?>
        <div class="main-panel">
            <h3 class="hide-if-has-template"><?php _e( 'General Settings', 'rocketgalleries' ); ?></h3>
            <table class="hide-if-has-template form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row">
                            <label for="assets"><?php _e( 'Scripts & Styling', 'rocketgalleries' ); ?></label>
                        </th>
                        <td class="radio-buttons">
                            <label for="assets_compatibility">
                                <input type="radio" name="settings[assets]" id="assets_compatibility" value="compatibility" <?php checked( $settings['assets'], 'compatibility' ); ?>>
                                <span><?php _e( 'Compatibility', 'rocketgalleries' ); ?></span>
                            </label>
                            <label for="assets_optimized">
                                <input type="radio" name="settings[assets]" id="assets_optimized" value="optimized" <?php checked( $settings['assets'], 'optimized' ); ?>>
                                <span><?php _e( 'Optimized', 'rocketgalleries' ); ?></span>
                            </label>
                            <p class="description"><?php _e( 'This option controls where the plugin\'s scripts and styling are loaded. "Compatibility" will load them in the page header, which is less performant but also less likely to suffer conflicts with other plugins. "Optimized" conditionally loads CSS and JS in the page footer, which is better for performance, but more likely to encounter errors. We recommend trying "Optimized" and reverting back if you encounter any issues.', 'rocketgalleries' ); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="hide-if-has-template divider"></div>
            
            <h3><?php _e( 'Reset Plugin', 'rocketgalleries' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row">
                            <label for="reset"><?php _e( 'Plugin Settings', 'rocketgalleries' ); ?></label>
                        </th>
                        <td>
                            <input type="submit" name="reset" class="button button-secondary warn" value="<?php _e( 'Reset Plugin', 'rocketgalleries' ); ?>">
                            <p class="description"><?php _e( 'Click this button to reset the plugin to its default settings. This cannot be reversed, so be sure before you do this!', 'rocketgalleries' ); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="divider"></div>

            <h3><?php _e( 'Installation Settings', 'rocketgalleries' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><?php _e( 'PHP Version', 'rocketgalleries' ); ?></th>
                        <td><?php echo phpversion(); ?></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'MySQL Version', 'rocketgalleries' ); ?></th>
                        <td><?php echo mysql_get_server_info(); ?></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'WordPress Version', 'rocketgalleries' ); ?></th>
                        <td><?php global $wp_version; echo $wp_version; ?></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Plugin Version', 'rocketgalleries' ); ?></th>
                        <td><?php echo RocketGalleries::$version; ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="divider"></div>

            <?php
                /**
                 * After actions
                 */
                do_action( 'rocketgalleries_settings_after', $settings, $page );
            ?>

            <p class="submit">
                <input type="submit" name="save" class="button button-primary button-large" id="save" accesskey="p" value="<?php _e( 'Save Settings', 'rocketgalleries' ); ?>">
            </p>
        </div>
    </form>
</div>