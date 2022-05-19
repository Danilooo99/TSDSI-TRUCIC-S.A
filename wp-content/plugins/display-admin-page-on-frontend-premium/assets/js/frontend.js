function vgfaInitIframes() {
	var $iframeWrappers = jQuery('.vgca-iframe-wrapper:not(.wpfa-initialized)');

	$iframeWrappers.each(function () {
		var $iframeWrapper = jQuery(this);
		$iframeWrapper.addClass('wpfa-initialized');
		var $iframe = $iframeWrapper.find('iframe');
		var hash = window.location.hash;
		var iframe = document.getElementById($iframe.attr('id'));

		if (!vgfa_data.disable_stateful_navigation && window.location.hash && window.location.hash.indexOf('wpfa:') > -1 && jQuery('.vgca-iframe-wrapper').length === 1 && !$iframe.data('urlFromHashApplied')) {
			var decodedHash = atob(window.location.hash.replace('#wpfa:', ''));
			if (decodedHash.indexOf('http') < 0) {
				var urlFromHash = vgfa_data.wpadmin_base_url + decodedHash;
				$iframe.data('urlFromHashApplied', 1);
				$iframe.data('wpfa', urlFromHash);
				if (!$iframe.data('lazy-load')) {
					$iframe.attr('src', urlFromHash);
					wpFrontendAdminFrontend.notifyIfPageDoesNotLoad($iframeWrapper);
				}
			}
			//console.log('urlFromHash: ', urlFromHash);
		}

		if ($iframe.data('forward-parameters') && hash && hash.indexOf('wpfa:') < 0) {
			if ($iframe.data('lazy-load')) {
				$iframe.data('wpfa', $iframe.data('wpfa') + hash);
			} else {
				$iframe.attr('src', $iframe.attr('src') + hash);
				wpFrontendAdminFrontend.notifyIfPageDoesNotLoad($iframeWrapper);
			}
		}

		$iframe.data('lastPage', $iframe.attr('src'));

		// Note. We used to update iframe height when the window is resized
		// but the mobile scroll sometimes triggers the resize event so it
		// was causing a flickering issue. However, with the latest css
		// changes, we no longer need to update the height on resize.

		wpFrontendAdminFrontend.sendFrontendSettingsToIframe($iframe, iframe);
		setInterval(function () {
			wpFrontendAdminFrontend.sendFrontendSettingsToIframe($iframe, iframe);
		}, 1000);


	});
}

