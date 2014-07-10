;(function($) {

    /**
     * Out image model
     */
    window.ImageModel = Backbone.Model.extend({

        defaults: {
            attachment_id: null,
            url:           null,
            alt:           null,
            title:         null,
            sizes:         null
        },

    });

    /**
     * Our image collection
     */
    window.ImageCollection = Backbone.Collection.extend({

        /**
         * The model used by this collection
         */
        model: ImageModel,

        /**
         * Primary key increment
         */
        _primary: 0,

        /**
         * Constructor
         */
        initialize: function() {

            var self = this;

            // Bind events
            this.on('add', this.addModel, this);
            this.on('remove', this.removeModel, this);
            this.on('reset', this.resetData, this);
            this.on('change', this.resetData, this);

            // Increment primary key for each model (timeout ensures models are added before doing so)
            setTimeout(function() {
                _.each(self.models, function(model) {
                    self._primary++;
                });
            });

        },

        /**
         * Functionality called when a model is added to the collection
         */
        addModel: function(model) {

            // Increment primary key
            this._primary++;

            // Add ID to model & store it
            model.set({ 'id': this._primary }, { silent: true });

            // Silently reset the collection (forces refresh of _byId and _byCid properties)
            this.reset(this.models, { silent: true });

            // Set stored data
            this.resetData();

        },

        /**
         * Functionality called when a model is removed from the collection
         */
        removeModel: function() {

            var self = this;

            // Reset model IDs
            this.resetIDs();

            // Silently reset the collection (forces refresh of _byId and _byCid properties)
            this.reset(this.models, { silent: true });

            // Set stored data
            this.resetData();

        },

        /**
         * Resets the collection data
         */
        resetData: function() {

            // Reset stored (hidden input) data
            $('#gallery-images').val(JSON.stringify(this));

        },

        /**
         * Resets the model ID's from within the collection
         */
        resetIDs: function() {

            // Reset primary key
            this._primary = 0;

            // Reset ID's
            _.each(this.models, function(model) {
                this._primary++;
                model.set({ 'id': this._primary }, { silent: true });
            }, this);

            return this;

        },

    });

    /**
     * Our gallery view
     */
    window.GalleryView = wp.media.View.extend({

        /**
         * Our container element for this view
         */
        $container: $('.thumbnails-container .inner'),

        /**
         * The template for thumbnails in this view
         */
        template: wp.media.template('gallery-thumbnail'),

        /**
         * Constructor
         */
        initialize: function() {

            var self = this;

            // Bind events
            this.collection.on('add', this.addThumb, this);
            this.collection.on('remove', this.render, this);
            this.collection.on('reset', this.render, this);

            // Delete all images functionality
            $(document).delegate('.delete-images', 'click', function(event) {

                event.preventDefault();

                // Remove all of the thumbnails
                if ( confirm( rocketgalleries.delete_images ) )
                    self.removeThumbs.call(self, event);

            });

            // Add image(s) functionality
            $(document).delegate('.add-image', 'click', function(event) {

                event.preventDefault();

                // Initiate & display the add image view
                self.addImageView = new AddImageView({
                    collection: self.collection
                }).render();

            });

            // Delete image functionality
            $(document).delegate('.delete-button', 'click', function(event) {

                event.preventDefault();

                // Confirm before deleting the image
                if ( confirm( rocketgalleries.delete_image ) )
                    self.collection.remove(self.collection.get($(this).parent().attr('data-id')));

            });

        },

        /**
         * Adds a thumbnail to the view
         */
        addThumb: function(model) {

            // Add the new thumbnail for this model
            this.$container.append(this.template(model.toJSON()))

        },

        /**
         * Removes a thumbnail from the view
         */
        removeThumbs: function(event) {

            event.preventDefault();

            // Remove thumbnails
            this.$container.empty();

            // Clear the collection
            this.collection.reset();

            // Reset primary index
            this.collection._primary = 0;

        },

        /**
         * Renders the view
         */
        render: function() {

            // Remove the previous thumbnails
            this.$container.empty();

            // Add the current thumbnails
            _.each(this.collection.models, function(model) {
                this.$container.append(this.template(model.toJSON()));
            }, this);

            return this;

        }

    });

    /**
     * Add image view
     */
    window.AddImageView = Backbone.View.extend({

        /**
         * The media upload file frame.
         */
        fileFrame: null,

        /**
         * The file frame properties
         */
        frameProperties: {
            title:    rocketgalleries.media_upload.title,
            button:   rocketgalleries.media_upload.button,
            multiple: true
        },

        /**
         * The attributes to propogate for each image.
         * The keys are the attachment attributes, and the values are our model attributes to be set.
         */
        modelAttributes: {
            'attachment_id': 'id',
            'url':           'url',
            'alt':           'alt',
            'title':         'title',
            'sizes':         'sizes'
        },

        /**
         * Constructor
         */
        initialize: function() {

            // Bail if file frame already exists
            if ( this.fileFrame )
                return;

            // Create the media frame
            this.fileFrame = wp.media.frames.fileFrame = wp.media({
                title: this.frameProperties.title,
                button: {
                    text: this.frameProperties.button
                },
                multiple: this.frameProperties.multiple
            });

            // Run callback when an image(s) is selected
            this.fileFrame.on('select', this.onSelect, this);

        },

        /**
         * Handles image selection
         */
        onSelect: function() {

            // Get the selected images
            var selection = this.fileFrame.state().get('selection');

            // Add them to the slides collection
            _.each(selection.models, function(image) {

                // The object of new attributes
                var newAttributes = {};

                // Add the attributes
                _.each(this.modelAttributes, function(value, key) {
                    newAttributes[key] = image.get(value);
                }, this);

                /*
                for ( var i in this.modelAttributes )
                    newAttributes[this.modelAttributes[i]] = image.get(this.modelAttributes[i]);
                */

                // Push the new slide
                this.collection.add([newAttributes]);

            }, this);

        },

        /**
         * Displays the file frame
         */
        render: function() {

            this.fileFrame.open();

            return this;

        }

    });

    // Override Media Library sidebar template with our own
    wp.media.view.Attachment.Details.prototype.template = wp.media.template('gallery-media-sidebar');

    // Show/hide settings functionality
    $('.sidebar-name').bind('click', function() {

        var $thisParent = $(this).parent(),
            $thisContent = $thisParent.find('.sidebar-content');

        // Close the other widgets before opening selected widget
        if ( !$thisParent.hasClass('exclude' ) ) {
            $('.sidebar-name').each(function() {

                // Get parent
                var $parent = $(this).parent();

                // Close the widget
                if ( !$parent.hasClass('exclude') && !$parent.hasClass('closed') ) {
                    $parent.find('.sidebar-content').slideUp(200, function() {
                        $parent.addClass('closed');
                    });
                }

            });
        }

        // Open/close the widget
        if ( $thisParent.hasClass('closed') )
            $thisContent.slideDown(200, function() {
                $thisParent.removeClass('closed');
            });
        else
            $thisContent.slideUp(200, function() {
                $thisParent.addClass('closed');
            });

    });

    // Fade out messages after 5 seconds
    setTimeout(function() {
        $('.message').not('.permanent').each(function() {
            $(this).fadeOut(400, function() {
                $(this).remove();
            });
        });
    }, 5000);

    // Show warning prompts
    $('.warn').bind('click', function() {
        if ( !confirm( rocketgalleries.warn ) )
            return false;
    });

    // Get slides and bail if they can't be found
    if ( $('#gallery-images').length == 0 )
        return;
   
    // Get the current image collection
    window.imageCollection = new ImageCollection(JSON.parse($('#gallery-images').val()));

    // Initiate & render the "Gallery" view
    window.galleryView = new GalleryView({
        collection: imageCollection
    }).render();

    // Sortables functionality
    $('.thumbnails-container').sortable({
        items: '.thumbnail',
        containment: 'parent',
        tolerance: 'pointer',
        stop: function(event, ui) {

            var order = [],
                sortedModels = [];

            /** Get the new sort order */
            $(this).find('.thumbnail').each(function() {
                order.push($(this).attr('data-id'));
            });

            /** Get array of models in sorted order */
            for ( var i = 0; i < order.length; i++ )
                sortedModels.push(imageCollection.get(order[i]));

            /** Reset the collection & its IDs */
            imageCollection.reset(sortedModels).resetIDs().resetData();

        }
    });

})(jQuery);