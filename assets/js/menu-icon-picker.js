/**
 * Menu Icon Picker — Appearance → Menus
 *
 * Adds a searchable icon-grid popover (+ "Upload Custom Icon" via the
 * WP Media Library) behind each menu item's "Menu Icon" button, added
 * server-side by hec_menu_item_icon_field() in inc/header-functions.php.
 *
 * One shared popover is built once and repositioned/rebound to whichever
 * item's toggle button was clicked, instead of duplicating the full icon
 * grid markup per menu item (menus can have many items).
 *
 * @package HelloElementorChild
 */
(function ($) {
	'use strict';

	if (typeof hecIconPickerData === 'undefined') {
		return;
	}

	var icons = hecIconPickerData.icons || {};
	var i18n = hecIconPickerData.i18n || {};
	var $popover = null;
	var $activePicker = null; // the .hec-icon-picker currently bound to the open popover
	var mediaFrame = null;

	function buildPopover() {
		if ($popover) {
			return $popover;
		}

		var $el = $(
			'<div class="hec-icon-picker-popover" style="display:none;">' +
				'<div class="hec-icon-picker-popover-hdr">' +
					'<input type="text" class="hec-icon-picker-search" placeholder="' + i18n.search + '">' +
					'<button type="button" class="hec-icon-picker-close" aria-label="' + i18n.close + '">&times;</button>' +
				'</div>' +
				'<div class="hec-icon-picker-grid"></div>' +
				'<button type="button" class="button button-primary hec-icon-picker-upload">' + i18n.upload + '</button>' +
			'</div>'
		);

		var $grid = $el.find('.hec-icon-picker-grid');

		// "None" tile first — clears both slug and custom image. Same
		// single-column square as every icon tile below it (was
		// grid-column:span 2 with a text label — combined with the grid
		// items' aspect-ratio:1/1, spanning 2 columns doubled its WIDTH
		// which then doubled its HEIGHT to match, making it visibly
		// twice the size of every other tile). A small "no icon" glyph
		// keeps it visually consistent with the icon grid instead.
		var noneSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M5.7 5.7l12.6 12.6"/></svg>';
		$grid.append(
			$('<button type="button" class="hec-icon-picker-item hec-icon-picker-item--none" data-slug="none"></button>')
				.attr('title', i18n.none)
				.html(noneSvg)
		);

		$.each(icons, function (slug, icon) {
			if ('none' === slug) {
				return;
			}
			var $btn = $('<button type="button" class="hec-icon-picker-item"></button>')
				.attr('data-slug', slug)
				.attr('title', icon.label)
				.html(icon.svg);
			$grid.append($btn);
		});

		$('body').append($el);
		$popover = $el;
		bindPopoverEvents();
		return $popover;
	}

	function bindPopoverEvents() {
		$popover.on('click', '.hec-icon-picker-close', function () {
			closePopover();
		});

		$popover.on('input', '.hec-icon-picker-search', function () {
			var term = $(this).val().toLowerCase();
			$popover.find('.hec-icon-picker-item').each(function () {
				var $item = $(this);
				var label = ($item.attr('title') || '').toLowerCase();
				$item.toggle(-1 === label.indexOf(term) ? false : true);
			});
		});

		$popover.on('click', '.hec-icon-picker-item', function () {
			var slug = $(this).data('slug');
			applySelection(slug, '', '');
			closePopover();
		});

		$popover.on('click', '.hec-icon-picker-upload', function (e) {
			e.preventDefault();
			openMediaFrame();
		});

		$(document).on('click', function (e) {
			if (!$popover || !$popover.is(':visible')) {
				return;
			}
			// The WP Media Library modal (opened by "Upload Custom Icon")
			// renders outside .hec-icon-picker-popover, appended straight
			// to <body>. Every click inside it (browsing the grid,
			// clicking "Select"/"Use this image") bubbles to this same
			// document handler — without this check it was treated as a
			// "click outside", closing the popover and nulling
			// $activePicker via closePopover() BEFORE the media frame's
			// own 'select' event even fired. applySelection() then found
			// $activePicker already gone and silently did nothing — the
			// exact "upload doesn't work" symptom. WP adds
			// body.modal-open while any media modal is open; that plus
			// an explicit .media-modal/.media-modal-backdrop check
			// covers it regardless of timing.
			if ($('body').hasClass('modal-open') || $(e.target).closest('.media-modal, .media-modal-backdrop, .media-frame').length) {
				return;
			}
			var $target = $(e.target);
			if ($target.closest('.hec-icon-picker-popover').length || $target.closest('.hec-icon-picker-toggle').length) {
				return;
			}
			closePopover();
		});

		$(document).on('keydown', function (e) {
			if ('Escape' === e.key && $popover && $popover.is(':visible')) {
				closePopover();
			}
		});
	}

	/**
	 * Writes the chosen icon (preset slug OR custom image) into the
	 * currently-bound .hec-icon-picker's two hidden inputs + updates its
	 * button preview. Exactly one of (slug, imageId) should be meaningful
	 * at a time — passing a slug clears the image field and vice versa,
	 * mirroring the "custom image wins" precedence in the PHP save
	 * handler (hec_save_menu_item_icon()).
	 */
	function applySelection(slug, imageId, previewHtml) {
		if (!$activePicker) {
			return;
		}
		var $slugInput = $activePicker.find('.hec-icon-picker-slug');
		var $imageInput = $activePicker.find('.hec-icon-picker-image');
		var $preview = $activePicker.find('.hec-icon-picker-preview');

		if (imageId) {
			$imageInput.val(imageId);
			$slugInput.val('none');
			$preview.html(previewHtml);
		} else {
			$slugInput.val(slug || 'none');
			$imageInput.val('');
			$preview.html('none' === slug ? '' : (icons[slug] ? icons[slug].svg : ''));
		}
	}

	function openMediaFrame() {
		if (mediaFrame) {
			mediaFrame.open();
			return;
		}
		mediaFrame = wp.media({
			title: i18n.uploadTitle,
			button: { text: i18n.uploadButton },
			multiple: false,
			library: { type: ['image/png', 'image/jpeg', 'image/gif', 'image/webp'] },
		});
		mediaFrame.on('select', function () {
			var attachment = mediaFrame.state().get('selection').first().toJSON();
			var previewUrl = attachment.url;
			if (attachment.sizes) {
				if (attachment.sizes.hec_menu_icon) {
					previewUrl = attachment.sizes.hec_menu_icon.url;
				} else if (attachment.sizes.thumbnail) {
					previewUrl = attachment.sizes.thumbnail.url;
				}
			}
			var previewHtml = '<img src="' + previewUrl + '" class="nav-dropdown-icon-img" alt="">';
			applySelection('', attachment.id, previewHtml);
			closePopover();
		});
		mediaFrame.open();
	}

	function closePopover() {
		if ($popover) {
			$popover.hide();
		}
		$activePicker = null;
	}

	function openPopoverFor($picker, $toggleBtn) {
		buildPopover();
		$activePicker = $picker;

		var offset = $toggleBtn.offset();
		$popover
			.css({
				top: offset.top + $toggleBtn.outerHeight() + 4,
				left: offset.left,
			})
			.show();
		$popover.find('.hec-icon-picker-search').val('').trigger('input').trigger('focus');
	}

	$(document).on('click', '.hec-icon-picker-toggle', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var $picker = $btn.closest('.hec-icon-picker');
		if ($activePicker && $activePicker.is($picker) && $popover && $popover.is(':visible')) {
			closePopover();
			return;
		}
		openPopoverFor($picker, $btn);
	});

})(jQuery);
