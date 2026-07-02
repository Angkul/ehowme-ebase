# eBase — Changelog

All notable changes to this project will be documented in this file.

---

## [1.0.8] - 2026-07-02

### Added
- **Nav Text Hover Color (Transparent State)** theme option — a dedicated hover color for nav links while the header is actively floating transparent over the hero (unscrolled), separate from the existing solid-state hover color. Follows the same "more specific .scrolled rule restores the normal look" pattern already used for the base transparent text color, so it automatically steps aside once the header goes solid on scroll.

### Fixed
- Mega menu closing before the user could move the mouse into it and click anything. `.mega-panel` is `position: absolute` with `pointer-events: none` by default, only becoming interactive while the trigger `<li>` matches `:hover`/`:focus-within` in CSS — a 1px gap between the trigger and the panel (plus normal mouse movement not being perfectly vertical) was enough to drop the `:hover` state for an instant while moving toward the panel, which also strips `pointer-events` immediately (it isn't part of the animated transition), so the panel could still be visible mid-fade yet already unclickable. Removed the gap and added a JS hover-intent delay (`assets/js/header.js`) that keeps the panel open for 250ms after the mouse leaves the trigger, cancelled if the cursor re-enters the trigger or the panel, as an additional trigger alongside the existing CSS `:hover`/`:focus-within` — keyboard/focus behavior is untouched.

## [1.0.7] - 2026-07-02

### Fixed
- Transparent header showing as solid white instead of overlapping the hero. Two compounding causes: (1) the rule that pulls page content up under the header targeted `#content`, which only exists on templates that go through the theme's own `template-parts/*.php` — any page actually built with Elementor (the normal case) renders its own wrapper instead, so the rule never matched there; (2) on sites using the off-canvas mobile menu (Sidebar Right/Left), `template-parts/custom-header.php` also emits `#hec-drawer-overlay` and `#hec-offcanvas` right after `</header>`, so a naive "next sibling" fix pulled those up instead of the real content. Now explicitly targets the real content root in every combination header.php can produce (Elementor-rendered pages, theme-template pages, dropdown menu, off-canvas menu). Verified on both a dropdown-menu site (stg2.angkul.com) and an off-canvas-menu site (localhost).
- Header buttons (`.lang-btn`, `.mobile-menu-toggle`, and the off-canvas `.ofc-back`/`.ofc-close`/`.ofc-trigger`) silently losing their font-size/border-radius/padding/border to Elementor's global kit CSS (`.elementor-kit-4 button, ...`), which is more specific than a bare single-class selector. Scoped each under its stable container id (`#site-header`, `#hec-offcanvas`) instead of adding `!important`, since an id always outranks any number of classes/types.
- Elementor-built mega menu content inheriting `.header-nav ul li a`'s padding/color/font-size (10px 18px, etc.) even when the Elementor container itself was set to 0 padding. That selector is a plain descendant selector, so it matched any `<a>` nested in a `<ul><li>` anywhere inside `.header-nav`, including Elementor widgets (icon lists, nav menus, ...) rendered inside the mega panel's Elementor-template mode (`.mega-elementor-wrap`, see `HEC_Nav_Walker`). Added a `:not(.mega-elementor-wrap a)` exclusion so the rule stays scoped to the theme's own nav markup and leaves Elementor-authored content alone.

## [1.0.6] - 2026-07-02

### Fixed
- Transparent sticky header not working on mobile (removed an erroneous `position: relative` override inside the mobile media query)
- Header and off-canvas mobile menu no longer reserve extra space for the admin bar on mobile
- Border-radius CSS inputs being ignored on mobile (hardcoded fallback overrode the theme option)
- Language button / CTA button radius fields silently failing to save (`type="number"` couldn't hold CSS length values like `30px`) — switched to text inputs with a `hec_css_length()` sanitizer
- Logo-removed fallback showing a generic "Site" instead of the actual site name

### Added
- Border-radius theme options for the language switcher button and the CTA button
- Dedicated CTA hover background color option (previously tied to the base CTA color)
- Functional "Header CSS Variables" preview that reflects unsaved field changes live
- Real visual live header preview inside the Theme Options page

### Changed
- Theme Options admin page redesigned: tabbed layout, English labels, less scrolling
- Removed the unused "Site Tagline" field

## [1.0.0] - 2026-06-30

### Initial Release

**Header & Navigation**
- Custom sticky header (`template-parts/custom-header.php`)
- Mega menu support via `has-mega` CSS class on menu items
- Elementor template inside mega panel: ใส่ `elementor:ID` ใน Description ของ menu item
- Custom `Walker_Nav_Menu` (`HEC