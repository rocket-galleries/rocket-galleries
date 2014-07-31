<div class="wrap">
    <h2><?php _e( 'Extensions', 'rocketgalleries' ); ?></h2>

    <?php
        /**
         * Before actions
         */
        do_action( 'rocketgalleries_list_extensions_before', $page );
    ?>

    <div id="extensions" class="extensions">
        <div class="extension" style="width: 100%">
            <h4>Help us build the extensions you want!</h4>
            <p>At the moment, we're searching for ideas and use cases to create extensions for. If you have any suggestions, please post them on our <a href="https://github.com/rocket-galleries/rocket-galleries/issues">Github issues pages</a>. It's likely that any extensions that receive notable interest will get developed! Otherwise, if you'd prefer to email us, feel free to do so <a href="mailto:support@rocketgalleries.com?subject=Extension Suggestion">here</a>.</p>
            
            <h4>Want to develop your own extension?</h4>
            <p>Are you a developer? We would love to work with any developers interested in creating their own extensions for Rocket Galleries. If so, <a href="mailto:support@rocketgalleries.com?subject=Develop an Extension">contact us</a>.</p>
            
            <p>
                <a href="https://github.com/rocket-galleries/rocket-galleries/issues" target="_blank" class="button-primary">Suggest an extension on Github</a>
                <a href="mailto:support@rocketgalleries.com?subject=Develop an Extension" target="_blank" class="button-secondary">Get involved in developing an extension</a>
            </p>
        </div>
    </div>

    <?php
        /**
         * After actions
         */
        do_action( 'rocketgalleries_list_extensions_after', $page );
    ?>
</div>