<div class="wrap">
    <h2 class="nav-tab-wrapper">
        <?php foreach ( $tabs as $key => $value ) : ?>
            <?php
                // Set the "nav-tab" class
                $nav_class = 'nav-tab';

                // Set active tab class if appropriate
                if ( $tab == $key ) {
                    $nav_class .= ' nav-tab-active';
                }
            ?>
            <a href="admin.php?page=<?php echo $page; ?>&amp;tab=<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $nav_class ); ?>"><?php echo esc_html( $value ); ?></a>
        <?php endforeach; ?>
    </h2>

    <form name="post" action="admin.php?page=<?php echo $page; ?>&amp;tab=<?php echo $tab; ?>" method="post">
        <?php
            /**
             * Security nonce field
             */
            wp_nonce_field( "rocketgalleries-save_{$page}", "rocketgalleries-save_{$page}", false );
            wp_nonce_field( "rocketgalleries-reset_{$page}", "rocketgalleries-reset_{$page}", false );
        
            /**
             * Before actions
             */
            do_action( 'rocketgalleries_settings_before', $settings, $page, $tab );

            /**
             * Load the tab
             */
            ?>
                <div class="main-panel">
                    <?php
                        if ( has_action( "rocketgalleries_display_{$tab}_tab" ) ) {
                            do_action( "rocketgalleries_display_{$tab}_tab", $page );
                        }
                        else {
                            if ( file_exists( plugin_dir_path( __FILE__ ) . "edit-settings_{$tab}.php" ) ) {
                                require plugin_dir_path( __FILE__ ) . "edit-settings_{$tab}.php";
                            }
                        }
                    ?>
                </div>
            <?php

            /**
             * After actions
             */
            do_action( 'rocketgalleries_settings_after', $settings, $page, $tab );
        ?>

        <p class="submit">
            <input type="submit" name="save" class="button button-primary button-large" id="save" accesskey="p" value="<?php _e( 'Save Settings', 'rocketgalleries' ); ?>">
        </p>
        </div>
    </form>
</div>