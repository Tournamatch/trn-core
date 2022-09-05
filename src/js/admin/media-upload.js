/**
 * Admin media upload input handler.
 *
 * @link       https://www.tournamatch.com
 * @since      4.3.0
 *
 * @package    Tournamatch
 *
 */
(function ($) {
    'use strict';

    window.addEventListener('load', function () {

        // Uploading files
        let file_frame;
        let wp_media_post_id = wp.media.model.settings.post.id; // Store the old id

        jQuery('.trn-media-upload-button').each(function() {
            jQuery(this).on('click', function (event) {
                event.preventDefault();

                let post_id = jQuery(this).attr('data-post-id');
                let preview_id = jQuery(this).attr('data-preview-id');
                let input_id = jQuery(this).attr('data-input-id');
                let title = jQuery(this).attr('data-title');
                let button_text = jQuery(this).attr('data-button-text');

                if (!file_frame) {
                    // Set the wp.media post id so the uploader grabs the ID we want when initialised
                    if (post_id) {
                        wp.media.model.settings.post.id = post_id;
                    }

                    // Create the media frame.
                    file_frame = wp.media.frames.file_frame = wp.media({
                        title: title,
                        button: {
                            text: button_text,
                        },
                        multiple: false	// Set to true to allow multiple files to be selected
                    });
                } else {
                    // Set the post ID to what we want
                    if (post_id) {
                        file_frame.uploader.uploader.param('post_id', post_id);
                    }
                }

                file_frame.off('select');

                // When an image is selected, run a callback.
                file_frame.on('select', function () {
                    // We set multiple to false so only get one image from the uploader
                    let attachment = file_frame.state().get('selection').first().toJSON();

                    // Do something with attachment.id and/or attachment.url here
                    jQuery(`#${preview_id}`).attr('src', attachment.url).removeClass('hidden');
                    jQuery(`#${input_id}`).val(attachment.id);

                    // Restore the main post ID
                    wp.media.model.settings.post.id = wp_media_post_id;
                });

                // Finally, open the modal
                file_frame.open();
            });

            // Restore the main ID when the add media button is pressed
            jQuery('a.add_media').on('click', function () {
                wp.media.model.settings.post.id = wp_media_post_id;
            });
        });
    }, false);
})(jQuery);