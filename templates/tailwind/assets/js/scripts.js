jQuery( document ).ready( function ( $ ) {
	var lecoCPLity,
		lityDeleteIcon = '<a href="javascript:void(0);" class="file-delete" data-file-id="{file-id}"><span class="icon-file-delete">\n' +
			'                        <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><defs></defs><g id="Trash"><path class="cls-1" d="M40,7L38.38,39.4a8,8,0,0,1-8,7.6H17.61a8,8,0,0,1-8-7.6L8,7"/><line class="cls-1" x1="4" y1="7" x2="44" y2="7"/><line class="cls-1" x1="18.5" y1="15" x2="19.5" y2="37"/><line class="cls-1" x1="29.5" y1="15" x2="28.5" y2="37"/><path class="cls-1" d="M16,7l1.09-3.26A4,4,0,0,1,20.88,1h6.23a4,4,0,0,1,3.79,2.74L32,7"/></g></svg>\n' +
			'                    </span>',
		lityDownloadIcon = '<span class="icon-download">\n' +
			'                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px"\n' +
			'                             y="0px" viewBox="0 0 48 48" style="enable-background:new 0 0 48 48;" xml:space="preserve">\n' +
			'                            <title>Download</title>\n' +
			'                            <g>\n' +
			'                                <path class="st0" d="M43,31v6c0,3.3-3,6-6.6,6H11.6C8,43,5,40.3,5,37v-6"></path>\n' +
			'                                <line class="st0" x1="24" y1="4" x2="24" y2="31"></line>\n' +
			'                                <polyline class="st0" points="14,21 24,31 34,21  "></polyline>\n' +
			'                            </g>\n' +
			'                        </svg>\n' +
			'                     </span></a>',
		lecoUploader,
		lecoSearchTimer;

	// Backwards compatibilty for custom phase boxes.
	if ( $( '.entry .project-status' ).length ) {
		var projectPhases = [ '.current', '.next', '.completion-date' ];
		for ( var i = 0; i < 3; i++ ) {
			$( '.project-status .leco-cp-container ' + projectPhases[ i ] + ' .value' ).html( $( '.entry .project-status ' + projectPhases[ i ] + ' .value' ).text() );
			$( '.project-status .leco-cp-container ' + projectPhases[ i ] + ' .label' ).html( $( '.entry .project-status ' + projectPhases[ i ] + ' .label' ).text() );
		}
	}

	$( document ).on( 'click', '[data-leco-cp-lity]', function ( e ) {
		e.preventDefault();

		var target = $( this ).attr( 'href' );

		lecoCPLity = lity(
			target,
			{
				template: '<div class="lity" role="dialog" aria-label="Dialog Window (Press escape to close)" tabindex="-1"><div class="lity-wrap" data-lity-close role="document"><div class="lity-loader" aria-hidden="true">Loading...</div><div class="lity-container"><div class="lity-content"></div><button class="lity-close" type="button" aria-label="Close (Press escape to close)" data-lity-close><?xml version="1.0" encoding="utf-8" standalone="yes"?><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 48 48" style="enable-background:new 0 0 48 48;" xml:space="preserve"><style type="text/css">\n' +
					'\t.st0{fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;}\n' +
					'</style><title>Close</title><g id="Fail"><circle class="st0" cx="24" cy="24" r="23"></circle><g id="Cross"><line class="st0" x1="15" y1="15" x2="33" y2="33"></line><line class="st0" x1="15" y1="33" x2="33" y2="15"></line></g></g></svg></button></div></div></div>'
			},
			$( this )
		);
	} ).on( 'click', '[data-leco-cp-mark-as-complete]', function () {
		var target = '#module_mark_as_complete_' + $( this ).data( 'key' );

		lecoCPLity = lity(
			target,
			{
				template: '<div class="lity" role="dialog" aria-label="Dialog Window (Press escape to close)" tabindex="-1"><div class="lity-wrap" data-lity-close role="document"><div class="lity-loader" aria-hidden="true">Loading...</div><div class="lity-container"><div class="lity-content"></div><button class="lity-close" type="button" aria-label="Close (Press escape to close)" data-lity-close><?xml version="1.0" encoding="utf-8" standalone="yes"?><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 48 48" style="enable-background:new 0 0 48 48;" xml:space="preserve"><style type="text/css">\n' +
					'\t.st0{fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;}\n' +
					'</style><title>Close</title><g id="Fail"><circle class="st0" cx="24" cy="24" r="23"></circle><g id="Cross"><line class="st0" x1="15" y1="15" x2="33" y2="33"></line><line class="st0" x1="15" y1="33" x2="33" y2="15"></line></g></g></svg></button></div></div></div>'
			}
		);

		return false;
	} ).on( 'click', '.lity-close svg', function () {
		lecoCPLity.close();
	} ).on( 'click', '.leco-cp-sidebar h2:not(:first-of-type) a', function () {
		$( this ).children( '.iconset' ).toggleClass( 'hidden' );
		$( this ).parent().next( 'ul' ).slideToggle();
	} ).on( 'click', '.module_mark_as_complete .btn', function ( e ) {
		e.preventDefault();

		var module = $( this ).parent().parent().attr( 'id' ).split( '_' ),
			phase = module[ 4 ],
			key = module[ 5 ],
			data = {
				'action': 'leco_cp_mark_as_complete',
				'post_id': lecoCPVars.post_id,
				'phase': phase,
				'key': key,
				'_wpnonce': lecoCPVars._wpnonce
			};

		$.post( lecoCPVars.ajaxurl, data, function ( data ) {
			if ( -1 != data ) {
				$( '#m_' + phase + '_' + key ).removeClass( 'mark-as-complete' ).addClass( 'completed' );
				$( '#m_' + phase + '_' + key ).find( '.checkmark' ).removeAttr( 'data-leco-cp-mark-as-complete' ).find( '.tooltip' ).html( 'Completed<span class="arrow"></span>' );
			} else {
				console.log( 'Failed to update the module status.' );
			}
		} );

		lecoCPLity.close();
	} ).on( 'click', '#sidebar-open', function () {
		$( '.leco-cp-sidebar' ).show();
		$( '#sidebar-close' ).removeClass( 'hidden' );
		$( this ).addClass( 'hidden' );
	} ).on( 'click', '#sidebar-close', function () {
		$( '.leco-cp-sidebar' ).hide();
		$( '#sidebar-open' ).removeClass( 'hidden' );
		$( this ).addClass( 'hidden' );
	} ).on( 'focus', '#docsearch', function () {
		$( '.search-feedback' ).removeClass( 'hidden' );
		if ( $( this ).val().length < 3 ) {
			$( '.results-default' ).removeClass( 'hidden' );
			$( '.has-results, .no-result' ).addClass( 'hidden' );
		}
	} ).on( 'blur', '#docsearch', function () {
		$( '.search-feedback' ).addClass( 'hidden' );
	} ).on( 'keyup', '#docsearch', function () {
		var keyword = $( this ).val().trim();
		var data = {
			'action': 'leco_cp_live_portal_search',
			'post_id': lecoCPVars.post_id,
			's': keyword,
			'_wpnonce': lecoCPVars._wpnonce
		};

		clearTimeout( lecoSearchTimer );

		if ( keyword.length > 2 ) {
			lecoSearchTimer = setTimeout( function () {
				$.post( lecoCPVars.ajaxurl, data, function ( data ) {
					if ( data != -1 ) {
						$( '.has-results' ).removeClass( 'hidden' );
						$( '.no-result, .results-default, .module-wrap' ).addClass( 'hidden' );

						for ( var i = 0; i < data.data.length; i++ ) {
							if ( typeof data.data[ i ] === 'string' ) {
								$( '.' + data.data[ i ] ).removeClass( 'hidden' );
							} else {
								$( '#content_page_' + data.data[ i ] ).removeClass( 'hidden' );
							}
						}
					} else {
						$( '.has-results, .results-default' ).addClass( 'hidden' );
						$( '.no-result, .module-wrap' ).removeClass( 'hidden' );
					}
				} );
			}, 500 );
		} else if ( keyword === '' ) {
			$( '.has-results, .no-result' ).addClass( 'hidden' );
			$( '.search-feedback, .results-default' ).removeClass( 'hidden' );
			$( '.module-wrap' ).removeClass( 'hidden' );
		}
	} ).on( 'lity:ready', function ( e, instance ) {
		if ( instance.opener().find( '.client-uploads-container' ) ) {
			var module = instance.opener().attr( 'href' ),
				browse_button = $( e.target ).find( '.choose-files' );

			lecoUploader = new plupload.Uploader( {
				runtimes: 'html5,flash,silverlight,html4',
				browse_button: browse_button[ 0 ], // this can be an id of a DOM element or the DOM element itself,
				drop_element: e.target,
				url: lecoCPVars.ajaxurl,
				flash_swf_url: lecoCPVars.flash_swf_url,
				silverlight_xap_url: lecoCPVars.silverlight_xap_url,
				filters: lecoCPVars.filters,
				init: {
					Init: function ( up ) {
						var uploaddiv = $( e.target );

						if ( up.features.dragdrop ) {
							uploaddiv.addClass( 'drag-drop' );

							uploaddiv
								.bind( 'dragover.wp-uploader', function () {
									$( e.target ).find( '.choose-files' ).addClass( 'drag-over' );
								} )
								.bind( 'dragleave.wp-uploader, drop.wp-uploader', function () {
									$( e.target ).find( '.choose-files' ).removeClass( 'drag-over' );
								} );
						}
					},
					UploadProgress: function ( up, file ) {
						$( '#' + file.id + ' .progress' ).css( 'width', file.percent + '%' );
					},
					FilesAdded: function ( up, files ) {
						var html = '';
						plupload.each( files, function ( file ) {
							html += '<li id="' + file.id + '" class="client-uploads"><a href="file/' + file.id + '/" download><span class="progress"></span>' + file.name + ' (' + plupload.formatSize( file.size ) + ') ' + lityDownloadIcon + '</a>' + lityDeleteIcon.replace( '{file-id}', file.id ) + '</li>';
						} );
						$( e.target ).find( '.files' ).append( html );

						up.refresh();
						up.start();
					},
					UploadComplete: function ( up, files ) {
						// save the files array to the module.
						var data = {
							'action': 'leco_cp_client_upload_complete',
							'post_id': lecoCPVars.post_id,
							'module': module,
							'_wpnonce': lecoCPVars._wpnonce,
							'files': JSON.stringify( files )
						};

						$.post( lecoCPVars.ajaxurl, data, function ( data ) {
							if ( data.success ) {
								console.log( data );
							} else {
								alert( data.data.message );
							}
						} );
					},
					FileUploaded: function ( up, file, result ) {
						result = JSON.parse( result.response );

						if ( ! result.data.error ) {
							file.file = result.data.file;
						} else {
							$( '#' + file.id ).hide( 'slow', function () {
								$( this ).remove();
							} );
							alert( result.data.error );
						}
					}
				},
				multipart_params: {
					'action': 'leco_cp_client_upload',
					'post_id': lecoCPVars.post_id,
					'module': module,
					'_wpnonce': lecoCPVars._wpnonce
				}
			} );
			lecoUploader.init();

			lecoUploader.bind( 'Error', function ( up, err ) {
				alert( lecoCPVars.failed_to_upload + ' ' + err.file.name + '. ' + lecoCPVars.error + ' ' + err.message );
			} );
		}
	} ).on( 'lity:close', function ( e, instance ) {
		if ( instance.opener().parent().hasClass( 'client-uploads' ) ) {
			if ( lecoUploader instanceof plupload.Uploader ) {
				lecoUploader.destroy();
			}
		}
	} ).on( 'click', '.file-delete', function () {
		var result = confirm( lecoCPVars.delete_client_upload ), $this = $( this );

		if ( result ) {
			var data = {
				'action': 'leco_cp_client_upload_delete',
				'post_id': lecoCPVars.post_id,
				'module': '#' + $( this ).parent().parent().parent().attr( 'id' ),
				'_wpnonce': lecoCPVars._wpnonce,
				'file_id': $( this ).data( 'file-id' )
			};

			$.post( lecoCPVars.ajaxurl, data, function ( data ) {
				if ( data.success ) {
					$this.parent( 'li' ).hide( 'slow', function () {
						$( this ).remove();
					} );
				} else {
					alert( data.data.message );
				}
			} );
		}
	} );
} );