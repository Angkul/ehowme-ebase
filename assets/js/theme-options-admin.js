/**
 * Admin UI behavior for the "Theme Options" page: tab switching, a live
 * CSS-variables text preview, and a live iframe preview of the actual
 * homepage (with a Desktop / Tablet / Mobile device switcher) that
 * updates instantly for colors/toggles as fields change (before hitting
 * Save) — see hecApplyLivePreview().
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
			'--header-cta-color': hecVal('hec_cta_text_color', { fallback: '#ffffff' }),
			'--header-cta-radius': hecVal('hec_cta_btn_radius', { px: true, fallback: '30px' }),
			'--header-cta-bg-2': hecVal('hec_cta_bg_color_2', { fallback: '#ffffff' }),
			'--header-cta-hover-bg-2': hecVal('hec_cta_hover_bg_color_2', { fallback: '#f5f5f5' }),
			'--header-cta-color-2': hecVal('hec_cta_text_color_2', { fallback: '#222222' }),
			'--header-cta-border-2': hecVal('hec_cta_border_color_2', { fallback: '#222222' }),
			'--header-cta-radius-2': hecVal('hec_cta_btn_radius_2', { px: true, fallback: '30px' }),
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
	 * Live iframe preview (#hec-live-preview-frame) — the actual
	 * homepage, same-origin, so its document is directly reachable.
	 * CSS variables + a few show/hide toggles get pushed into the
	 * REAL rendered page instantly. Anything that needs different
	 * markup (menu items, icon choice, header layout order, mobile
	 * menu style, ...) only updates after Save reloads this iframe
	 * along with the rest of the admin page.
	 * ------------------------------------------------------------- */
	function hecGetPreviewDoc() {
		var iframe = document.getElementById('hec-live-preview-frame');
		if (!iframe) return null;
		try {
			var doc = iframe.contentDocument || (iframe.contentWindow && iframe.contentWindow.document);
			return (doc && doc.documentElement) ? doc : null;
		} catch (e) {
			return null; // shouldn't happen (same-origin), but don't blow up if it does
		}
	}

	// Hide the wp-admin toolbar inside the preview iframe (the real
	// page shows it too since we're logged in) so the box only shows
	// the theme's own header, not a second admin bar on top of it.
	function hecInjectPreviewStyles(doc) {
		if (doc.getElementById('hec-preview-style-override')) return;
		var style = doc.createElement('style');
		style.id = 'hec-preview-style-override';
		style.textContent = '#wpadminbar{display:none !important;} html{margin-top:0 !important;} body.admin-bar{margin-top:0 !important;}';
		doc.head.appendChild(style);
	}

	function hecApplyLivePreview() {
		var doc = hecGetPreviewDoc();
		if (!doc) return;

		var vars = hecComputeVars();
		var root = doc.documentElement;
		$.each(vars, function (key, val) {
			root.style.setProperty(key, val);
		});

		var $doc = $(doc);
		$doc.find('.lang-switch').toggle(hecIsChecked('hec_show_lang_switcher', true));
		$doc.find('.header-cta-btn:not(.header-cta-btn--secondary)').toggle(hecIsChecked('hec_show_cta_button', true));
		$doc.find('.header-cta-btn--secondary').toggle(hecIsChecked('hec_show_cta_button_2', false));

		var isTransparent = hecIsChecked('hec_header_transparent', false);
		$doc.find('.site-header-custom').toggleClass('is-transparent', isTransparent);
	}

	// Re-apply everything each time the iframe (re)loads — covers the
	// first load and any manual refresh of the preview frame.
	$('#hec-live-preview-frame').on('load', function () {
		var doc = hecGetPreviewDoc();
		if (doc) hecInjectPreviewStyles(doc);
		hecApplyLivePreview();
	});

	/* ---------------------------------------------------------------
	 * Device switcher — Desktop / Tablet / Mobile. Resizes the iframe's
	 * wrapper; the real theme's own responsive CSS (media queries)
	 * takes it from there since the iframe has its own viewport.
	 * ------------------------------------------------------------- */
	$('.hec-device-btn').on('click', function () {
		var device = $(this).data('device');
		$('.hec-device-btn').removeClass('is-active');
		$(this).addClass('is-active');
		$('#hec-live-preview-frame-wrap').attr('data-device', device);
	});

	// Initial paint (raw CSS text box only — the iframe applies itself
	// via its own 'load' handler above once it's actually ready).
	hecBuildCssPreview();

	// Live-update on every field that feeds a CSS variable or toggles
	// an element's visibility in the preview iframe.
	var hecWatchedFields = [
		'#hec_header_height', '#hec_header_max_width', '#hec_header_transparent',
		'#hec_header_bg_color', '#hec_header_border_color', '#hec_header_nav_color',
		'#hec_header_nav_hover_color', '#hec_header_active_color', '#hec_header_transparent_nav_color', '#hec_header_transparent_nav_hover_color', '#hec_mega_panel_top_offset', '#hec_mega_panel_width',
		'#hec_header_logo_height', '#hec_show_lang_switcher', '#hec_lang_btn_bg_color',
		'#hec_lang_btn_border_color', '#hec_lang_btn_hover_bg_color', '#hec_lang_btn_hover_border_color',
		'#hec_lang_btn_hover_color', '#hec_lang_btn_radius', '#hec_lang_menu_radius', '#hec_show_cta_button',
		'#hec_cta_bg_color', '#hec_cta_hover_bg_color', '#hec_cta_text_color', '#hec_cta_btn_radius',
		'#hec_show_cta_button_2', '#hec_cta_bg_color_2', '#hec_cta_hover_bg_color_2',
		'#hec_cta_text_color_2', '#hec_cta_border_color_2', '#hec_cta_btn_radius_2'
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

	/* ---------------------------------------------------------------
	 * Header Layout builder (tab "Header Layout") — one independent
	 * board per Desktop / Tablet / Mobile (they don't have to match),
	 * switched via the .hec-hb-device-btn tabs above them. All 3 boards
	 * stay initialized/live in the DOM at once (just hidden), so
	 * switching device tabs never drops an in-progress drag on another
	 * board. Drag chips between the Left / Center / Right / Not Used
	 * lists with jQuery UI Sortable (bundled with WP core, no extra
	 * library needed). Every drop re-serializes ALL 3 boards' current
	 * state into the hidden #hec_header_layout input as one JSON object
	 * ({ desktop:{...}, tablet:{...}, mobile:{...} }), which submits
	 * with the rest of the form and is sanitized server-side by
	 * hec_sanitize_header_layout().
	 * ------------------------------------------------------------- */
	var $hecBuilders = $('.hec-header-builder');
	if ($hecBuilders.length && $.fn.sortable) {
		function hecSerializeHeaderLayout() {
			var layout = {};
			$hecBuilders.each(function () {
				var device = $(this).data('hb-board');
				var deviceLayout = { left: [], center: [], right: [] };
				$(this).find('.hec-hb-list').each(function () {
					var zone = $(this).data('zone');
					if (!deviceLayout.hasOwnProperty(zone)) return; // "unused" list isn't saved
					$(this).find('.hec-hb-chip').each(function () {
						deviceLayout[zone].push($(this).data('id'));
					});
				});
				layout[device] = deviceLayout;
			});
			$('#hec_header_layout').val(JSON.stringify(layout));
		}

		$hecBuilders.each(function () {
			var device = $(this).data('hb-board');
			$(this).find('.hec-hb-list').sortable({
				connectWith: '#hec-header-builder-' + device + ' .hec-hb-list',
				placeholder: 'hec-hb-placeholder',
				forcePlaceholderSize: true,
				tolerance: 'pointer',
				update: hecSerializeHeaderLayout
			});
		});

		// Builder's own Desktop/Tablet/Mobile switcher (separate from the
		// Live Preview's — see the .hec-hb-device-btn CSS comment). Just
		// shows the matching board; nothing to re-render, every board is
		// already live underneath.
		$('.hec-hb-device-btn').on('click', function () {
			var device = $(this).data('hb-device');
			$('.hec-hb-device-btn').removeClass('is-active');
			$(this).addClass('is-active');
			$hecBuilders.hide().filter('[data-hb-board="' + device + '"]').show();
		});
	}

});
