# eBase — Changelog

All notable changes to this project will be documented in this file.

---

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