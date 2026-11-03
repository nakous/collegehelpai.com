jQuery(document).ready(function ($) {
    // Media Uploader for Logo
    var mediaUploader;

    $('.upload-logo-button').on('click', function (e) {
        e.preventDefault();

        // If the media uploader already exists, open it
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Create the media uploader
        mediaUploader = wp.media({
            title: 'Choose Logo',
            button: {
                text: 'Choose Logo'
            },
            multiple: false
        });

        // When a logo is selected, run a callback
        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            if ($('#logo-preview').length === 0) {
                $('.logo-preview').append('<img id="logo-preview" src="' + attachment.url + '" style="max-width: 100px;"/>');
            } else {
                $('#logo-preview').attr('src', attachment.url);
            }
            $('#cobra_logo').val(attachment.url);
            $('.remove-logo').show();
        });

        // Open the uploader dialog
        mediaUploader.open();
    });

    // Remove Logo
    $('.remove-logo').on('click', function (e) {
        e.preventDefault();
        $('#logo-preview').attr('src', '');
        $('#cobra_logo').val('');
        $(".logo-preview").hide();
    });

    // Social Media Links Dynamic Fields
    var socialLinksContainer = $('#social-links-container');
    var socialLinkCount = socialLinksContainer.children().length;

    // Add new social link field
    $('#add-social-link').on('click', function () {
        const icons = {
            facebook: 'fa-facebook',
            twitter: 'fa-twitter',
            instagram: 'fa-instagram',
            linkedin: 'fa-linkedin',
            youtube: 'fa-youtube',
            tiktok: 'fa-tiktok',
            pinterest: 'fa-pinterest',
            custom: 'fa-link'
        };
        var newField = `
            <div class="social-link-field">
                <select name="cobra_social_links[${socialLinkCount}][platform]" class="social-platform">
                    <option value="">Select Platform</option>
                    <option value="facebook">Facebook</option>
                    <option value="twitter">Twitter</option>
                    <option value="instagram">Instagram</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="youtube">YouTube</option>
                    <option value="tiktok">TikTok</option>
                    <option value="pinterest">Pinterest</option>
                    <option value="custom">Custom</option>
                </select>
                <input type="text" name="cobra_social_links[${socialLinkCount}][url]" 
                       placeholder="Enter URL" class="social-url">
                <input type="text" name="cobra_social_links[${socialLinkCount}][icon]" 
                       placeholder="Icon Class (e.g., fa-facebook)" class="social-icon" value="fa-link">
                <button type="button" class="button remove-social-link">Remove</button>
            </div>
        `;
        socialLinksContainer.append(newField);
        socialLinkCount++;
    });

    // Remove social link field
    $(document).on('click', '.remove-social-link', function () {
        $(this).parent('.social-link-field').remove();
        reindexSocialFields();
    });

    // Reindex social fields after removal
    function reindexSocialFields() {
        socialLinkCount = 0;
        $('.social-link-field').each(function () {
            $(this).find('select, input').each(function () {
                var name = $(this).attr('name');
                if (name) {
                    var newName = name.replace(/\[\d+\]/, '[' + socialLinkCount + ']');
                    $(this).attr('name', newName);
                }
            });
            socialLinkCount++;
        });
    }

    // Style Switcher
    $('#cobra_theme_style').on('change', function () {
        var selectedStyle = $(this).val();
        // You can add preview functionality here if needed
    });

    // Tab Navigation
    // $('.nav-tab').on('click', function(e) {
    //     e.preventDefault();
    //     var targetTab = $(this).data('tab');

    //     // Update active tab
    //     $('.nav-tab').removeClass('nav-tab-active');
    //     $(this).addClass('nav-tab-active');

    //     // Show target content
    //     $('.tab-content').hide();
    //     $('#' + targetTab).show();
    // });

    // Menu Location Settings
    $('.menu-location-select').on('change', function () {
        var location = $(this).val();
        var menuId = $(this).data('menu-id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_menu_location',
                location: location,
                menu_id: menuId,
                nonce: cobra_settings.nonce
            },
            success: function (response) {
                if (response.success) {
                    alert('Menu location updated successfully!');
                }
            }
        });
    });

    // Color Picker Initialization
    if ($.fn.wpColorPicker) {
        $('.color-picker').wpColorPicker();
    }

    // Save Settings with AJAX
    $('#cobra-settings-form').on('submit', function (e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('input[type="submit"]');

        submitButton.prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: form.serialize() + '&action=save_cobra_settings',
            success: function (response) {
                if (response.success) {
                    $('.settings-saved-notice').fadeIn().delay(2000).fadeOut();
                } else {
                    alert('Error saving settings. Please try again.');
                }
                submitButton.prop('disabled', false);
            },
            error: function () {
                alert('Error saving settings. Please try again.');
                submitButton.prop('disabled', false);
            }
        });
    });

    // Handle file uploads for additional assets
    $('.upload-file').on('click', function (e) {
        e.preventDefault();
        var button = $(this);
        var field = button.prev('input');

        var fileUploader = wp.media({
            title: 'Select File',
            button: {
                text: 'Use this file'
            },
            multiple: false
        });

        fileUploader.on('select', function () {
            var attachment = fileUploader.state().get('selection').first().toJSON();
            field.val(attachment.url);
        });

        fileUploader.open();
    });
});