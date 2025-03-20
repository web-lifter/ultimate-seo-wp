jQuery(document).ready(function ($) {
    $('#regenerate-sitemap').on('click', function () {
        let button = $(this);
        let statusMessage = $('#sitemap-status');

        button.prop('disabled', true).text('Regenerating...');
        statusMessage.text('');

        $.post(ultimateSEOAjax.ajaxurl, {
            action: 'regenerate_sitemap',
            security: ultimateSEOAjax.nonce
        })
        .done(function (response) {
            if (response.success) {
                statusMessage.css('color', 'green').text(response.data.message);
            } else {
                statusMessage.css('color', 'red').text('Error regenerating sitemap.');
            }
        })
        .fail(function () {
            statusMessage.css('color', 'red').text('Request failed. Try again.');
        })
        .always(function () {
            button.prop('disabled', false).text('Regenerate Sitemap');
        });
    });
});
