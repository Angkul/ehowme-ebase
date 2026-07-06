/**
 * Admin UI behavior for the "Theme Options" page: tab switching, a live
 * CSS-variables text preview, and a real visual header mock-up that
 * updates instantly as fields change (before hitting Save).
 */
jQuery(function ($) {

	/* ---------------------------------------------------------------
	 * Tabs
	 * ------------------------------------------------------------- */
	$('.hec-tab-btn').on('click', function () {
		var tab = $(this).data('tab');
		$('.hec-tab-btn').removeClass('is-active');
		$(this).addClass('is-active');
		$('.hec-tab-panel').removeClass('is-active');
		$('.hec-tab-panel[data-tab="' + tab + '"]').addClass('is-active');
	});

	/* ---------------------------------------------------------------
	 * Color pickers
	 * ------------------------------------------------------------- */
	$('.hec-color-picker').wpColorPicker({
		change: function (e, ui) {
			$(e.target).next('.hec-color-hex').text(ui.color.toString());
			// wpColorPicker fires this outside the normal "change" event
			// cycle, so kick the preview manually.
			hecBuildCssPreview();
			hecApplyLivePreview();
		}
	});

	/* ---------------------------------------------------------------
	 * Helpers — read the CURRENT (maybe unsaved) value of a field
	 * ------------------------------------------------------------- */
	function hecVal(id, opts) {
		opts = opts || {};
		var $el = $('#' + id);
		if (!$el.length) return opts.fallback || '';
		var val = $el.is(':checkbox') ? ($el.is(':checked') ? '1' : '0') : $el.val();
		if (val === '' || val === null || typeof val === 'undefined') {
			val = opts.fallback || '';
		}
		if (opts.px && /^-?\d+(\.\d+)?$/.test(val)) {
			val += 'px';
		}
		return val;
	}

	function hecIsChecked(id, fallback) {
		var $el = $('#' + id);
		if (!$el.length) return fallback;
		return $el.is(':checked');
	}

	/* ---------------------------------------------------------------
	 * Mirrors hec_output_header_css_vars() in inc/theme-options.php
	 * ------------------------------------------------------------- */
	function hecComputeVars() {
		return {
			'--header-height': hecVal('hec_header_height', { px: true, fallback: '70px' }),
			'--header-max-width': hecVal('hec_header_max_width', { px: true, fallback: '1200px' }),
			'--header-bg-color': hecVal('hec_header_bg_color', { fallback: '#ffffff' }),
			'--header-border-color': hecVal('hec_header_border_color', { fallback: '#e5e5e5' }),
			'--header-nav-color': hecVal('hec_header_nav_color', { fallback: '#333333' }),
			'--header-nav-hover-color': hecVal('hec_header_nav_hover_color', { fallback: '#e67e22' }),
			'--header-nav-active-color': hecVal('hec_header_active_color', { fallback: '#e67e22' }),
			'--header-transparent-nav-color': hecVal('hec_header_transparent_nav_color', { fallback: '#ffffff' }),
			'--header-transparent-nav-hover-color': hecVal('hec_header_transparent_nav_hover_color', { fallback: '#ffffff' }),
			'--mega-panel-top-offset': hecVal('hec_mega_panel_top_offset', { fallback: '0px' }),
			'--mega-panel-width': hecVal('hec_mega_panel_width', { fallback: '760px' }),
			'--header-logo-height': hecVal('hec_header_logo_height', { px: true, fallback: '50px' }),
			'--header-cta-bg': hecVal('hec_cta_bg_color', { fallback: '#222222' }),
			'--header-cta-hover-bg': hecVal('hec_cta_hover_bg_color', { fallback: '#e67e22' }),
			'--header-cta-color': '#ffffff',
			'--header-cta-radius': hecVal('hec_cta_btn_radius', { px: true, fallback: '30px' }),
			'--lang-btn-bg-color': hecVal('hec_lang_btn_bg_color', { fallback: '#ffffff' }),
			'--lang-btn-border-color': hecVal('hec_lang_btn_border_color', { fallback: '#E8E8E6' }),
			'--lang-btn-hover-bg-color': hecVal('hec_lang_btn_hover_bg_color', { fallback: '#f7f7f5' }),
			'--lang-btn-hover-border-color': hecVal('hec_lang_btn_hover_border_color', { fallback: '#E8E8E6' }),
			'--lang-btn-hover-color': hecVal('hec_lang_btn_hover_color', { fallback: '#0F0F0F' }),
			'--lang-btn-radius': hecVal('hec_lang_btn_radius', { px: true, fallback: '100px' }),
			'--lang-menu-radius': hecVal('hec_lang_menu_radius', { px: true, fallback: '12px' })
		};
	}

	/* ---------------------------------------------------------------
	 * Raw CSS text preview box (#hec-css-preview)
	 * ------------------------------------------------------------- */
	function hecBuildCssPreview() {
		var $pre = $('#hec-css-preview');
		if (!$pre.length) return;
		var vars = hecComputeVars();
		var lines = [':root {'];
		$.each(vars, function (key, val) {
			lines.push('  ' + key + ': ' + val + ';');
		});
		lines.push('}');
		$pre.text(lines.join('\n'));
	}

	/* ---------------------------------------------------------------
	 * Visual header mock-up (#hec-live-preview) — same CSS classes as
	 * the real front-end header, just with the CSS variables applied
	 * as inline custom properties on the wrapper so they cascade down.
	 * ------------------------------------------------------------- */
	function hecApplyLivePreview() {
		var $preview = $('#hec-live-preview');
		if (!$preview.length) return;

		var vars = hecComputeVars();
		var el = $preview.get(0);
		$.each(vars, function (key, val) {
			el.style.setProperty(key, val);
		});

		$('#hec-mock-lang-switch').toggle(hecIsChecked('hec_show_lang_switcher', true));
		$('#hec-mock-cta').toggle(hecIsChecked('hec_show_cta_button', true));

		var isTransparent = hecIsChecked('hec_header_transparent', false);
		$('#hec-mock-header').toggleClass('is-transparent', isTransparent);
		$preview.toggleClass('is-transparent', isTransparent);

		// Logo: swap between uploaded image and site-name text, live.
		var logoUrl = $('#hec_logo_url').val();
		var $logoLink = $('#hec-mock-logo-wrap a');
		if (logoUrl) {
			var $img = $logoLink.find('img');
			if (!$img.length) {
				$logoLink.empty();
				$img = $('<img id="hec-mock-logo-img" alt="" style="max-height:var(--header-logo-height,50px);width:auto;">');
				$logoLink.append($img);
			}
			$img.attr('src', logoUrl);
		} else if (!$logoLink.find('.site-title-text').length) {
			var siteName = $preview.data('site-name') || 'Site';
			$logoLink.empty().append($('<span class="site-title-text" id="hec-mock-sitename"></span>').text(siteName));
		}
	}

	// Scrolling inside the preview box toggles the same ".scrolled"
	// class assets/js/header.js adds on the real front-end, so the
	// transparent → solid transition can be seen without leaving wp-admin.
	$('#hec-live-preview').on('scroll', function () {
		var scrolled = $(this).scrollTop() > 40;
		$('#hec-mock-header').toggleClass('scrolled', scrolled);
	});

	// Initial paint.
	hecBuildCssPreview();
	hecApplyLivePreview();

	// Live-update on every field that feeds a CSS variable or toggles
	// a mock element's visibility.
	var hecWatchedFields = [
		'#hec_header_height', '#hec_header_max_width', '#hec_header_transparent',
		'#hec_header_bg_color', '#hec_header_border_color', '#hec_header_nav_color',
		'#hec_header_nav_hover_color', '#hec_header_active_color', '#hec_header_transparent_nav_color', '#hec_header_transparent_nav_hover_color', '#hec_mega_panel_top_offset', '#hec_mega_panel_width',
		'#hec_header_logo_height', '#hec_show_lang_switcher', '#hec_lang_btn_bg_color',
		'#hec_lang_btn_border_color', '#hec_lang_btn_hover_bg_color', '#hec_lang_btn_hover_border_color',
		'#hec_lang_btn_hover_color', '#hec_lang_btn_radius', '#hec_lang_menu_radius', '#hec_show_cta_button',
		'#hec_cta_bg_color', '#hec_cta_hover_bg_color', '#hec_cta_btn_radius'
	].join(', ');

	$(document).on('input change', hecWatchedFields, function () {
		hecBuildCssPreview();
		hecApplyLivePreview();
	});

	/* ---------------------------------------------------------------
	 * Media uploader (Logo Image / Logo Image 2x)
	 * ------------------------------------------------------------- */
	var mediaFrame;
	$(document).on('click', '.hec-upload-btn', function (e) {
		e.preventDefault();
		var targetId = $(this).data('target');
		var $wrap = $(this).closest('td');

		mediaFrame = wp.media({
			title: 'Select Logo Image',
			button: { text: 'Use this image' },
			multiple: false,
			library: { type: 'image' }
		});

		mediaFrame.on('select', function () {
			var attachment = mediaFrame.state().get('selection').first().toJSON();
			$('#' + targetId).val(attachment.url);
			var $imgPreview = $wrap.find('img');
			if ($imgPreview.length) {
				$imgPreview.attr('src', attachment.url);
			} else {
				$wrap.prepend('<img src="' + attachment.url + '" style="max-height:60px;max-width:240px;display:block;margin-bottom:8px;border-radius:4px;border:1px solid #ddd;padding:4px;">');
			}
			if (!$wrap.find('.hec-remove-btn').length) {
				$wrap.find('.hec-upload-btn').after(' <button type="button" class="button hec-remove-btn" data-target="' + targetId + '">Remove</button>');
			}
			hecApplyLivePreview();
		});

		mediaFrame.open();
	});

	$(document).on('click', '.hec-remove-btn', function (e) {
		e.preventDefault();
		var targetId = $(this).data('target');
		var $wrap = $(this).closest('td');
		$('#' + targetId).val('');
		$wrap.find('img').remove();
		$(this).remove();
		hecApplyLivePreview();
	});

});
