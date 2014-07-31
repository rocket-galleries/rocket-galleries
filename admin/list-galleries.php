<?php
    /**
     * Get the current page, as this page is paginated
     */
    $paged = ( isset( $_GET['paged'] ) ) ? $_GET['paged'] : 1;
?>

<div class="wrap">
    <h2>
        <?php _e( 'Galleries ', 'rocketgalleries' ); ?>
        <a href="admin.php?page=rocketgalleries_add_gallery" class="add-new-h2">
            <?php _e( 'Add New', 'rocketgalleries' ); ?>
        </a>
        <?php if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) : ?>
            <span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;', 'rocketgalleries' ), $_GET['s'] ); ?></span>
        <?php endif; ?>
    </h2>

    <form id="welcome-actions" action="" method="post">
        <?php
            /**
             * Displays welcome message
             */
            require 'welcome-panel.php';
        ?>
    </form>

    <?php
        /**
         * Before actions
         */
        do_action( 'rocketgalleries_list_galleries_before', $galleries, $page );
    ?>

    <form id="posts-filter" action="" method="get">
        <input type="hidden" name="page" id="page" value="<?php echo $page; ?>" />
        <p class="search-box">
            <label class="screen-reader-text" for="post-search-input"><?php _e( 'Search Galleries:', 'rocketgalleries' ); ?></label>
            <input type="search" id="post-search-input" name="s" value="<?php if ( isset( $_GET['s'] ) ) echo esc_attr( $_GET['s'] ); ?>">
            <input type="submit" name="" id="search-submit" class="button" value="<?php _e( 'Search Galleries', 'rocketgalleries' ); ?>">
        </p>
    </form>

    <form id="galleries-list" action="admin.php?page=<?php echo $page; ?>" method="get">
        <input type="hidden" name="page" id="page" value="<?php echo $page; ?>" />

        <?php
            /**
             * Security nonce field
             */
            wp_nonce_field( "rocketgalleries-bulk_{$page}", "rocketgalleries-bulk_{$page}", false );
        ?>

        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="action" id="action">
                    <option value="-1" selected="selected"><?php _e( 'Bulk Actions', 'rocketgalleries' ); ?></option>
                    <option value="duplicate"><?php _e( 'Duplicate', 'rocketgalleries' ); ?></option>
                    <option value="delete"><?php _e( 'Delete', 'rocketgalleries' ); ?></option>
                </select>
                <input type="submit" name="" id="doaction" class="button action" value="Apply">
            </div>

            <div class="tablenav-pages">
                <span class="displaying-num"><?php printf( _n( '1 gallery', '%d galleries', count( $galleries ), 'rocketgalleries' ), count( $galleries ) ); ?></span>
                <span class="pagination-links">
                    <a class="first-page <?php if ( $paged == 1 ) echo 'disabled'; ?>" title="<?php _e( 'Go to the first page', 'rocketgalleries' ); ?>" href="admin.php?page=<?php echo $page; ?>">«</a>
                    <a class="prev-page <?php if ( $paged == 1 ) echo 'disabled'; ?>" title="<?php _e( 'Go to the previous page', 'rocketgalleries' ); ?>" href="admin.php?page=<?php echo $page; ?>&amp;paged=<?php if ( $paged == 1 ) { echo 1; } else { echo ( $paged - 1 ); } ?>">‹</a>
                    <span class="paging-input">
                        <input class="current-page" title="<?php _e( 'Current page', 'rocketgalleries' ); ?>" type="text" name="paged" value="<?php echo $paged; ?>" size="1"> of <span class="total-pages"><?php echo $max_pages; ?></span>
                    </span>
                    <a class="next-page <?php if ( $paged == $max_pages ) echo 'disabled'; ?>" title="<?php _e( 'Go to the next page', 'rocketgalleries' ); ?>" href="admin.php?page=<?php echo $page; ?>&amp;paged=<?php if ( $paged == $max_pages ) { echo $max_pages; } else { echo ( $paged + 1 ); } ?>">›</a>
                    <a class="last-page <?php if ( $paged == $max_pages ) echo 'disabled'; ?>" title="<?php _e( 'Go to the last page', 'rocketgalleries' ); ?>" href="admin.php?page=<?php echo $page; ?>&amp;paged=<?php echo ( $max_pages ); ?>">»</a>
                </span>
            </div>

            <br class="clear">
        </div>

        <table class="wp-list-table widefat fixed posts" cellspacing="0">
            <?php foreach ( array( 'thead', 'tfoot' ) as $element ) : ?>

                <?php echo "<{$element}>"; ?>
                    <tr>
                        <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                            <label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All', 'rocketgalleries' ); ?></label><input id="cb-select-all-1" type="checkbox">
                        </th>
                        <th scope="col" id="id" class="manage-column column-id <?php echo ( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'id' ) ? 'sorted ' : 'sortable '; echo ( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) ? 'desc' : 'asc'; ?>" style="">
                            <a href="admin.php?page=<?php echo $page; ?>&amp;s=<?php if ( isset( $_GET['s'] ) ) echo $_GET['s']; ?>&amp;orderby=id&amp;order=<?php echo ( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) ? 'desc' : 'asc'; ?>">
                                <span><?php _e( 'ID', 'rocketgalleries' ); ?></span><span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" id="name" class="manage-column column-name <?php echo ( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'name' ) ? 'sorted ' : 'sortable '; echo ( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) ? 'desc' : 'asc'; ?>" style="">
                            <a href="admin.php?page=<?php echo $page; ?>&amp;s=<?php if ( isset( $_GET['s'] ) ) echo $_GET['s']; ?>&amp;orderby=name&amp;order=<?php echo ( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) ? 'desc' : 'asc'; ?>">
                                <span><?php _e( 'Title', 'rocketgalleries' ); ?></span><span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" id="author" class="manage-column column-author <?php echo ( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'author' ) ? 'sorted ' : 'sortable '; echo ( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) ? 'desc' : 'asc'; ?>" style="">
                            <a href="admin.php?page=<?php echo $page; ?>&amp;s=<?php if ( isset( $_GET['s'] ) ) echo $_GET['s']; ?>&amp;orderby=author&amp;order=<?php echo ( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) ? 'desc' : 'asc'; ?>">
                                <span><?php _e( 'Author', 'rocketgalleries' ); ?></span><span class="sorting-indicator"></span>
                            </a>
                        </th>
                    </tr>
                <?php echo "</{$element}>"; ?>

            <?php endforeach; ?>

            <tbody>
                <?php if ( empty( $galleries ) ) : ?>

                    <tr class="no-items">
                        <td class="colspanchange" colspan="4"><?php _e( 'No galleries found.', 'rocketgalleries' ); ?></td>
                    </tr>

                <?php else : ?>
                    <?php foreach ( $galleries as $index => $gallery ) : ?>

                        <tr id="gallery-<?php echo esc_attr( $gallery->id ); ?>" class="gallery-<?php echo esc_attr( $gallery->id ); ?> <?php if ( $index+1 & 1 ) echo 'alternate'; ?>" valign="top">
                            <th scope="row" class="check-column">
                                <label class="screen-reader-text" for="cb-select-<?php echo esc_attr( $gallery->id ); ?>">
                                    <?php _e( 'Select ', 'rocketgalleries' ); ?><?php echo esc_html( $gallery->name ); ?>
                                </label>

                                <input id="cb-select-<?php echo esc_attr( $gallery->id ); ?>" type="checkbox" name="id[]" value="<?php echo esc_attr( $gallery->id ); ?>">
                            </th>

                            <td class="gallery-id column-id">
                                <?php echo esc_html( $gallery->id ); ?>
                            </td>

                            <td class="gallery-name column-name">
                                <strong>
                                    <a class="row-name" href="admin.php?page=<?php echo $page; ?>&amp;edit=<?php echo esc_attr( $gallery->id ); ?>" title="<?php printf( __( 'Edit &#8220;%s&#8221;', 'rocketgalleries' ), $gallery->name ); ?>">
                                        <?php echo esc_html( $gallery->name ); ?>
                                    </a>
                                </strong>

                                <div class="row-actions">
                                    <?php
                                        /**
                                         * Generate the URLs, including security nonces, for our actions
                                         */
                                        $duplicate = wp_nonce_url( "admin.php?page={$page}&id={$gallery->id}&action=duplicate", "rocketgalleries-duplicate_{$page}", "rocketgalleries-duplicate_{$page}" );
                                        $delete = wp_nonce_url( "admin.php?page={$page}&id={$gallery->id}&action=delete", "rocketgalleries-delete_{$page}", "rocketgalleries-delete_{$page}" );
                                    ?>
                                    <span class="edit"><a href="admin.php?page=<?php echo $page; ?>&amp;edit=<?php echo esc_attr( $gallery->id ); ?>" title="<?php _e( 'Edit this gallery', 'rocketgalleries' ); ?>"><?php _e( 'Edit', 'rocketgalleries' ); ?></a> | </span>
                                    <span class="duplicate"><a href="<?php echo esc_url( $duplicate ); ?>" title="<?php _e( 'Duplicate this gallery', 'rocketgalleries' ); ?>"><?php _e( 'Duplicate', 'rocketgalleries' ); ?></a> | </span>
                                    <span class="trash"><a href="<?php echo esc_url( $delete ); ?>" class="submitdelete" title="<?php _e( 'Delete this gallery', 'rocketgalleries' ); ?>"><?php _e( 'Delete', 'rocketgalleries' ); ?></a></span>
                                </div>
                            </td>

                            <td class="author column-author">
                                <a href="admin.php?page=<?php echo $page; ?>&amp;filterby=author&amp;filter=<?php echo esc_attr( $gallery->author ); ?>">
                                    <?php echo esc_html( $gallery->author ); ?>
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="alignleft actions">
                <select name="action2" id="action2">
                    <option value="-1" selected="selected"><?php _e( 'Bulk Actions', 'rocketgalleries' ); ?></option>
                    <option value="duplicate"><?php _e( 'Duplicate', 'rocketgalleries' ); ?></option>
                    <option value="delete"><?php _e( 'Delete', 'rocketgalleries' ); ?></option>
                </select>
                <input type="submit" name="" id="doaction2" class="button action" value="<?php _e( 'Apply', 'rocketgalleries' ); ?>">
            </div>

            <div class="tablenav-pages">
                <span class="displaying-num"><?php printf( _n( '1 gallery', '%d galleries', count( $galleries ), 'rocketgalleries' ), count( $galleries ) ); ?></span>
                <span class="pagination-links">
                    <a class="first-page <?php if ( $paged == 1 ) echo 'disabled'; ?>" title="<?php _e( 'Go to the first page', 'rocketgalleries' ); ?>" href="admin.php?page=<?php echo $page; ?>">«</a>
                    <a class="prev-page <?php if ( $paged == 1 ) echo 'disabled'; ?>" title="<?php _e( 'Go to the previous page', 'rocketgalleries' ); ?>" href="admin.php?page=<?php echo $page; ?>&amp;paged=<?php if ( $paged == 1 ) { echo 1; } else { echo ( $paged - 1 ); } ?>">‹</a>
                    <span class="paging-input">
                        <input class="current-page" title="<?php _e( 'Current page', 'rocketgalleries' ); ?>" type="text" name="paged" value="<?php echo $paged; ?>" size="1"> of <span class="total-pages"><?php echo $max_pages; ?></span>
                    </span>
                    <a class="next-page <?php if ( $paged == $max_pages ) echo 'disabled'; ?>" title="<?php _e( 'Go to the next page', 'rocketgalleries' ); ?>" href="admin.php?page=<?php echo $page; ?>&amp;paged=<?php if ( $paged == $max_pages ) { echo $max_pages; } else { echo ( $paged + 1 ); } ?>">›</a>
                    <a class="last-page <?php if ( $paged == $max_pages ) echo 'disabled'; ?>" title="<?php _e( 'Go to the last page', 'rocketgalleries' ); ?>" href="admin.php?page=<?php echo $page; ?>&amp;paged=<?php echo ( $max_pages ); ?>">»</a>
                </span>
            </div>
            
            <br class="clear">
        </div>
    </form>

    <?php
        /**
         * After actions
         */
        do_action( 'rocketgalleries_list_galleries_after', $galleries, $page );
    ?>
</div>