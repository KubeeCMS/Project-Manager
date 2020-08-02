(window.webpackJsonp=window.webpackJsonp||[]).push([[2],{10:function(e,t,n){"use strict";n.r(t);var a=n(1),s={name:"App"},i=n(0),r=Object(i.a)(s,(function(){var e=this.$createElement,t=this._self._c||e;return t("div",{attrs:{id:"vue-backend-app"}},[t("h1",[this._v("Backend App")]),this._v(" "),t("router-view")],1)}),[],!1,null,null,null).exports,u=n(2),l={name:"Home",data:()=>({msg:"Welcome to Your Vue.js Admin App"})},p=Object(i.a)(l,(function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"home"},[t("span",[this._v(this._s(this.msg))])])}),[],!1,null,"1be020c0",null).exports,c={name:"Settings",data:()=>({})},o=Object(i.a)(c,(function(){var e=this.$createElement;return(this._self._c||e)("div",{staticClass:"app-settings"},[this._v("\n  The Settings Page\n")])}),[],!1,null,"9b386344",null).exports;a.a.use(u.a);var h=new u.a({routes:[{path:"/",name:"Home",component:p},{path:"/settings",name:"Settings",component:o}]});var m=function(e){var t=jQuery;let n=t("#toplevel_page_"+e),a=window.location.href,s=a.substr(a.indexOf("admin.php"));n.on("click","a",(function(){var e=t(this);t("ul.wp-submenu li",n).removeClass("current"),e.hasClass("wp-has-submenu")?t("li.wp-first-item",n).addClass("current"):e.parents("li").addClass("current")})),t("ul.wp-submenu a",n).each((function(e,n){t(n).attr("href")!==s||t(n).parent().addClass("current")}))};a.a.config.productionTip=!1,new a.a({el:"#vue-admin-app",router:h,render:e=>e(r)}),m("vue-app")}},[[10,0,1]]]);



