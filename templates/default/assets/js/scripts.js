jQuery(document).ready(function ($) {
    var lecoCPLity;

    $(document).on('click', '[data-leco-cp-lity]', function (e) {
        e.preventDefault();

        var target = $(this).attr('href');

        lecoCPLity = lity(
            target,
            {
                'template': '<div class="lity" role="dialog" aria-label="Dialog Window (Press escape to close)" tabindex="-1"><div class="lity-wrap" data-lity-close role="document"><div class="lity-loader" aria-hidden="true">Loading...</div><div class="lity-container"><div class="lity-content"></div><button class="lity-close" type="button" aria-label="Close (Press escape to close)" data-lity-close><?xml version="1.0" encoding="utf-8" standalone="yes"?><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 48 48" style="enable-background:new 0 0 48 48;" xml:space="preserve"><style type="text/css">\n' +
                '\t.st0{fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;}\n' +
                '</style><title>Close</title><g id="Fail"><circle class="st0" cx="24" cy="24" r="23"></circle><g id="Cross"><line class="st0" x1="15" y1="15" x2="33" y2="33"></line><line class="st0" x1="15" y1="33" x2="33" y2="15"></line></g></g></svg></button></div></div></div>'
            }
        );
    }).on('click', '[data-leco-cp-mark-as-complete]', function () {
		var target = '#module_mark_as_complete_' + $(this).data('key');

		lecoCPLity = lity(
			target,
			{
				'template': '<div class="lity" role="dialog" aria-label="Dialog Window (Press escape to close)" tabindex="-1"><div class="lity-wrap" data-lity-close role="document"><div class="lity-loader" aria-hidden="true">Loading...</div><div class="lity-container"><div class="lity-content"></div><button class="lity-close" type="button" aria-label="Close (Press escape to close)" data-lity-close><?xml version="1.0" encoding="utf-8" standalone="yes"?><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 48 48" style="enable-background:new 0 0 48 48;" xml:space="preserve"><style type="text/css">\n' +
					'\t.st0{fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;}\n' +
					'</style><title>Close</title><g id="Fail"><circle class="st0" cx="24" cy="24" r="23"></circle><g id="Cross"><line class="st0" x1="15" y1="15" x2="33" y2="33"></line><line class="st0" x1="15" y1="33" x2="33" y2="15"></line></g></g></svg></button></div></div></div>'
			}
		);

		return false;
	}).on('click', '.lity-close svg', function() {
        lecoCPLity.close();
    }).on('click', '.leco-cp-sidebar h2:not(:first-of-type) a', function(){
        $(this).children('.iconset').toggleClass('hidden');
        $(this).parent().next('ul').slideToggle();
    }).on('click', '.module_mark_as_complete .btn', function(e){
        e.preventDefault();

		var module = $(this).parent().parent().attr('id').split('_'),
			phase = module[4],
			key = module[5],
			data = {
				'action': 'leco_cp_mark_as_complete',
				'post_id': lecoCPVars.post_id,
				'phase': phase,
				'key': key,
				'_wpnonce': lecoCPVars._wpnonce
			};

		$.post(lecoCPVars.ajaxurl, data, function (data) {
			if (-1 != data) {
				$('#m_' + phase + '_' + key).removeClass('mark-as-complete').addClass('completed');
				$('#m_' + phase + '_' + key).find('.checkmark').removeAttr('data-leco-cp-mark-as-complete').find('.tooltip').html('Completed<span class="arrow"></span>');
			} else {
				console.log( 'Failed to update the module status.' );
			}
		});

		lecoCPLity.close();
    });
});