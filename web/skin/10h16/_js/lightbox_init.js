$(initWLightbox);

/* wlightbox inline */
function initWLightbox() {
    $('.rtcol, footer').each(function () {
        var $container = $(this);

        if ($container.find('a.wlightbox').length < 1) return true;

        $container.addClass('temp-show-for-js-calculating');

        $container.find('a.wlightbox').each(function () {
            var element = $(this);
            element.wlightbox(
                getLightboxOptions(
                    element, isFooterElement(element)
                )
            );
            $(this).on('click',
                function () {
                    triggerLightboxPiwikTrack(element)
                }
            );
        });

        $container.removeClass('temp-show-for-js-calculating')
    });
}

function triggerLightboxPiwikTrack($lBoxLink) {
    var lightboxCode = $lBoxLink.attr('data-href').replace('#', '');
    var piwikImgUrl = 'https://tracking.wikimedia.de/piwik.php?idsite=1&url=https://spenden.wikimedia.de/lightbox-clicked/' + lightboxCode + '&rec=1';

    $lBoxLink.prepend('<img src="' + piwikImgUrl + '" width="0" height="0" border="0" />');
}

function getLightboxOptions($lBoxLink, isFooterElement) {
    var elementOptions = $lBoxLink.data('wlightbox-options'),
        options;
    if (isFooterElement) {
        options = getOptionsForFooterLink($lBoxLink);
    } else {
        options = getOptionsForSidebarLink($lBoxLink);
    }
    if (typeof elementOptions !== 'undefined') {
        $.extend(options, elementOptions);
    }
    return options;
}

function getOptionsForFooterLink($lBoxLink) {
    var $containerWLightbox = $('#main > .container');

    return {
        container: $containerWLightbox,
        top: '150px',
        left: '128px',
        maxWidth: '686px'
    };
}

function getOptionsForSidebarLink($lBoxLink) {
    var $containerWLightbox = $('#main > .container');

    return {
        container: $containerWLightbox,
        top: 0,
        left: '128px',
        maxWidth: '686px'
    };
}

/* determine whether the lightbox link is a child of the footer bar */
function isFooterElement($lBoxLink) {
    return $('#footer').has($lBoxLink).length > 0;
}