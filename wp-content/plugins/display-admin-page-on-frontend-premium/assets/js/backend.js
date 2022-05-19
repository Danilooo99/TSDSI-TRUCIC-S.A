/**
 * A (possibly faster) way to get the current timestamp as an integer.
 * @returns int
 */
function _now() {
	var out = Date.now() || new Date().getTime();
	return out;
}
/**
 * Returns a function, that, when invoked, will only be triggered at most once during a given window of time. Normally, the throttled function will run as much as it can, without ever going more than once per wait duration; but if you’d like to disable the execution on the leading edge, pass {leading: false}. To disable execution on the trailing edge, ditto.
 * @param func
 * @param int wait
 * @param obj options
 * @returns func
 */
function _throttle(func, wait, options) {

	if (!wait) {
		wait = 300;
	}
	var context, args, result;
	var timeout = null;
	var previous = 0;
	if (!options)
		options = {};
	var later = function () {
		previous = options.leading === false ? 0 : _now();
		timeout = null;
		result = func.apply(context, args);
		if (!timeout)
			context = args = null;
	};
	return function () {
		var now = _now();
		if (!previous && options.leading === false)
			previous = now;
		var remaining = wait - (now - previous);
		context = this;
		args = arguments;
		if (remaining <= 0 || remaining > wait) {
			if (timeout) {
				clearTimeout(timeout);
				timeout = null;
			}
			previous = now;
			result = func.apply(context, args);
			if (!timeout)
				context = args = null;
		} else if (!timeout && options.trailing !== false) {
			timeout = setTimeout(later, remaining);
		}
		return result;
	};
}
;

