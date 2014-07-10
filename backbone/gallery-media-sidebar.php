<?php
    /**
     * This is a Backbone.js template
     */
?>

<h3>
    <?php _e( 'Image Details', 'rocketgalleries' ); ?>
    <span class="settings-save-status">
        <span class="spinner"></span>
        <span class="saved"><?php esc_html_e( 'Saved.', 'rocketgalleries' ); ?></span>
    </span>
</h3>

<div class="attachment-info">
    <div class="thumbnail" style="max-width: none; max-height: 220px;">
        <# if ( data.uploading ) { #>
            <div class="media-progress-bar"><div></div></div>
        <# } else if ( 'image' === data.type ) { #>
            <img src="{{ data.size.url }}" draggable="false" style="max-width: none; max-height: 220px;" />
        <# } else { #>
            <img src="{{ data.icon }}" class="icon" draggable="false" />
        <# } #>
    </div>
    <div class="details">
        <div class="filename">{{ data.filename }}</div>
        <div class="uploaded">{{ data.dateFormatted }}</div>

        <# if ( 'image' === data.type && ! data.uploading ) { #>
            <# if ( data.width && data.height ) { #>
                <div class="dimensions">{{ data.width }} &times; {{ data.height }}</div>
            <# } #>

            <# if ( data.can.save ) { #>
                <a class="edit-attachment" href="{{ data.editLink }}&amp;image-editor" target="_blank"><?php _e( 'Edit Image', 'rocketgalleries' ); ?></a>
                <a class="refresh-attachment" href="#"><?php _e( 'Refresh', 'rocketgalleries' ); ?></a>
            <# } #>
        <# } #>

        <# if ( ! data.uploading && data.can.remove ) { #>
            <a class="delete-attachment" href="#"><?php _e( 'Delete Permanently', 'rocketgalleries' ); ?></a>
        <# } #>

        <div class="compat-meta">
            <# if ( data.compat && data.compat.meta ) { #>
                {{{ data.compat.meta }}}
            <# } #>
        </div>
    </div>
</div>