<?php

// Display the panel if it hasn't been already dismissed.
if ( get_option( 'rocketgalleries_disable_welcome_panel' ) == false ) :

    // URL references
    $links = array(
        'get-started'       => 'http://wordpress.org/plugins/rocket-galleries/installation',
        'display-galleries' => 'http://wordpress.org/plugins/rocket-galleries/installation#display',
        'faqs'              => 'http://wordpress.org/plugins/rocket-galleries/faq',
        'support'           => 'http://wordpress.org/support/plugin/rocket-galleries',
    );

?>
<div id="welcome-panel" class="welcome-panel">
    <?php
        /**
         * Allow developers to add content before the panel
         */
        do_action( 'rocketgalleries_welcome_panel_before' );
    ?>

    <a href="admin.php?page=<?php echo $page; ?>&amp;disable_welcome_panel=true" class="welcome-panel-close"><?php _e( 'Dismiss', 'rocketgalleries' ); ?></a>
    <div class="welcome-panel-content">
        <h3><?php _e( 'Welcome to Rocket Galleries', 'rocketgalleries' ); ?></h3>
        
        <p class="about-description">
            <?php _e( 'Thanks for installing Rocket Galleries. Here are some links to help get you started.', 'rocketgalleries' ); ?>
        </p>

        <div class="welcome-panel-column-container">
            <div class="welcome-panel-column">
                <h4><?php _e( 'Get Started', 'rocketgalleries' ); ?></h4>
                <a class="button button-primary button-hero" href="<?php echo $links['get-started']; ?>"><?php _e( 'View the Documentation', 'rocketgalleries' ); ?></a>
            </div>

            <div class="welcome-panel-column">
                <h4><?php _e( 'Need some help?', 'rocketgalleries' ); ?></h4>
                <ul>
                    <li>
                        <a href='<?php echo $links['display-galleries']; ?>'>
                            <span class="dashicons dashicons-format-gallery"></span>
                            <?php _e( 'Displaying a Gallery', 'rocketgalleries' ); ?>
                        </a>
                    </li>
                    <li>
                        <a href='<?php echo $links['faqs']; ?>'>
                            <span class="dashicons dashicons-format-status"></span>
                            <?php _e( 'Frequently Asked Questions', 'rocketgalleries' ); ?>
                        </a>
                    </li>
                    <li>
                        <a href='<?php echo $links['support']; ?>'>
                            <span class="dashicons dashicons-groups"></span>
                            <?php _e( 'Help & Support', 'rocketgalleries' ); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <?php
        /**
         * Allow developers to add content after the panel
         */
        do_action( 'rocketgalleries_welcome_panel_after' );
    ?>
</div>
<?php endif; ?>