var wpFrontendAdminBackend = {
	addViewOnFrontendToolUrl: function () {
		var $viewInFrontend = jQuery('#wp-admin-bar-vgca-direct-frontend-link a');
		if ($viewInFrontend.length) {
			var slug = encodeURIComponent(window.location.href.replace(window.vgfaWpAdminBase, ''));
			var $title = jQuery('h1').first().clone();

			// Some plugins have weird tags inside the main <h1>, like gravityforms
			$title.find('script, style, iframe, form, input').remove();

			$viewInFrontend.attr('href', $viewInFrontend.attr('href') + '&vgca_slug=' + slug + '&title=' + $title.text());
		}
	},
	renderCustomCss: function () {
		var vgfaCustomCssFinal = window.vgfaCustomCss;
		if (typeof vgfaCustomCssFinal !== 'undefined') {
			jQuery('head').append('<style id="vgca-custom-css">' + vgfaCustomCssFinal + '</style>');
		}
	},
	openFrontendLinksInMainWindow: function () {
		jQuery('body').on('click', 'a', function (e) {
			var $a = jQuery(this);
			var url = jQuery(this).attr('href');
			var target = jQuery(this).attr('target') || '';
			target = target.toLowerCase();

			// Bail if it's not a valid url
			if (typeof url !== 'string' || url.indexOf('blob:') === 0 || url.indexOf('http') < 0 || url.indexOf('wpfaNoInterfere') > -1 || !window.wpfaIframeId || jQuery(this).parents('.instant-img-container').length || url.indexOf('preview=true') > -1 || target === '_blank') {
				return true;
			}
			// Don't capture the click in the menu editor > item > dropdown arrow link because it's handled with JS by WP core
			if ($a.parents('#update-nav-menu').length && ($a.hasClass('item-move-down') || $a.hasClass('item-move-up') || $a.hasClass('item-edit'))) {
				return true;
			}
			// Don't capture the click on the wp ultimo > admin.php?page=wu-new-template > template filters - because it's handled with JS by WU
			if ($a.parents('.wp-filter-template').length && jQuery('body').hasClass('admin_page_wu-new-template')) {
				return true;
			}
			// WP Ultimo v2 uses wu-ajax in some links that might be mistaken as front end links
			if (url.indexOf('wu-ajax=') > -1) {
				return true;
			}

			var openInMainWindow = true;
			var keywordsToOpenInsideIframe = vgfa_backend_data.links_open_inside_iframe;

			keywordsToOpenInsideIframe.forEach(function (keyword) {
				if (url.indexOf(keyword) > -1) {
					openInMainWindow = false;
				}
			});
			if (openInMainWindow) {
				wpFrontendAdminBackend.parentFunction('vgfaNavigateTo', url);
				e.preventDefault();
				return false;
			}
		});
	},
	getUrlParam: function (paramName, url) {
		if (url && url.indexOf('?') < 0) {
			return false;
		}
		var rawUrlParams = window.location.search;
		if (url) {
			var rawUrlParams = '?' + url.split('?')[1];
		}
		var urlParams = new URLSearchParams(rawUrlParams);
		return urlParams.get(paramName);
	},
	parentFunction: function (functionName, arguments) {

		// Fix for elementor because it loads the editor without our scripts and the admin page with our script is inside an iframe
		if (window.location.href.indexOf('elementor-preview') > -1) {
			var iframeParent = window.parent.parent;
		} else if (window.location.href.indexOf('brizy-edit-iframe') > -1) {
			var iframeParent = window.parent.parent;
		} else {
			var iframeParent = window.parent;
		}
		if (!window.wpfaIframeId && window.location.href.indexOf('wpfa_id=') > -1 && typeof URLSearchParams === 'function') {
			window.wpfaIframeId = wpFrontendAdminBackend.getUrlParam('wpfa_id');
		}

		iframeParent.postMessage(JSON.stringify({
			'functionName': functionName,
			'arguments': arguments,
			'iframeId': window.wpfaIframeId
		}), '*');
	},

	getElementsWithTextEdit: function () {
		return jQuery('h1,h2,h3,h4,h5,h6,span,a,button,p,div,label, td, th, abbr, blockquote').filter(function () {
			return jQuery(this).children().length < 1;
		});
	},

	getElementCSSSelector: function (el, withClass) {
		var names = [];
		while (el.parentNode) {
			if (el.id) {
				names.unshift('#' + el.id);
				break;
			} else {
				if (el == el.ownerDocument.documentElement) {
					names.unshift(el.tagName.toLowerCase());
				} else {
					var tagName = el.tagName.toLowerCase();
					if (withClass && el.className) {
						tagName += "." + el.className.replace(/\s+/g, '.');
					}

					for (var c = 1, e = el; e.previousElementSibling; e = e.previousElementSibling, c++)
						;
					names.unshift(tagName + ":nth-child(" + c + ")");
				}
				el = el.parentNode;
			}
		}
		var out = names.join(" > ").replace('html >', '');
		out = out.replace(/wpbody-content > div\.wrap\:nth-child\(\d+\)/g, 'wpbody-content > div.wrap');
		if (out.indexOf('.subsubsub') > -1) {
			var parts = out.split('.subsubsub');
			out = parts[0] + '.subsubsub';
		}

// FluentCRM uses very specific classes, so we don't need the :nth-child much and it causes mismatches
		if (out.indexOf('div.fluentcrm_app_wrapper:nth-child') > -1) {
			out = out.replace(/\:nth-child\(\d+\)/g, '');
			out = out.replace('.current.fluentcrm_active', '');
		}

		if (vgfa_backend_data.enable_loose_css_selectors) {
			out = out.replace(/\:nth-child\(\d+\)/g, '');
		}
		return out;
	},
	listenForTextChanges: function () {

		// Listen for text changes
		jQuery('body').on('focus', '[contenteditable]', function () {
			if (window.wpfaIsEditingOneText) {
				return true;
			}
			const $this = jQuery(this);
			$this.data('before', $this.html());
			window.wpfaIsEditingOneText = true;
		}).on('blur', '[contenteditable]', function () {
			const $this = jQuery(this);
			if ($this.data('before') !== $this.html()) {
				$this.data('after', $this.html());
				$this.trigger('change');
			}
			window.wpfaIsEditingOneText = false;
		}).on('mouseover', '[contenteditable]', function () {
			if (!window.wpfaIsEditingOneText) {
				jQuery(this).focus();
			}
		});
	},
	initializeTextChangesTracking: function () {
		wpFrontendAdminBackend.listenForTextChanges();

		wpFrontendAdminBackend.getElementsWithTextEdit().on('change', _throttle(function (e) {
			var $element = jQuery(this);
			wpFrontendAdminBackend.parentFunction('vgfaSaveTextChange', {'before': $element.data('before'), 'after': $element.data('after'), 'url': window.location.href});
		}, 4000, {
			leading: true,
			trailing: true
		}));
	},
	addUrlParam: function (url, paramKey, paramValue) {
		if (url.indexOf(paramKey + '=' + paramValue) > -1) {
			return url;
		}
		// Add a parameter to frontend URLs to indicate where they came from
		var urlParts = url.split('#');
		var newUrl = urlParts[0];
		newUrl += url.indexOf('?') < 0 ? '?' : '&';
		newUrl += paramKey + '=' + paramValue;
		if (typeof urlParts[1] !== 'undefined') {
			newUrl += '#' + urlParts[1];
		}
		return newUrl;

	},
	addRefererToUrl: function (url, rawParentUrl) {
		if (url.indexOf('vgfa_referrer') > -1) {
			return url;
		}
		try {
			// Add a parameter to frontend URLs to indicate where they came from
			var urlParts = url.split('#');
			var newUrl = urlParts[0];
			newUrl += url.indexOf('?') < 0 ? '?' : '&';
			newUrl += 'vgfa_referrer=' + btoa(rawParentUrl);
			if (typeof urlParts[1] !== 'undefined') {
				newUrl += '#' + urlParts[1];
			}
			return newUrl;
		} catch (e) {
			return false;
		}
	},
	prepareStatefulLinks: function (allowRepeatedRuns) {
		if (vgfa_backend_data.disable_stateful_navigation) {
			return false;
		}
		if (jQuery('body').data('wpfaStatefulLinksAdded') && !allowRepeatedRuns) {
			return false;
		}
		var parentUrl = this.getParentData('url');
		var rawParentUrl = this.getParentData('url');
		if (!parentUrl) {
			return false;
		}
		parentUrl = parentUrl.replace(/#.+$/, '');
		var sourceId = this.getParentData('sourceId');
		var wpAdminParts = vgfaWpAdminBase.split('/').filter(n => n);
		var wpAdminDirectory = '/' + wpAdminParts[ wpAdminParts.length - 1 ] + '/';
		jQuery('body a').each(function () {
			var $a = jQuery(this);
			var url = $a.attr('href');

			// Compatibility with the WooCommerce Memberships plugin. The "add new" button in the members page opens a popup and should not be handled as a regular link
			if ($a.hasClass('page-title-action') && url.indexOf('post-new.php?post_type=wc_user_membership') > 0) {
				return true;
			}

			// Bail if it's not a valid url (empty, only hash, has wpfaNoInterfere, or starts with / )
			if (!url || typeof url !== 'string' || url.indexOf('#') === 0 || url.indexOf('wpfaNoInterfere') > -1 || (url.indexOf('/') === 0 && url.indexOf(wpAdminDirectory) !== 0) || url.indexOf('mailto:') === 0 || url.indexOf('tel:') === 0 || url.indexOf('javascript:') === 0 || url.indexOf('admin-ajax.php') > -1 || url.indexOf('#wpfa:') > -1) {
				return true;
			}
			// Exclude media library links that open the modal
			if ($a.hasClass('thickbox')) {
				return true;
			}
			// Exclude elementor link because it breaks the "edit with elementor" button in the regular editor
			if ($a.attr('id') === 'elementor-go-to-edit-page-link') {
				return true;
			}
			// Don't capture the click in the menu editor > item > dropdown arrow link because it's handled with JS by WP core
			if ($a.parents('#update-nav-menu').length && ($a.hasClass('item-move-down') || $a.hasClass('item-move-up') || $a.hasClass('item-edit'))) {
				return true;
			}


			// Bail if it's a frontend URL
			if (url.indexOf('http') === 0 && url.indexOf(vgfaWpAdminBase) < 0) {
				try {
					if (url.indexOf('?') > -1) {
						url = wpFrontendAdminBackend.addRefererToUrl(url, rawParentUrl);
					}
					if (vgfa_backend_data.wu_sso_enabled) {
						var randomNumbers = Math.floor(1000 + Math.random() * 9000);
						url = wpFrontendAdminBackend.addUrlParam(url, 'wpfacache', randomNumbers);
					}
					$a.attr('href', url);
				} catch (e) {
					return true;
				}
				return true;
			}

			var urlsThatRequireReferer = vgfa_backend_data.stateful_urls_that_require_referer.split(',');
			urlsThatRequireReferer.forEach(function (urlFragment) {
				if (urlFragment && url.indexOf(urlFragment) > -1) {
					url = wpFrontendAdminBackend.addRefererToUrl(url, rawParentUrl);
				}
			});


			var urlForHash = url.replace(vgfaWpAdminBase, '').replace(/#.+$/, '');
			// We use try because btoa might throw errors if strings use non-latin characters
			try {
				var statefulUrl = parentUrl + '#wpfa:' + btoa(urlForHash);
			} catch (e) {
				return true;
			}
			$a.data('wpfa-stateful-url', statefulUrl);
			$a.data('wpfa-original-href', url);
			$a.attr('href', statefulUrl);

			// Don't capture the click on the WC settings > payment gateways > status switch because it's handled with JS by WC
			if ($a.hasClass('wc-payment-gateway-method-toggle-enabled')) {
				return true;
			}

			// Don't capture the click on the wp ultimo > admin.php?page=wu-new-template > template filters - because it's handled with JS by WU
			if ($a.parents('.wp-filter-template').length && jQuery('body').hasClass('admin_page_wu-new-template')) {
				return true;
			}

			var linkTarget = $a.attr('target') ? $a.attr('target').toLowerCase() : '';
			if (linkTarget !== '_blank') {
				$a.click(function (e) {
					var originalUrl = jQuery(this).data('wpfa-original-href');
					var currentUrl = jQuery(this).attr('href');
					if (currentUrl.indexOf('#wpfa:') > -1 && originalUrl) {
						e.preventDefault();
						e.stopPropagation();
						originalUrl = wpFrontendAdminBackend.addUrlParam(originalUrl, 'vgfa_internal', 1);
						window.location.href = wpFrontendAdminBackend.addUrlParam(originalUrl, 'vgfa_source', sourceId);
						return false;
					}
				});
			}
		});
		jQuery('body').data('wpfaStatefulLinksAdded', 1);
	},
	getParentData: function (key) {
		var data = jQuery('body').data('parentData');
		var out = null;
		if (typeof data === 'object' && data[key]) {
			out = data[key];
		}
		return out;
	},
	reportPageDataToParent: function () {
		var popupSelectors = vgfa_backend_data.extra_popup_selectors;
		// WP Amelia. The popups are popups only for desktop and mobile version uses full screen forms
//		if (jQuery(window).width() > 782) {
//			popupSelectors += ', .am-side-dialog .el-dialog';
//		}
		var visiblePopupsStartsAt = [];
		if (popupSelectors) {
			var $visiblePopups = jQuery(popupSelectors).filter(function () {
				return jQuery(this).is(':visible');
			});

			$visiblePopups.each(function () {
				var $popup = jQuery(this);
				var heightRequiredByPopup = $popup.height() + 100;

				visiblePopupsStartsAt.push({
					height: $popup.height(),
					topPosition: $popup.offset().top,
					heightRequiredByPopup: heightRequiredByPopup
				});
				// Make sure the admin page is as tall as the popup
				// If the page is smaller, the popup would look cut off
				if (jQuery('body').height() < heightRequiredByPopup) {
					jQuery('body').height(heightRequiredByPopup);
				}
			});
		}

		var $body = jQuery('body');
		var height = $body.height();
		var minimumHeight = wpFrontendAdminBackend.getParentData('minimumHeight') || parseInt(vgfa_backend_data.minimum_content_height);
		if (height < minimumHeight && $body.is(':visible')) {
			height = minimumHeight;
			$body.height(minimumHeight);
		}
		wpFrontendAdminBackend.parentFunction('vgfaUpdateIframeData', {
			'url': window.location.href,
			'height': height,
			'gutenbergEditorFound': jQuery('.block-editor__container').length,
			'visiblePopupsStartsAt': visiblePopupsStartsAt,
			'bodyClasses': jQuery('body').attr('class')
		});
		jQuery("body").one("wubox:load", function () {
			setTimeout(function () {
				var $wuPopup = jQuery('#WUB_window');
				wpFrontendAdminBackend.parentFunction('vgfaUpdateIframeData', {
					'url': window.location.href,
					'height': height,
					'gutenbergEditorFound': jQuery('.block-editor__container').length,
					'visiblePopupsStartsAt': [{
							height: $wuPopup.height(),
							topPosition: $wuPopup.offset().top,
							heightRequiredByPopup: $wuPopup.height() + 100
						}],
					'bodyClasses': jQuery('body').attr('class')
				});
			}, 500);
		});
	}
};

// Add the title to the "view in frontend" request
jQuery(document).ready(function () {
	wpFrontendAdminBackend.addViewOnFrontendToolUrl();
});
if (window.parent != window) {
	wpFrontendAdminBackend.renderCustomCss();


	// Add more classes to some elements, so we can select them more accurately for the "hide elements" tool
	// We must do this very early because the css depends on these selectors and we want to avoid showing the elements for a second while the page loads
	if (jQuery('body').hasClass('woocommerce_page_wc-settings') && window.location.href.indexOf('&tab=') > -1) {
		var tabName = wpFrontendAdminBackend.getUrlParam('tab');
		jQuery('.woocommerce .subsubsub').addClass('vgfa-tab-' + tabName);
		jQuery('#mainform').children().addClass('vgfa-tab-' + tabName);
	}

	jQuery(document).ready(function () {
		// Insert the DomOutliner JS in the DOM in case other plugins have removed the third-party scripts from the page
		if (!jQuery('#vg-frontend-admin-outline-js').length) {
			jQuery('body').append("<script src='" + vgfa_backend_data.domoutline_js_file_url + "' id='vg-frontend-admin-outline-js'></script>");
		}

		// Add page identifier to the html tag
		if (jQuery('body').attr('class')) {
			var classes = jQuery('body').attr('class').split(' ');
			var pageClass = '';
			for (var i = 0; i < classes.length; i++) {
				var matches = /^vgfa-page\-(.+)/.exec(classes[i]);
				if (matches != null) {
					var pageClass = matches[1];
				}
			}
			if (pageClass) {
				jQuery('html').addClass('vgfa-page-' + pageClass);
			}
		}
		// The prepareStatefulLinks runs the first time when the iframe receives the frontend settings initially,
		// But we must run it a couple of times later to make sure links inserted by ajax calls are processed too
		setTimeout(function () {
			wpFrontendAdminBackend.prepareStatefulLinks(true);
		}, 400);
		setTimeout(function () {
			wpFrontendAdminBackend.prepareStatefulLinks(true);
		}, 1500);
		setTimeout(function () {
			wpFrontendAdminBackend.prepareStatefulLinks(true);
		}, 2500);
		setTimeout(function () {
			wpFrontendAdminBackend.prepareStatefulLinks(true);
		}, 4000);

		// Support for Folders by Premio. Sometimes the body doesn't have the class that we need to know when the folders feature is activated
		if (jQuery('.premio-folder-count').length && !jQuery('body').hasClass('has-premio-box')) {
			jQuery('body').addClass('has-premio-box');
		}


		// If URL is not for wp-admin page, open outside the iframe
		wpFrontendAdminBackend.openFrontendLinksInMainWindow();

		if (typeof vgfaTableColumnsPostType !== 'undefined') {
			// Show own posts
			wpFrontendAdminBackend.parentFunction('vgfaInitializeShowOwnPosts', vgfaTableColumnsPostType);

			// Table columns manager
			wpFrontendAdminBackend.parentFunction('vgfaInitializeColumnsManager', {'vgfaTableColumns': vgfaTableColumns, 'vgfaTableColumnsPostType': vgfaTableColumnsPostType});
		}

		if (vgfa_backend_data.disable_all_admin_notices) {
			jQuery('.vgca-only-admin-content body.wp-admin .update-nag, .vgca-only-admin-content body.wp-admin .updated, .vgca-only-admin-content body.wp-admin .notice.error, .vgca-only-admin-content body.wp-admin .is-dismissible, .vgca-only-admin-content body.wp-admin .notice').remove();
		}
	});
	jQuery(window).on('unload', function () {
		wpFrontendAdminBackend.parentFunction('vgfaStartLoading');
		return null;
	});
	var initialHeight = jQuery('body').height() || 600;
	wpFrontendAdminBackend.parentFunction('vgfaStopLoading', initialHeight);
	jQuery(window).on('load', function () {
		wpFrontendAdminBackend.parentFunction('vgfaStopLoading', jQuery('body').height());

		// Send the required roles of this page to the parent
		if (typeof vgfaRequiredRoles !== 'undefined') {
			wpFrontendAdminBackend.parentFunction('vgfaSetRequiredCapability', vgfaRequiredRoles);
		}
	});

	wpFrontendAdminBackend.reportPageDataToParent();
	setInterval(function () {
		wpFrontendAdminBackend.reportPageDataToParent();
	}, 1000);
}

function wpfaSetIframeState(e) {
	var args = e.data;
	window.wpfaIframeId = args.id;
	jQuery('body').data('parent-id', args.id);
	jQuery('body').data('parentData', args);

	if (args.isEditingText && !window.vgfaIsEditingText) {
		vgfaStartTextEdit();
	}

	// Allow to receive the admin CSS from the frontend page
	// In case the iframe contains a page from a different site
	if (args.adminCss && !jQuery('style.vgfa-inserted-from-frontend').length && (!jQuery('style.vgfa-admin-css').length || window.location.href.indexOf('/upload.php') > -1)) {
		jQuery('head').append(args.adminCss.replace('class="vgfa-admin-css"', 'class="vgfa-admin-css vgfa-inserted-from-frontend"'));
	}


	// Stateful links
	wpFrontendAdminBackend.prepareStatefulLinks();
	jQuery(document).trigger('wpFrontendAdmin/iframeStateUpdated');
}

function wpfaShowHIddenElements(e) {
	jQuery(e.data).each(function () {
		var displayValue = 'initial';
		if (jQuery(this).prop("tagName").match(/TH|TD/)) {
			displayValue = 'table-cell';
		} else if (jQuery(this).prop("tagName").match(/TR/)) {
			displayValue = 'table-row';
		}
		jQuery(this).attr('style', 'display: ' + displayValue + ' !important');
		jQuery(this).removeClass('wpfa-force-hide');
	});
}
function vgfaPreventClick(e) {
	e.preventDefault();
	return false;
}

function vgfaStopHideElementOutline() {
	if (window.vgfaHideElementOutline) {
		vgfaHideElementOutline.stop();
		jQuery('a[wpfa-href]').each(function () {
			var $a = jQuery(this);
			$a.attr('href', $a.attr('wpfa-href'));
			$a.attr('onclick', $a.attr('wpfa-onclick'));
			$a.attr('target', $a.attr('wpfa-target'));
		});
	}
}
function vgfaStartHideElementOutline() {
	jQuery('a').each(function () {
		var $a = jQuery(this);
		if ($a.attr('href') && $a.attr('href')[0] !== '#') {
			if (!$a.attr('wpfa-href')) {
				$a.attr('wpfa-href', $a.attr('href'));
			}
			$a.attr('wpfa-onclick', $a.attr('onclick') || '');
			$a.attr('wpfa-target', $a.attr('target') || '');
			$a.attr('href', 'javascript:void(0)');
			$a.attr('onclick', 'vgfaPreventClick(event)');
			$a.attr('target', '');
		}
	});
	window.vgfaHideElementOutline = DomOutline({onClick: function (element) {

			// Remove the random ID from the Gutenberg header toolbar temporarily, so we can get a good selector
			if (jQuery(element).hasClass('edit-post-header-toolbar')) {
				jQuery(element).data('wpfa-temp-id', jQuery(element).attr('id'));
				jQuery(element).attr('id', '');
			}
			var selector1 = wpFrontendAdminBackend.getElementCSSSelector(element);
			var selector2 = wpFrontendAdminBackend.getElementCSSSelector(element, true);

			//  When we hide one row action, apply it to all the rows in the posts table
			if (jQuery(element).parents('.row-actions').length && selector1.indexOf('#post-') === 0) {
				selector1 = selector1.replace(/^\#post-\d+ > /, 'tr > ');
				selector2 = selector2.replace(/^\#post-\d+ > /, 'tr > ').replace('.row-actions.visible', '.row-actions');
			}
			//  When we hide one row action, apply it to all the rows in the users table
			if (jQuery(element).parents('.row-actions').length && selector1.indexOf('#user-') === 0) {
				selector2 = selector2.replace(/^\#user-\d+ > /, 'tr > ').replace('.row-actions.visible', '.row-actions').replace(/:nth-child\(\d+\)/g, '');
				selector1 = selector2;
			}
			//  When we hide all the actions in one row, apply it to all the rows in the users table
			if (jQuery(element).hasClass('row-actions') && selector1.indexOf('#user-') === 0) {
				selector2 = selector2.replace(/^\#user-\d+ > /, 'tr > ').replace('.row-actions.visible', '.row-actions').replace(/:nth-child\(\d+\)/g, '');
				selector1 = selector2;
			}
			//  When we hide one row action, apply it to all the rows in the terms table
			if (jQuery(element).parents('.row-actions').length && selector1.indexOf('#tag-') === 0) {
				selector1 = selector1.replace(/^\#tag-\d+ > /, 'tr > ');
				selector2 = selector2.replace(/^\#tag-\d+ > /, 'tr > ').replace('.row-actions.visible', '.row-actions');
			}
			//  When we hide one row action, apply it to all the rows in the terms table
			if (selector2.indexOf('components-modal__screen-overlay') > -1) {
				selector2 = selector2.replace(/^.+components-modal__screen-overlay/, '.components-modal__screen-overlay');
				selector1 = selector2;
			}
			// When we hide the header of one column (or one element inside a header), 
			// automatically hide the column from all the rows in the table
			if (jQuery(element).parents('table').length && jQuery(element).parents('thead').length) {
				var $table = jQuery(element).parents('table');
				var $header = jQuery(element).prop("tagName") === 'TH' ? jQuery(element) : jQuery(element).parents('th');
				if ($header.length) {
					var headerIndex = $header.index() + 1;
					var possibleClassName = $header.attr('id') ? 'column-' + $header.attr('id') : false;
					if (possibleClassName && $header.hasClass(possibleClassName)) {
						selector1 = wpFrontendAdminBackend.getElementCSSSelector($table[0], true) + ' thead > tr > .' + possibleClassName + ', ' + wpFrontendAdminBackend.getElementCSSSelector($table[0], true) + ' tbody > tr > .' + possibleClassName + ', ' + wpFrontendAdminBackend.getElementCSSSelector($table[0], true) + ' tfoot > tr > .' + possibleClassName;
						selector2 = selector1;
					} else {
						selector1 = wpFrontendAdminBackend.getElementCSSSelector($table[0]) + ' thead > tr > :nth-child(' + headerIndex + '), ' + wpFrontendAdminBackend.getElementCSSSelector($table[0]) + ' tbody > tr > :nth-child(' + headerIndex + '), ' + wpFrontendAdminBackend.getElementCSSSelector($table[0]) + ' tfoot > tr > :nth-child(' + headerIndex + ')';
						selector2 = wpFrontendAdminBackend.getElementCSSSelector($table[0], true) + ' thead > tr > :nth-child(' + headerIndex + '), ' + wpFrontendAdminBackend.getElementCSSSelector($table[0], true) + ' tbody > tr > :nth-child(' + headerIndex + '), ' + wpFrontendAdminBackend.getElementCSSSelector($table[0], true) + ' tfoot > tr > :nth-child(' + headerIndex + ')';
					}
				}
			}
			// WPForms compatibility. They don't use <thead> nor <th> for some table headers
			// When we hide the header of one column (or one element inside a header), 
			// automatically hide the column from all the rows in the table
			if (jQuery(element).parents('table').length && jQuery(element).parents('.wpforms-dash-widget-forms-list-columns').length) {
				var $table = jQuery(element).parents('table');
				var $header = jQuery(element).prop("tagName") === 'TD' ? jQuery(element) : jQuery(element).parents('td');
				if ($header.length) {
					var headerIndex = $header.index() + 1;
					selector1 = wpFrontendAdminBackend.getElementCSSSelector($table[0]) + ' tr.wpforms-dash-widget-forms-list-columns > :nth-child(' + headerIndex + '), ' + wpFrontendAdminBackend.getElementCSSSelector($table[0]) + ' tbody > tr > :nth-child(' + headerIndex + '), ' + wpFrontendAdminBackend.getElementCSSSelector($table[0]) + ' tfoot > tr > :nth-child(' + headerIndex + ')';
					selector2 = wpFrontendAdminBackend.getElementCSSSelector($table[0], true) + ' tr.wpforms-dash-widget-forms-list-columns > :nth-child(' + headerIndex + '), ' + wpFrontendAdminBackend.getElementCSSSelector($table[0], true) + ' tbody > tr > :nth-child(' + headerIndex + '), ' + wpFrontendAdminBackend.getElementCSSSelector($table[0], true) + ' tfoot > tr > :nth-child(' + headerIndex + ')';
				}
			}

			// When we hide the header in a table that doesn't contain thead, automatically hide the parent row of the table
			if (jQuery(element).parents('table').length && !jQuery(element).parents('thead').length && jQuery(element).prop("tagName") === 'TH') {
				var $row = jQuery(element).parents('tr');
				if ($row.length) {
					selector1 = wpFrontendAdminBackend.getElementCSSSelector($row[0]);
					selector2 = wpFrontendAdminBackend.getElementCSSSelector($row[0], true);
				}
			}
			// When we hide any cell of the WooCommerce payment gateways table, hide the entire row automatically
			if (jQuery(element).parents('table.wc_gateways').length && jQuery(element).parents('tbody').length && jQuery(element).prop("tagName") === 'TD') {
				var $row = jQuery(element).parents('tr');
				if ($row.length) {
					selector1 = wpFrontendAdminBackend.getElementCSSSelector($row[0]);
					selector2 = wpFrontendAdminBackend.getElementCSSSelector($row[0], true);
				}
			}
			// When we hide any cell of the WooCommerce shipping zones table, hide the entire row automatically
			if (jQuery(element).parents('table.wc-shipping-zones').length && jQuery(element).parents('tbody').length && jQuery(element).prop("tagName") === 'TD') {
				var $row = jQuery(element).parents('tr');
				if ($row.length) {
					selector1 = wpFrontendAdminBackend.getElementCSSSelector($row[0]);
					selector2 = wpFrontendAdminBackend.getElementCSSSelector($row[0], true);
				}
			}
			// When we hide any link from the wp breadcrumbs, automatically hide the li tag that contains the breadcrumb item
			if (jQuery(element).prop("tagName") === 'A' && jQuery(element).parents('.subsubsub').length) {
				var $li = jQuery(element).parent('li');
				if ($li.length) {
					selector1 = wpFrontendAdminBackend.getElementCSSSelector($li[0]);
					selector2 = wpFrontendAdminBackend.getElementCSSSelector($li[0], true);
				}
			}

			if (jQuery(element).data('wpfa-temp-id')) {
				jQuery(element).attr('id', jQuery(element).data('wpfa-temp-id'));
				jQuery(element).data('wpfa-temp-id', '');
			}

			var $selection1 = jQuery(selector1);
			try {
				var $selection2 = jQuery(selector2);
			} catch (error) {
				var $selection2 = null;
			}
			// Hide elements for the preview
			if ($selection2 && ($selection2.length === 1 || vgfa_backend_data.enable_loose_css_selectors)) {
//				$selection2.hide();
//				$selection2.css('display', 'none !important');
// Remove any display value because we will set it using the class name
				$selection2.css('display', '');
				$selection2.addClass('wpfa-force-hide');
				var selector = selector2;
			} else {
//				$selection1.hide();
//				$selection1.css('display', 'none !important');
// Remove any display value because we will set it using the class name
				$selection1.css('display', '');
				$selection1.addClass('wpfa-force-hide');
				var selector = selector1;
			}

			// If the selector has the .active class, add a second selector for the element without the active class
			// because when we have tabs and we are hiding tab headers, the tab header might get the active class on click
			// causing this to hide only when the tab is active. So we must hide when it's inactive as well
			if (selector.indexOf('.active') > -1) {
				selector = selector + ', ' + selector.replace('.active', '');
			}

			wpFrontendAdminBackend.parentFunction('vgfaHideElement', selector);

			jQuery('a[wpfa-href]').each(function () {
				var $a = jQuery(this);
				$a.attr('href', $a.attr('wpfa-href'));
				$a.attr('onclick', $a.attr('wpfa-onclick'));
				$a.attr('target', $a.attr('wpfa-target'));
			});
		}});
	vgfaHideElementOutline.start();
}
function vgfaStartTextEdit() {
	window.vgfaIsEditingText = true;
	wpFrontendAdminBackend.getElementsWithTextEdit().attr('contenteditable', '');
	jQuery('body').append('<style id="text-change-css">[contenteditable] {    border: 2px solid #ffb300 !important;}</style>');

	wpFrontendAdminBackend.initializeTextChangesTracking();
}
function vgfaStopTextEdit() {
	window.vgfaIsEditingText = false;
	wpFrontendAdminBackend.getElementsWithTextEdit().removeAttr('contenteditable');
	jQuery('#text-change-css').remove();
}

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
	var rawData = e.originalEvent.data;  // Should work.

	if (!rawData || typeof rawData !== 'string' || typeof rawData.indexOf === 'undefined' || rawData.indexOf('{') < 0) {
		return true;
	}

	try {
		var data = JSON.parse(rawData);
	} catch (e) {
		return true;
	}
	vgseExecuteFunctionByName(data.functionName, window, {'data': data.arguments});
});

// Support for JS apps. We'll try to add the stateful links after navigating using hash navigation
// Only 3 times, one quickly, the other after 1.5 seconds, and finally 2.5 seconds to support different AJAX load times
if (!vgfa_backend_data.disable_stateful_navigation) {
	// Prepare the stateful links every 400ms because some JS apps modify content with ajax or insert content anytime
	setTimeout(function () {
		setInterval(function () {
			wpFrontendAdminBackend.prepareStatefulLinks(true);
		}, 400);
	}, 400);
	jQuery(window).on('hashchange', function (e) {
		setTimeout(function () {
			wpFrontendAdminBackend.prepareStatefulLinks(true);
		}, 400);
		setTimeout(function () {
			wpFrontendAdminBackend.prepareStatefulLinks(true);
		}, 1500);
		setTimeout(function () {
			wpFrontendAdminBackend.prepareStatefulLinks(true);
		}, 2500);
		setTimeout(function () {
			wpFrontendAdminBackend.prepareStatefulLinks(true);
		}, 4000);
		setTimeout(function () {
			wpFrontendAdminBackend.prepareStatefulLinks(true);
		}, 5000);
		setTimeout(function () {
			wpFrontendAdminBackend.prepareStatefulLinks(true);
		}, 6000);
	});
	// When the page loads with a hash directly, trigger the hashchange event so our prepareStatefulLinks intervals run
	// because the JS app will load content through ajax calls so we need to prepare those links
	if (window.location.hash) {
		window.dispatchEvent(new HashChangeEvent("hashchange"));
	}
}

/*Material theme support*/
setTimeout(function () {
	if (jQuery('#wpbody-content > .wrap').length > 1 && jQuery('body[class*="material"]').length > 1) {
		jQuery('#wpbody-content > .wrap').first().remove();
	}
}, 1000);