jQuery(document).ready(function ($) {
	if ( $('#leco_cp_client').length > 0 ) {
		$('select#leco_cp_client').select2();
	}

	$(document).on('click', '.leco_cp_save, #leco_cp_save', function (e) {
		$('#publish').click();
		e.preventDefault();
	});

	$('#leco_cp_template').on('change', function () {
		$(this).next('.button').remove();
		$(this).next('.button-primary').remove();

		if ('0' !== $(this).val()) {
			var data = {
					'action': 'leco_cp_get_permalink',
					'post_id': $(this).val(),
					'_wpnonce': lecoCPAdminVars._wpnonce
				},
				$this = $(this);
			$.post(ajaxurl, data, function (data) {
				if (-1 != data) {
					$('<a id="leco_cp_template_preview" href="' + data + '" class="button button-large" target="_blank">Preview</a> ').insertAfter($this);
				}
			});

			$('<input id="leco_cp_save" class="button button-primary button-large leco_cp_save" type="button" name="leco_cp_template_submit" value="Use This Template">').insertAfter($(this));
			$('#leco_cp_custom_branding, #leco_cp_info').hide();
		} else {
			$('#leco_cp_custom_branding, #leco_cp_info').show();
		}
	});

	function pjsHideThree(psjv) {
		if (psjv === 'hide') {
			$('.cmb2-id-leco-cp-current-phase, .cmb2-id-leco-cp-next-phase, .cmb2-id-leco-cp-completion-date').hide();
		} else {
			$('.cmb2-id-leco-cp-current-phase, .cmb2-id-leco-cp-next-phase, .cmb2-id-leco-cp-completion-date').show();
		}
	}

	if ($('#leco_cp_show_project_status').length > 0) {
		var pjs = $('#leco_cp_show_project_status'), psjv = pjs.val();

		pjsHideThree(psjv);

		pjs.on('change', function () {
			var show = $(this).val();
			pjsHideThree(show);
		});
	}

	function switchModule(field, moduleType) {
		if ( field.hasClass('module-type-group') ) {
			field.children('.module-type').hide();
			field.children('.module-type-' + moduleType).show();
		} else {
			field.find('.module-type').hide();
			field.find('.module-type-' + moduleType).show();
		}
	}

	$('.module-type-select').each(function () {
		var moduleType = $(this).find('select').val(),
			field = $(this).parent('.module-type-group');

		setModuleType( moduleType, field );
	});

	function setModuleType(moduleType, field) {
        var contentPage = field.children('.module-type-content-page').find('.cmb2_select');

		switch( moduleType ) {
			case 'content_page':
				field.children('.module-type-files').hide();
				field.children('.module-type-private-files').hide();
				field.children('.module-type-url').hide();
				field.children('.module-type-client-uploads').hide();

				var addNew = contentPage.next('.add-new-content-page'),
					post_id = contentPage.val(),
					isDisabled = false,
					data = {},
					selectElem = field.children('.module-type-content-page');

				if ( lecoCPAdminVars.supportComments === '1' ) {
					disableContentPagesHaveComments(contentPage, selectElem);
				}

				if (post_id !== '0') {
                    data = {
                        'action': 'leco_cp_get_permalink',
                        'post_id': post_id,
                        'permalink': lecoCPAdminVars.permalink,
                        '_wpnonce': lecoCPAdminVars._wpnonce
                    };
                    $.post(ajaxurl, data, function (data) {
                        if (-1 != data) {
                            contentPage.nextAll('.preview-content-page').remove();
                            contentPage.nextAll('.edit-content-page').remove();
                            $('<a href="' + data + '" class="button button-large preview-content-page" target="_blank">Preview</a> ').insertAfter(contentPage.next('.add-new-content-page')).after('<a href="post.php?post=' + contentPage.val() + '&action=edit&parent_id=' + $('#post_ID').val() + '" class="button button-large edit-content-page" target="_blank">Edit</a> ');
                        }
                    });
				}

                if (!isDisabled) {
                    if ( ! addNew.length ) {
                    	var display = (post_id !== '0') ? 'display:none' : '';
                        contentPage.after('<a href="javascript:void(0)" class="button button-large button-primary add-new-content-page" style="' + display + '">Add Now</a>');
                    }
				}

				break;
			default:
				contentPageOptionRestore(contentPage);
				switchModule(field, moduleType);
		}
	}

	function disableContentPagesHaveComments(contentPage, selectElem) {
        contentPage.children('option').each(function(){
            var $this = $(this);
            if ($this.attr('selected') !== 'selected') {
                var data = {
                    'action': 'leco_cp_content_page_has_comment',
                    'post_id': $this.attr('value'),
                    '_wpnonce': lecoCPAdminVars._wpnonce
                };
                $.post(ajaxurl, data, function (data) {
                    if (parseInt(data) > 0 && data !== $('#post_ID').val()) {
                        $this.attr('disabled', 'disabled');
                    }

					selectElem.show();
                    // Add button to create new content page.
                    if ( ! selectElem.find('.add-new-content-page').length )
                        contentPage.after('<a href="javascript:void(0)" class="button button-large button-primary add-new-content-page">Add Now</a>');
                });
            }
        });
    }

	function getModuleTitle(elem) {
		var title = $(elem).next('.cmb-field-list').children(':first-child').find('input.regular-text').val(),
			currentTitle = $(elem).html();
		if ($(elem).children().length > 1) {
			var originalTitle = $(elem).children(':first-child')[0].outerHTML;
			$(elem).html(originalTitle + '<span> - ' + title + '</span>');
		} else {
			$(elem).html(currentTitle + '<span> - ' + title + '</span>');
		}
	}

	$('.cmb-group-title').each(function (i, elem) {
		if (i > 0) {
			getModuleTitle(elem);
		}
	});

	function formatState(state) {
		if (!state.id) {
			return state.text;
		}
		var $state = $(
			'<span><i class="icon-' + state.element.value + '"></i> ' + state.text + '</span>'
		);
		return $state;
	}

	function format(state) {
		if (!state.id) {
			return state.text;
		}
		var $state = $(
			'<img class="icon" src="' + lecoCPAdminVars.iconURL + 'caviar/' + state.id.toLowerCase() + '.svg" width="18" height="18" style="vertical-align:middle" /> <span>' + state.text + '</span>'
		);
		return $state;
	}

	function contentPageOptionRestore( contentPage ) {
		contentPage.val('0');
        contentPage.nextAll('.preview-content-page').remove();
        contentPage.nextAll('.edit-content-page').remove();
	}

	var lecoCPSelect2Option = {
		templateResult: format,
		templateSelection: format,
		width: "40%"
	};

	$('.select2-icon').select2(lecoCPSelect2Option);

	$(document) // Apply select2 on cloned rows
		.on('cmb2_add_row', function (evt, row) {
			row.find('span.select2').remove();
			row.find('select.select2-icon').removeClass('select2-hidden-accessible').select2(lecoCPSelect2Option);
			switchModule(row, 'url');
		})
		.on('cmb_init', function () {
			$('.cmb-remove-row-button, .cmb-remove-group-row').on('click', function (e) {
				var msg = "";
				if ( 'leco_cp_phases_repeat' === $(this).data( 'selector' ) ) {
					msg = "Remove this phase will DELETE all its modules.\r\nAre you sure?";
				} else {
					msg = "Are you sure to remove this module?";
				}

				var r = confirm(msg);

				if (!r) {
					e.stopImmediatePropagation();
				}
			});
		})
		.on('cmb_media_modal_init', function(evt, media) {
			if (media.field.indexOf('private_files') !== -1) {
				var cmb = window.CMB2, handlers = window.CMB2.mediaHandlers, isList = media.isList, id = media.field;

				// borrowed from Download Monitor
				media.frames[id].on( 'ready', function () {
					media.frames[id].uploader.options.uploader.params.type = 'leco_private_file';
				} );

				media.frames[id].on( 'select', function() {
					var selection = media.frames[id].state().get( 'selection' );
					var type = isList ? 'list' : 'single';

					// replace selection urls.
					for (var i = 0; i < selection.models.length; i++) {
						selection.models[i].attributes.url = lecoCPAdminVars.permalink + 'file/' + btoa($('#post_ID').val() + '|' + selection.models[i].id) + '/' + selection.models[i].attributes.filename;
					}

					if ( cmb.attach_id && isList ) {
						$( '[data-ifd="'+ cmb.attach_id +'"]' ).parents( 'li' ).replaceWith( handlers.list( selection, true ) );
					} else {
						handlers[type]( selection );
					}

					cmb.trigger( 'cmb_media_modal_select', selection, media );
				});
			}
		})
		.on('cmb_media_modal_open', function(evt, selection, media) {
			if (media.field.indexOf('private_files') !== -1) {
				var handlers = window.CMB2.mediaHandlers, id = media.field;

				media.frames[id].off( 'select', handlers.selectFile );
			}
		})
		.on('click', '.cmb-shift-rows', function (evt) {
			var $this = $(this);
			var $from = $this.parents('.cmb-repeatable-grouping');
			var $goto = $this.hasClass('move-up') ? $from.prev('.cmb-repeatable-grouping') : $from.next('.cmb-repeatable-grouping');

			getModuleTitle($from.find('.cmb-group-title'));
			getModuleTitle($goto.find('.cmb-group-title'));

			$('.select2-icon').select2(lecoCPSelect2Option);

			var gotoModuleType = $goto.find('.module-type-select').find('select').val(),
				gotoField = $goto.find('.module-type-select').parent('.module-type-group');
			var fromModuleType = $from.find('.module-type-select').find('select').val(),
				fromField = $from.find('.module-type-select').parent('.module-type-group');
			setModuleType( gotoModuleType, gotoField );
			setModuleType( fromModuleType, fromField );
		})
		.on('change', '.module-type-select select', function () {
			var moduleType = $(this).val(),
				field = $(this).parents('.module-type-group'),
                contentPage = field.children('.module-type-content-page').find('.cmb2_select');

			switch (moduleType) {
				case 'content_page':
					field.children('.module-type-url').hide();
					field.children('.module-type-files').hide();
					field.children('.module-type-private-files').hide();
					field.children('.module-type-client-uploads').hide();
					var selectElem = field.children('.module-type-content-page');

					if ( lecoCPAdminVars.supportComments === '1' ) {
						disableContentPagesHaveComments(contentPage, selectElem);
					} else {
						selectElem.show();
						if ( ! selectElem.find('.add-new-content-page').length ) {
							contentPage.after('<a href="javascript:void(0)" class="button button-large button-primary add-new-content-page">Add Now</a>');
						} else {
							selectElem.find('.add-new-content-page').show();
							selectElem.find('.preview-content-page').remove();
							selectElem.find('.edit-content-page').remove();
						}
					}

					break;
				default:
					contentPageOptionRestore(contentPage);
					switchModule(field, moduleType);
			}
		})
		.on('change', '.module-type-content-page select', function() {
			if ('0' !== $(this).val()) {
				var addNew = $(this).next('.add-new-content-page'),
					data = {
						'action': 'leco_cp_get_permalink',
						'post_id': $(this).val(),
						'permalink': lecoCPAdminVars.permalink,
						'_wpnonce': lecoCPAdminVars._wpnonce
					},
					$this = $(this);

				addNew.hide();
				$.post(ajaxurl, data, function (data) {
					if (-1 != data) {
						$this.nextAll('.preview-content-page').remove();
						$this.nextAll('.edit-content-page').remove();
						$('<a href="' + data + '" class="button button-large preview-content-page" target="_blank">Preview</a> ').insertAfter(addNew).after('<a href="post.php?post=' + $this.val() + '&action=edit&parent_id=' + $('#post_ID').val() + '" class="button button-large edit-content-page" target="_blank">Edit</a> ');
					}
				});
			} else {
				$(this).next('.add-new-content-page').show();
				$(this).nextAll('.preview-content-page').remove();
				$(this).nextAll('.edit-content-page').remove();
			}
		})
		.on('click', '.module-type-content-page .add-new-content-page', function (e) {
			e.preventDefault();

			$(this).html('Adding...');
			var permalink = lecoCPAdminVars.permalink,
				fieldList = $(this).closest('.cmb-field-list'),
				title = fieldList.find('.module-title').find('input').val(),
				content = fieldList.find('.module-desc').find('textarea').val(),
				data = {
					'action': 'leco_cp_add_content_page',
					'title': title,
					'content': content,
					'post_id': $('#post_ID').val(),
					'_wpnonce': lecoCPAdminVars._wpnonce
				},
				$this = $(this);

			$.post(ajaxurl, data, function (data) {
				if (-1 != data) {
					data = JSON.parse(data);
					// Update select option
					$this.prev('select').children('option').removeAttr('selected');
					// Append new option to ALL content page fields
					$('.module-type-content-page select').append('<option value="' + data.ID + '">' + title + '</option>');
					$this.prev('select').children('option').last().attr('selected', 'selected');
					$this.after('<code class="just-created-content-page">You just create a new content page - ' + title + '!</code>');
					$('.just-created-content-page').fadeOut(1000, 'linear');
					$this.html('Add New');
					$this.hide();

					$('<a href="' + permalink  + 'module/' + data.post_name + '/" class="button button-large preview-content-page" target="_blank">Preview</a> ').insertAfter($this).after('<a href="post.php?post=' + data.ID + '&action=edit&parent_id=' + $('#post_ID').val() + '" class="button button-large edit-content-page" target="_blank">Edit</a> ');
				}
			});
		})
		.on('click', '.leco-cp-notice.is-dismissible .notice-dismiss', function () {
			var notice_el = $(this).closest('.leco-cp-notice');

			var notice = notice_el.attr('id');
			var notice_nonce = notice_el.attr('data-nonce');
			$.post(
				ajaxurl,
				{
					action: 'leco_cp_dismiss_notice',
					nonce: notice_nonce,
					notice: notice
				}
			)
		});

	$('#leco_cp_public_portal').on('change', function () {
		if ('yes' === $(this).val()) {
			var r = confirm("Are you sure you want to make this portal public?\r\n\r\nPublic means it’s visible to anybody with the URL. Please don’t share any passwords or private information in a public portal.");

			if (r) {
				$(this).val('yes');
			} else {
				$(this).val('no');
			}
		}
	});

	if ( typeof typenow !== 'undefined' && typenow === 'leco_content_page' && lecoCPAdminVars.supportComments === '1') {
		if ( $('.leco-project-link.private').length > 0 && $('.leco-project-link').length > 1 ) {
			$('#comment_status').attr('disabled', 'disabled');
		}
	}
});
