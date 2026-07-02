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
		   Sticky / Transparent header — add .scrolled class
		   once the user leaves the top of the page. Runs for
		   sticky headers AND transparent headers (transparent
		   needs it to know when to switch back to solid bg).

		   Previously this ran on every single 'scroll' event and
		   wrote to classList synchronously — on trackpad/momentum
		   scrolling that fires dozens of times per second and was
		   forcing style recalculations mid-scroll, which is what
		   showed up as stutter/jank (and a whitish flash while the
		   background-color transition kept restarting). Fixed by:
		   1) batching to one check per animation frame (rAF)
		   2) skipping the DOM write entirely when state is unchanged
		   3) hysteresis (different enter/exit thresholds) so tiny
		      scroll jitter near one boundary can't flicker the class
		   ================================================ */
		if (header.classList.contains('is-sticky') || header.classList.contains('is-transparent')) {
			var HEC_SCROLLED_ENTER = 60; // px — switch to solid after this
			var HEC_SCROLLED_EXIT  = 20; // px — switch back to transparent/top state below this

			var hecIsScrolled = null; // unknown yet — forces the first run to apply a state
			var hecTicking    = false;

			var hecApplyScrollState = function () {
				hecTicking = false;
				var currentScroll = window.pageYOffset || document.documentElement.scrollTop;

				var shouldBeScrolled = hecIsScrolled;
				if (currentScroll > HEC_SCROLLED_ENTER) {
					shouldBeScrolled = true;
				} else if (currentScroll < HEC_SCROLLED_EXIT) {
					shouldBeScrolled = false;
				}

				if (shouldBeScrolled === hecIsScrolled) return; // no change — skip the DOM write

				hecIsScrolled = shouldBeScrolled;
				header.classList.toggle('scrolled', hecIsScrolled);
			};

			var hecOnScroll = function () {
				if (hecTicking) return;
				hecTicking = true;
				window.requestAnimationFrame(hecApplyScrollState);
			};

			// Run once on load in case the page opens already scrolled (e.g. #anchor link)
			hecApplyScrollState();

			window.addEventListener('scroll', hecOnScroll, { passive: true });
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
		   Mega Menu — hover intent

		   .mega-panel is position:absolute with pointer-events:none
		   by default (see style.css), only becoming interactive
		   while .nav-item--mega:hover/:focus-within matches. Pure
		   CSS :hover requires the cursor to stay continuously over
		   the trigger <li> (or the panel itself, since it's a
		   descendant) — any brief dead zone the cursor crosses while
		   moving from the link down into the panel drops :hover
		   instantly, which also strips pointer-events immediately
		   (not animated), so the panel can visually still be fading
		   out yet already be unclickable. A short close delay that's
		   cancelled by re-entering either the trigger or the panel
		   absorbs normal mouse movement without needing to track
		   cursor geometry. .mega-open is an additional trigger
		   alongside the existing :hover/:focus-within in style.css,
		   not a replacement — keyboard/focus behavior is untouched.
		   ================================================ */
		var megaCloseTimers = new WeakMap();

		document.querySelectorAll('.nav-item--mega').forEach(function (item) {
			function openMega() {
				var timer = megaCloseTimers.get(item);
				if (timer) {
					clearTimeout(timer);
					megaCloseTimers.delete(item);
				}
				item.classList.add('mega-open');
			}

			function scheduleCloseMega() {
				var timer = setTimeout(function () {
					item.classList.remove('mega-open');
					megaCloseTimers.delete(item);
				}, 250);
				megaCloseTimers.set(item, timer);
			}

			item.addEventListener('mouseenter', openMega);
			item.addEventListener('mouseleave', scheduleCloseMega);
		});

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
