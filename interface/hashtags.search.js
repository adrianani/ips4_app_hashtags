;( function($, _, undefined) {
    "use strict";

	ips.controller.mixin('hashtags.search', 'core.front.core.lightboxedImages', true, function() {

        this._hashtagClick = function(e) {
            e.preventDefault();

            // check if hashtag is a link / part of a link, if it is we hold it's url
            let elementToCheck = $(e.target).parent(),
                isLink = false,
                url,
                hashtagFilterUrl = ips.getSetting('baseURL') + 'index.php?app=core&module=search&controller=search&hashtags=' + $(e.target).data('hashtag');

            while( !isLink && elementToCheck.data('controller') !== 'core.front.core.lightboxedImages' ) {
                console.log(elementToCheck.data('class'));

                if( elementToCheck.prop('tagName') === 'A' ) {
                    url = elementToCheck.attr('href');
                    isLink = true;
                } else {
                    elementToCheck = elementToCheck.parent();
                }
            }

            // if the hashtag was linked to any link let the user decide what to do next
            if( isLink ) {
				ips.ui.alert.show({
					type: 'verify',
					icon: 'fa fa-warning',
					message: ips.getString('hashtagsLinkWarning'),
					subTextHtml: ips.getString('hashtagsLinkWarningSubtext'),
					callbacks: {
						yes: function() {
							window.open(url, '_blank');
							window.location.href = hashtagFilterUrl;
						},
						no: function() {
							window.location.href = hashtagFilterUrl;
						}
					}
				});
			} else {
				window.location.href = hashtagFilterUrl;
			}
        }

        this._hashtagTooltip = function(e) {
            let elm = $(e.target);

            elm.attr('_title', ips.getString('hashtagsFilterBy', {hashtag: elm.data('hashtag')}));

            ips.ui.tooltip.respond(elm, {}, e);
        }

        this.before('initialize', function() {
            this.on('click', 'span[data-hashtag]', _.bind( this._hashtagClick, this ));
            this.on('mouseenter mouseleave focus blur', 'span[data-hashtag]', _.bind( this._hashtagTooltip, this ));
        });
	});
}(jQuery, _));