var wpFrontendAdminFrontend = {
	getWPAdminDirectory: function () {
		var wpAdminParts = vgfa_data.wpadmin_base_url.split('/').filter(n => n);
		var wpAdminDirectory = '/' + wpAdminParts[ wpAdminParts.length - 1 ] + '/';
		return wpAdminDirectory;
	},
	notifyIfPageDoesNotLoad: function ($iframeWrapper) {
		// Notify if the page doesn't load in 10 seconds
		setTimeout(function () {
			if ($iframeWrapper.hasClass('vgfa-is-loading')) {
				$iframeWrapper.find('.wpfa-loading-too-long-message').show();
				$iframeWrapper.find('.vgca-loading-indicator').hide();
			}
		}, 40000);
	},
	sendFrontendSettingsToIframe: function ($iframe, iframe) {
		if ($iframe.data('lazy-load') && $iframe.is(':visible') && !$iframe.attr('src')) {
			vgfaStartLoading({iframeId: $iframe.attr('id')});

			// If the iframe doesn't have a wp-admin URL since the start, open it in the main window
			if ($iframe.data('wpfa').indexOf(this.getWPAdminDirectory()) < 0) {
				window.location.href = $iframe.data('wpfa');
			}
			$iframe.attr('src', $iframe.data('wpfa'));
			wpFrontendAdminFrontend.notifyIfPageDoesNotLoad($iframe.parents('.vgca-iframe-wrapper'));
		}

		wpFrontendAdminFrontend.iframeFunction('wpfaSetIframeState', {
			'id': $iframe.attr('id'),
			'url': window.location.href,
			'hash': window.location.hash,
			'isEditingText': typeof window.vgfaIsEditingText !== 'undefined' ? window.vgfaIsEditingText : null,
			'adminCss': vgfa_data.admin_css,
			'minimumHeight': parseInt($iframe.data('minimum-height')),
			'sourceId': parseInt($iframe.data('source-id'))
		}, iframe);
	},
	iframeFunction: function (functionName, arguments, iframe) {
		var iframes = [];
		if (!iframe) {
			var $visibleBackendPage = vgfaGetVisibleAdminPage();
			if (!$visibleBackendPage.length) {
				return true;
			}
			iframes = $visibleBackendPage.get();
		} else {
			iframes.push(iframe);
		}

		var dataToSend = JSON.stringify({
			'functionName': functionName,
			'arguments': arguments
		});
		// In case the iframe loaded in a popup and the popup html is removed when it's closed
		iframes.forEach(function (iframe) {
			if (iframe.contentWindow) {
				iframe.contentWindow.postMessage(dataToSend, '*');
			}
		});
	}
};
function vgfaUpdateIframeData(e) {
	var args = e.data;
	var iframeId = e.iframeId;
	var currentPage = args.url;
	var iframeHeight = args.height;
	var adminBodyClasses = args.bodyClasses;
	var visiblePopupsStartsAt = args.visiblePopupsStartsAt;
	var gutenbergEditorFound = args.gutenbergEditorFound;
	var $iframe = jQuery('#' + iframeId);
	if (!$iframe.length) {
		return true;
	}
	var $iframeWrapper = $iframe.parents('.vgca-iframe-wrapper');
	var iframeStartsAt = $iframe.offset().top;
	// If the user navigated to another admin page, update the iframe height
	if (currentPage !== $iframe.data('lastPage')) {
		$iframeWrapper.css('height', '');
		$iframe.css('height', '');
		$iframe.data('lastHeight', '');
		$iframe.data('lastPage', currentPage);

		// If we have one shortcode in the page and the current page is not using any hash for other purposes, 
		// add the current path to the URL so we can reload the page and preserve the iframe state
		if (!vgfa_data.disable_stateful_navigation && jQuery('.vgca-iframe-wrapper').length === 1 && (!window.location.hash || window.location.hash.indexOf('wpfa:') > -1)) {
			var urlForHash = currentPage.replace(vgfa_data.wpadmin_base_url, '').replace(/#wpfa.+$/, '');
			var initialUrl = $iframe.data('wpfa').replace(vgfa_data.wpadmin_base_url, '').replace(/#wpfa.+$/, '');
			var newHash = '#wpfa:' + btoa(urlForHash);

			if (urlForHash !== initialUrl && newHash !== window.location.hash) {
				if (window.location.hash) {
					window.wpfaIgnoreHashChange = true;
					window.location.hash = newHash;
				} else {
					window.location.replace(window.location.href += newHash);
				}
			}

			//console.log('urlForHash: ', urlForHash);
		}
	}

	// Support for full screen pages. The frontend editor has height=0 and it uses the window height
	if (vgfa_data.fullscreen_pages_keywords) {
		var isFullScreen = false;
		vgfa_data.fullscreen_pages_keywords.forEach(function (keyword) {
			if (currentPage.indexOf(keyword) > -1) {
				isFullScreen = true;
			}
		});

		// Maybe disable the full screen by keyword
		if (vgfa_data.disable_fullscreen_pages_keywords) {
			vgfa_data.disable_fullscreen_pages_keywords.forEach(function (keyword) {
				if (keyword && currentPage.indexOf(keyword) > -1) {
					isFullScreen = false;
				}
			});
		}

		// Fluent forms support. Show the admin content as full screen when the fluent forms editor is opened as full screen
		if (adminBodyClasses && adminBodyClasses.indexOf('ff_full_screen') > -1) {
			isFullScreen = true;
		}

		if (isFullScreen) {
			$iframe.addClass('vgfa-full-screen');
			$iframeWrapper.addClass('vgfa-wrapper-full-screen');
			jQuery('body').addClass('vgfa-full-screen-activated');

// If this is the brizy editor opened inside the iframe, automatically set the window height 
// because if we use dynamic height, the brizy UI will appear below the viewport
			if (currentPage.indexOf('&brizy-edit')) {
				iframeHeight = jQuery(window).height();
			}
		} else {
			$iframe.removeClass('vgfa-full-screen');
			$iframeWrapper.removeClass('vgfa-wrapper-full-screen');
			jQuery('body').removeClass('vgfa-full-screen-activated');
		}
	}

	if (window.location.href.indexOf('wpfa_debug_height=1') > -1) {
		$iframeWrapper.parent().find('.wpfa-report-height').remove();
		$iframeWrapper.before('<span class="wpfa-report-height">' + iframeHeight + '</span>');
	}

	// Auto scroll towards the visible popups
	if (visiblePopupsStartsAt.length) {
		visiblePopupsStartsAt.forEach(function (popup) {
			var topPosition = popup.topPosition;
			var elementStart = iframeStartsAt + topPosition - 20;
			var elementEnd = elementStart + popup.height;
			if (jQuery(window).scrollTop() !== elementStart && ((jQuery(window).scrollTop() > elementStart) || (jQuery(window).scrollTop() + jQuery(window).height()) < elementStart)) {
				jQuery('html,body').scrollTop(elementStart);
			}
			if (iframeHeight < popup.heightRequiredByPopup) {
				iframeHeight = popup.heightRequiredByPopup;
			}
		});
	}


	// Set iframe height based on the content height
	if (!$iframe.data('lastHeight') || $iframe.data('lastHeight') !== iframeHeight) {
		$iframe.height(iframeHeight);
		$iframeWrapper.height(iframeHeight);
		$iframe.data('lastHeight', iframeHeight);
		// Stop loading when we receive reported height because
		// sometimes when you use the browser history arrows, the previous page
		// loads instantly and the event that makes the page stop loading
		// doesn't run
		vgfaStopLoading({
			iframeId: iframeId,
			data: {}
		});
	}
}

function vgfaNavigateTo(e) {
	window.location.href = e.data;
}

function vgfaInitializeShowOwnPosts(e) {
	var postType = e.data;
	if (!jQuery('.vg-frontend-admin-quick-settings .show-own-posts').length || !postType) {
		jQuery('.vg-frontend-admin-quick-settings .show-own-posts').hide();
		return true;
	}

	var $showOwnPosts = jQuery('.vg-frontend-admin-quick-settings .show-own-posts input');
	$showOwnPosts.each(function () {
		jQuery(this).attr('name', jQuery(this).attr('name').replace('{post_type}', postType));
	});
}

function vgfaInitializeColumnsManager(e) {
	var vgfaTableColumns = e.data.vgfaTableColumns;
	var vgfaTableColumnsPostType = e.data.vgfaTableColumnsPostType;
	var $columnsManager = jQuery('.vg-frontend-admin-quick-settings .columns-manager');
	if (!$columnsManager.length || !vgfaTableColumns) {
		$columnsManager.hide();
		return true;
	}

	$columnsManager.show();
	$columnsManager.find('.columns-wrapper').empty();
	jQuery.each(vgfaTableColumns, function (columnKey, columnLabel) {
		var $column = $columnsManager.find('.column-template').first().clone();
		$column.find('span').text(columnLabel);
		$column.find('input').attr('value', columnKey);
		$column.find('input').attr('name', $column.find('input').attr('name').replace('{post_type}', vgfaTableColumnsPostType));
		if (typeof window.vgfaDisabledColumns !== 'undefined' && typeof window.vgfaDisabledColumns[vgfaTableColumnsPostType] !== 'undefined' && window.vgfaDisabledColumns[vgfaTableColumnsPostType].indexOf(columnKey) > -1) {
			$column.find('input').attr('checked', 'checked');
		}

		$columnsManager.find('.columns-wrapper').append($column);
	});
}
function vgfaHideElement(e) {
	var selector = e.data;
	var existingSelectors = jQuery('.hide-elements-input').val();
	if (existingSelectors) {
		selector = ',' + selector;
	}
	jQuery('.hide-elements-input').val(existingSelectors + selector);
	window.isHideElementOutlineActive = false;
	jQuery('.vg-frontend-admin-quick-settings .hide-elements-trigger').removeClass('wpfa-hide-elements-active');
}
function vgfaSetRequiredCapability(e) {
	jQuery('.required-capability-target').append(e.data);
}

function vgfaStartLoading(e) {
	var $parent = jQuery('#' + e.iframeId).parents('.vgca-iframe-wrapper');
	$parent.find('.vgca-loading-indicator').show();
	$parent.find('.vgca-loading-indicator').each(function () {
		var $loadingIndicator = jQuery(this);
		if (!$loadingIndicator.hasClass('wpfa-centered')) {
			$loadingIndicator.addClass('wpfa-centered');
			$loadingIndicator.css('left', ($loadingIndicator.parent().width() - $loadingIndicator.width()) / 2);
		}
	});
	$parent.addClass('vgfa-is-loading');
}

function vgfaStopLoading(e) {
	var $parent = jQuery('#' + e.iframeId).parents('.vgca-iframe-wrapper');
	var newHeight = e.data;
	if (newHeight) {
		$parent.find('iframe').height(newHeight);
		$parent.height(newHeight);
	}

	$parent.find('.vgca-loading-indicator').hide();
	$parent.removeClass('vgfa-is-loading');
	$parent.find('.wpfa-loading-too-long-message').hide();

	// FIX. Some tinymce plugins expect the top parent window to have the tinymce object, so we just forward it
	setTimeout(function () {
		if (typeof window.tinymce === 'undefined') {
			var $visibleAdminPage = vgfaGetVisibleAdminPage();
			var firstIframe = $visibleAdminPage.get(0);
			if (firstIframe) {
				try {
					window.tinymce = firstIframe.contentWindow.tinymce;
				} catch (e) {
					return true;
				}
			}
		}
	}, 1200);
}

// WC Cancel Order Pro compatibility
jQuery(document).ready(function () {
	if (jQuery('.vgca-iframe-wrapper').length && typeof jQuery.fancybox !== 'undefined' && !jQuery.fancybox.getInstance()) {
		jQuery.fancybox = {
			getInstance: function () {
				return {
					update: function () {
						var $visibleAdminPage = vgfaGetVisibleAdminPage();
						var firstIframe = $visibleAdminPage.get(0);
						if (firstIframe) {
							try {
								firstIframe.contentWindow.jQuery.fancybox.getInstance().update();
							} catch (e) {
								return true;
							}
						}
					}
				};
			}
		};
	}
});

function vgfaSaveTextChange(e) {
	if (!e.data.before || !e.data.after || e.data.before === e.data.after) {
		return true;
	}
	var before = jQuery.trim(e.data.before);
	var after = jQuery.trim(e.data.after);
	var url = jQuery.trim(e.data.url);
	var existingTextEdits = jQuery('.text-changes-input').val();
	//console.log('existingTextEdits: ', existingTextEdits);
	if (existingTextEdits) {
		var textEdits = JSON.parse(existingTextEdits);
		if (!textEdits) {
			textEdits = {};
		}
	} else {
		var textEdits = {};
	}

	if (typeof textEdits[url] === 'undefined') {
		textEdits[url] = {};
	}

	textEdits[url][before] = after;
	//console.log('textEdits: ', textEdits);
	jQuery('.text-changes-input').val(JSON.stringify(textEdits));
}

function vgfaGetVisibleAdminPage() {

	var $visibleBackendPage = jQuery('.vgca-iframe-wrapper iframe').filter(function () {
		return jQuery(this).is(':visible');
	});
	return $visibleBackendPage;
}

jQuery(window).on('load', function () {
	vgfaInitIframes();
	// Add support for shortcodes that loaded via ajax or were added to the 
	// DOM with lazy loading, common with popups that load content when the popup is opened
	setInterval(function () {
		vgfaInitIframes();
	}, 1000);

	// Fix. TinyMCE plugins call the send_to_editor on the parent window, 
	// which by mistake is our frontend page. We forward the call 
	// to the function inside the iframe (backend)
	window.send_to_editor = function (arg) {
		var $visibleAdminPage = vgfaGetVisibleAdminPage();
		if ($visibleAdminPage.length) {
			$visibleAdminPage.get().forEach(function (iframe) {
				iframe.contentWindow.send_to_editor(arg);
			});
		}
	}

	// Detect if wpfa-full-screen-bar exists
	if (jQuery('.wpfa-full-screen-bar').length) {
		jQuery('body').addClass('wpfa-has-full-screen-bar');
	}
});
jQuery(document).ready(function () {
	var $quickSettings = jQuery('.vg-frontend-admin-quick-settings');
	if (!$quickSettings.length) {
		return true;
	}

	var $toggle = jQuery('.vg-frontend-admin-quick-settings-toggle');
	jQuery('body').append($quickSettings);
	jQuery('body').append($toggle);
	jQuery('body').addClass('vgfa-has-quick-settings');
	$quickSettings.find('.common-errors').hide();
	$quickSettings.find('.expand-common-errors').click(function (e) {
		e.preventDefault();
		$quickSettings.find('.common-errors').slideToggle();
	});
	var $saveButton = $quickSettings.find('button');
	var iframeUrls = [];
	jQuery('.vgca-iframe-wrapper iframe').each(function () {
		iframeUrls.push(jQuery(this).data('wpfa'));
	});
	$quickSettings.find('input[name="wpfa_iframe_urls"]').val(iframeUrls.join(','));
	$quickSettings.submit(function (e) {
		e.preventDefault();
		$saveButton.text($saveButton.data('saving-text'));
		jQuery.post(vgfa_data.wp_ajax_url, $quickSettings.serialize(), function (response) {
			if (response.success) {
				alert(response.data.message);
				window.location.href = response.data.new_url;
			}
		});
		return false;
	});
// Remove elements tool
	var $hideElements = $quickSettings.find('.hide-elements-trigger');
	var $hideElementsInput = $quickSettings.find('.hide-elements-input');
	$quickSettings.find('.show-elements-trigger').click(function (e) {
		e.preventDefault();
		wpFrontendAdminFrontend.iframeFunction('wpfaShowHIddenElements', $hideElementsInput.val());
		$hideElementsInput.val('');
	});
	$hideElements.click(function (e) {
		e.preventDefault();
		if (window.isHideElementOutlineActive) {
			wpFrontendAdminFrontend.iframeFunction('vgfaStopHideElementOutline');
			window.isHideElementOutlineActive = false;
			$hideElements.removeClass('wpfa-hide-elements-active');
			$hideElements.blur();
		} else {
			wpFrontendAdminFrontend.iframeFunction('vgfaStartHideElementOutline');
			window.isHideElementOutlineActive = true;
			$hideElements.addClass('wpfa-hide-elements-active');
		}
	});
// Edit texts tool
	var $startEditingText = $quickSettings.find('.edit-text-trigger');
	var $stopEditingText = $quickSettings.find('.stop-edit-text-trigger');
	var $revertTextChangesInput = $quickSettings.find('.revert-all-text-edits-trigger');
	var $textChangesInput = $quickSettings.find('.text-changes-input');
	$revertTextChangesInput.click(function (e) {
		e.preventDefault();
		$textChangesInput.val('');
		jQuery('.vg-frontend-admin-save-button').click();
	});
	$startEditingText.click(function (e) {
		e.preventDefault();
		var $visibleBackendPage = vgfaGetVisibleAdminPage();
		if (!$visibleBackendPage.length) {
			return true;
		}

		wpFrontendAdminFrontend.iframeFunction('vgfaStartTextEdit');
		$startEditingText.hide();
		$stopEditingText.show();
		// Use by the admin page window, when we navigate from one admin page to another
		// we check this flag in the parent window to continue in editing mode
		window.vgfaIsEditingText = true;
	});
	$stopEditingText.click(function (e) {
		e.preventDefault();
		var $visibleBackendPage = vgfaGetVisibleAdminPage();
		if (!$visibleBackendPage.length) {
			return true;
		}

		wpFrontendAdminFrontend.iframeFunction('vgfaStopTextEdit');
		$stopEditingText.hide();
		$startEditingText.show();
		window.vgfaIsEditingText = false;
	});
	jQuery('body').addClass('vg-frontend-admin-visible-quick-settings');
	$toggle.click(function (e) {
		e.preventDefault();
		if ($quickSettings.is(':visible')) {
			$quickSettings.hide();
			jQuery('body').removeClass('vg-frontend-admin-visible-quick-settings');
			$toggle.text('+');
			$toggle.css('left', '0');
			var $visibleBackendPage = vgfaGetVisibleAdminPage();
			if ($visibleBackendPage.length) {
				// Force to resize the iframe
				$visibleBackendPage.data('lastPage', 'xx');
				$visibleBackendPage.data('lastHeight', '');
			}
		} else {
			$quickSettings.show();
			jQuery('body').addClass('vg-frontend-admin-visible-quick-settings');
			$toggle.text('x');
			$toggle.css('left', '');
			var $visibleBackendPage = vgfaGetVisibleAdminPage();
			if ($visibleBackendPage.length) {
				// Force to resize the iframe
				$visibleBackendPage.data('lastPage', 'xx');
				$visibleBackendPage.data('lastHeight', '');
			}
		}
	});
});
/**
 * Execute function by string name
 */
function vgseExecuteFunctionByName(functionName, context /*, args */) {
	var functionName = jQuery.trim(functionName);
	var args = [].slice.call(arguments).splice(2);
	var namespaces = functionName.split(".");
	var func = namespaces.pop();
	for (var i = 0; i < namespaces.length; i++) {
		context = context[namespaces[i]];
	}
	if (typeof context[func] !== 'undefined') {
		return context[func].apply(context, args);
	}
}

jQuery(window).on("message", function (e) {
	var rawData = e.originalEvent.data; // Should work.

	if (!rawData || typeof rawData !== 'string' || typeof rawData.indexOf === 'undefined' || rawData.indexOf('{') < 0) {
		return true;
	}
	try {
		var data = JSON.parse(rawData);
	} catch (e) {
		return true;
	}
	if (!data.iframeId) {
		var $visibleAdminPage = vgfaGetVisibleAdminPage();
		data.iframeId = $visibleAdminPage.first().attr('id');
	}
	vgseExecuteFunctionByName(data.functionName, window, {'data': data.arguments, 'iframeId': data.iframeId});
//	console.log('Data received in the frontend: ', data);
});

// Remove class to center loading indicator again
jQuery(window).on('resize', function () {
	jQuery('.vgca-loading-indicator.wpfa-centered').removeClass('wpfa-centered');
});

if (!vgfa_data.disable_stateful_navigation) {
	jQuery(window).on('hashchange', function (e) {
		// If flag window.wpfaIgnoreHashChange is set, it means we are updating the hash only
		// so we remove the flag and bail
		if (window.wpfaIgnoreHashChange) {
			window.wpfaIgnoreHashChange = false;
			return true;
		}

		if (jQuery('.vgca-iframe-wrapper').length === 1) {
			var $iframe = jQuery('.vgca-iframe-wrapper iframe').first();
			if (window.location.hash && window.location.hash.indexOf('wpfa:') > -1) {
				var decodedHash = atob(window.location.hash.replace('#wpfa:', ''));
				// Ignore hashes that start with http because the elementor editor is a frontend page and it 
				// starts with http and we don't want to load it when using the elementor editor
				if (decodedHash.indexOf('http') < 0) {
					var urlFromHash = vgfa_data.wpadmin_base_url + atob(window.location.hash.replace('#wpfa:', ''));
				}
			} else {
				var urlFromHash = $iframe.data('wpfa');
			}
			// Don't reload the iframe when the wp editor adds to the URL "&wp-post-new-reload=true" because it's an aesthetic change and not a real reload
			var urlFromHashWithoutHash = urlFromHash.split('#')[0];
			var iframeUrl = $iframe.attr('src').split('#')[0];
			var iframeCurrentUrl = $iframe.data('lastPage').split('#')[0];
			if (urlFromHash && urlFromHash.indexOf('&wp-post-new-reload=true') < 0 && urlFromHashWithoutHash !== iframeUrl && urlFromHashWithoutHash !== iframeCurrentUrl) {
				$iframe.attr('src', urlFromHash);
				//console.log('Hash changed to ', urlFromHash);
				//console.log('Hash changed, current ', currentPage);
			}
		}
		//console.log('hash changed');
	});
}