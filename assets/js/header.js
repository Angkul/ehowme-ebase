/**
 * Header JavaScript
 * - Sticky on scroll
 * - Language switcher dropdown
 * - Mobile menu toggle
 *
 * @package HelloElementorChild
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {

		var header = document.getElementById('site-header');
		if (!header) return;

		/* ================================================
		   Sticky header — add .scrolled class on scroll
		   ================================================ */
		if (header.classList.contains('is-sticky')) {
			var lastScroll = 0;

			window.addEventListener('scroll', function () {
				var currentScroll = window.pageYOffset || document.documentElement.scrollTop;

				if (currentScroll > 10) {
					header.classList.add('scrolled');
				} else {
					header.classList.remove('scrolled');
				}

				lastScroll = currentScroll <= 0 ? 0 : currentScroll;
			}, { passive: true });
		}

		/* ================================================
		   Language Switcher Dropdown
		   ================================================ */
		var langSwitcher = document.getElementById('hec-lang-switcher');

		if (langSwitcher) {
			var langBtn = langSwitcher.querySelector('.lang-switcher-btn');

			langBtn && langBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				var isOpen = langSwitcher.classList.toggle('open');
				langBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});

			// Close on outside click
			document.addEventListener('click', function () {
				langSwitcher.classList.remove('open');
				langBtn && langBtn.setAttribute('aria-expanded', 'false');
			});

			// Close on Escape key
			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape') {
					langSwitcher.classList.remove('open');
					langBtn && langBtn.setAttribute('aria-expanded', 'false');
					langBtn && langBtn.focus();
				}
			});
		}

		/* ================================================
		   Mobile — Dropdown vs Off-Canvas
		   ================================================ */
		var mobileToggle = document.getElementById('hec-mobile-toggle');
		var overlay      = document.getElementById('hec-drawer-overlay');
		var ofc          = document.getElementById('hec-offcanvas');
		var dropdownNav  = document.getElementById('site-navigation');
		var body         = document.body;

		/* ── DROPDOWN MODE ── */
		if (!ofc && dropdownNav && mobileToggle) {
			mobileToggle.addEventListener('click', function () {
				var open = dropdownNav.classList.toggle('mobile-open');
				mobileToggle.classList.toggle('active', open);
				mobileToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			});
			window.addEventListener('resize', function () {
				if (window.innerWidth > 991) {
					dropdownNav.classList.remove('mobile-open');
					mobileToggle.classList.remove('active');
					mobileToggle.setAttribute('aria-expanded', 'false');
				}
			});
		}

		/* ── OFF-CANVAS MODE ── */
		if (ofc && mobileToggle) {
			var stack = ['root'];

			function getPanel(id) {
				return ofc.querySelector('[data-panel="' + id + '"]');
			}

			function openOfc() {
				ofc.classList.add('is-open');
				ofc.setAttribute('aria-hidden', 'false');
				overlay && overlay.classList.add('active');
				body.style.overflow = 'hidden';
				mobileToggle.classList.add('active');
				mobileToggle.setAttribute('aria-expanded', 'true');
			}

			function closeOfc() {
				ofc.classList.remove('is-open');
				ofc.setAttribute('aria-hidden', 'true');
				overlay && overlay.classList.remove('active');
				body.style.overflow = '';
				mobileToggle.classList.remove('active');
				mobileToggle.setAttribute('aria-expanded', 'false');
				// Reset panels after transition ends
				setTimeout(function () {
					ofc.querySelectorAll('.ofc-panel').forEach(function (p) {
						p.classList.remove('ofc-panel--active', 'ofc-panel--prev');
					});
					var root = getPanel('root');
					if (root) root.classList.add('ofc-panel--active');
					stack = ['root'];
				}, 350);
			}

			function pushPanel(id) {
				var cur = stack[stack.length - 1];
				var curEl  = getPanel(cur);
				var nextEl = getPanel(id);
				if (!nextEl) return;
				curEl  && curEl.classList.remove('ofc-panel--active');
				curEl  && curEl.classList.add('ofc-panel--prev');
				nextEl.classList.remove('ofc-panel--prev');
				nextEl.classList.add('ofc-panel--active');
				nextEl.scrollTop = 0;
				stack.push(id);
			}

			function popPanel() {
				if (stack.length <= 1) { closeOfc(); return; }
				var cur  = stack.pop();
				var prev = stack[stack.length - 1];
				var curEl  = getPanel(cur);
				var prevEl = getPanel(prev);
				curEl  && curEl.classList.remove('ofc-panel--active', 'ofc-panel--prev');
				prevEl && prevEl.classList.remove('ofc-panel--prev');
				prevEl && prevEl.classList.add('ofc-panel--active');
			}

			// Toggle button
			mobileToggle.addEventListener('click', function () {
				ofc.classList.contains('is-open') ? closeOfc() : openOfc();
			});

			// Overlay
			overlay && overlay.addEventListener('click', closeOfc);

			// Delegated events inside off-canvas
			ofc.addEventListener('click', function (e) {
				if (e.target.closest('.ofc-close')) { closeOfc(); return; }
				if (e.target.closest('.ofc-back'))  { popPanel();  return; }
				var trig = e.target.closest('.ofc-trigger');
				if (trig) pushPanel(trig.dataset.target);
			});

			// Escape key
			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape' && ofc.classList.contains('is-open')) closeOfc();
			});

			// Resize to desktop → close
			window.addEventListener('resize', function () {
				if (window.innerWidth > 991 && ofc.classList.contains('is-open')) closeOfc();
			});
		}

		/* ================================================
		   Nav Dropdown — keyboard accessibility
		   ================================================ */
		var navItems = document.querySelectorAll('.header-nav li');

		navItems.forEach(function (item) {
			var submenu = item.querySelector('ul');
			if (!submenu) return;

			var link = item.querySelector('a');

			// Add aria attributes
			if (link) {
				link.setAttribute('aria-haspopup', 'true');
				link.setAttribute('aria-expanded', 'false');
			}

			item.addEventListener('mouseenter', function () {
				if (link) link.setAttribute('aria-expanded', 'true');
			});

			item.addEventListener('mouseleave', function () {
				if (link) link.setAttribute('aria-expanded', 'false');
			});

			// Keyboard: open on Enter or Space
			link && link.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					var expanded = link.getAttribute('aria-expanded') === 'true';
					link.setAttribute('aria-expanded', expanded ? 'false' : 'true');
					submenu.style.display = expanded ? 'none' : 'flex';
				}
			});
		});

	});

})();
