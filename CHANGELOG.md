# eBase — Changelog

All notable changes to this project will be documented in this file.

---

## [1.1.9] - 2026-07-22

### Fixed
- **Mega menu บน tablet/iPad: แตะครั้งแรกที่เมนูแม่ redirect ไปหน้าเลย ไม่เปิด mega panel ก่อน** — ปุ่ม hover-intent เดิมใน `header.js` (`mouseenter`/`mouseleave` เปิด/ปิด class `.mega-open`) ผูกไว้แบบไม่เช็คชนิด device เลย บนหน้าจอสัมผัส (รวม iPad ใน Safari) เบราว์เซอร์จำลอง event `mouseenter` ให้ก่อน `click` เสมอในการแตะครั้งเดียว ผลคือ `.mega-open` ถูกเติมไปแล้วตั้งแต่ก่อน handler ของการแตะจะเช็คด้วยซ้ำ ทำให้ทุกครั้งที่แตะเหมือน "เปิดอยู่แล้ว" แล้วปล่อยให้ลิงก์ทำงานตามปกติ (redirect ทันที) แก้โดย: (1) ผูก `mouseenter`/`mouseleave` เฉพาะ device ที่ hover ได้จริงผ่าน `matchMedia('(hover: hover) and (pointer: fine)')`, (2) เพิ่ม `click` handler แยกสำหรับ device ที่ไม่รองรับ hover — แตะครั้งแรก `preventDefault()` + เปิด panel, แตะซ้ำที่เดิม (panel เปิดอยู่แล้ว) ปล่อยให้ลิงก์ทำงานปกติ, และแตะนอกกล่องที่เปิดอยู่จะปิดให้อัตโนมัติ ไม่กระทบพฤติกรรม hover เดิมบน PC/เมาส์เลย

## [1.1.8] - 2026-07-22

### Fixed
- **Dropdown เมนูปกติ (ไม่ใช่ mega menu) เรียงรายการแนวนอนแทนที่จะเป็นแนวตั้ง** — `.header-nav ul { display:flex }` เดิมเป็น descendant selector ที่จับ `<ul>` ทุกชั้นใน `.header-nav` รวมถึง `<ul class="nav-dropdown">` ของ submenu ด้วย ทำให้ submenu เรียงเป็นแถวแทนที่จะเป็นลิสต์แนวตั้ง แก้โดยจำกัด flex ให้เฉพาะ `.header-nav > ul` (ลิสต์บนสุดจริงๆ) เท่านั้น
- **รองรับ dropdown ซ้อนได้ 2 ชั้น (submenu ของ submenu)** — เพิ่ม chevron/aria-haspopup ให้ item ที่มี submenu ต่อในชั้นที่ 1 และ flyout ไปทางขวา (`.nav-dropdown .nav-dropdown`) สำหรับชั้นที่ 2 (PHP เดิมรองรับ `depth => 3` อยู่แล้ว งานนี้คือฝั่ง render/CSS)
- **บั๊กจริงที่เจอระหว่างทำ**: `HEC_Nav_Walker::start_el()` ปิด `</li>` ทันทีในบรรทัดเดียวกับที่เปิด ทำให้ `<ul>` ของ submenu ชั้น 2 ถูกต่อออกมา "นอก" `<li>` ที่ปิดไปแล้ว (`<ul><li>...</li><ul>...</ul></li></ul>` ซึ่งเป็น HTML ที่ผิด ` <ul>` ซ้อนใน `<ul>` ตรงๆ ไม่ได้) เบราว์เซอร์เลยตัด submenu ชั้น 2 หลุดออกจากโครงสร้าง ไม่โผล่ตอน hover แม้ chevron จะติ๊กว่ามี children ก็ตาม — ย้ายการปิด `</li>` ไปที่ `end_el()` แทน (แพทเทิร์นเดียวกับ top-level ที่ทำถูกอยู่แล้ว)

### Removed
- **CSS ที่ซ่อน CTA Button 2 อัตโนมัติในช่วง "squeezed desktop" (992px–~1499px)** — เดิมถ้าพื้นที่ไม่พอ (ปุ่มทั้งสอง + เมนูเต็ม + lang switcher) ธีมจะซ่อน Button 2 ให้เองผ่าน media query ที่ผูกกับ breakpoint คงที่ (ลองเปลี่ยนเป็นคำนวณจาก Container Max Width ระหว่างพัฒนา แต่สรุปว่าตัดออกทั้งหมด) — การซ่อนปุ่มที่แอดมินเปิดใช้งานเองแบบเงียบๆ ถือเป็นพฤติกรรมที่ไม่ควรมี ถ้าปุ่มล้นทับเมนูจริงในหน้างานจริง ตอนนี้เป็นการตัดสินใจของแอดมินแทน ผ่านช่องทางที่มีอยู่แล้ว: Theme Options → CTA Buttons → "Show Button 2" (ปิดทั้งเว็บ) หรือ Theme Options → Header Layout → ลาก CTA Button 2 ออกจากบอร์ด Desktop (เฉพาะ device นั้น Tablet/Mobile ยังโชว์ได้ตามปกติ) ปุ่ม Button 1 (primary) ยังหด padding/font/height ต่ำกว่า 1024px ตามเดิม ไม่กระทบ

## [1.1.7] - 2026-07-21

### Added
- **Per-device Header Layout** — Desktop / Tablet / Mobile แยกตั้งค่ากันได้อิสระในหน้า Theme Options → Header Layout (บอร์ด drag & drop 3 ชุด สลับด้วยปุ่ม device switcher) แต่ละ element render ครั้งเดียวใน DOM แล้ว `assets/js/header.js` ย้ายเข้า zone ตาม breakpoint ปัจจุบัน (mobile ≤767 / tablet ≤991 / desktop) — id ของ nav, off-canvas, lang switcher จึงไม่ซ้ำ ข้อมูลเก่ารูปแบบ flat ถูก migrate เป็น per-device อัตโนมัติ
- **Mobile Menu Toggle เป็น element ใน Header Layout builder** — ปุ่ม hamburger ลาก-ย้ายตำแหน่ง/zone ได้เหมือน element อื่น (เดิม hardcode ท้าย header) พร้อม migration backfill สำหรับ site ที่เคย save layout ไว้ก่อนหน้า เพื่อไม่ให้ปุ่มหายตอนอัปเดต
- Header Live Preview card ขยายเต็มความกว้างพื้นที่ admin (`#wpbody-content`) — ส่วน form ตั้งค่ายังคงกว้าง 1040px เท่าเดิม

### Changed
- Tablet / Mobile ไม่มี Center zone แล้ว — ทั้งในบอร์ด builder (เหลือ Left / Right) และใน CSS (ซ่อน `.header-zone--center` ให้ grid ยุบเหลือ 2 คอลัมน์ ชิดซ้าย-ขวาด้วย `justify-content: space-between`)
- Off-canvas slide menu: ปุ่ม Primary (CTA Button 1) ย้ายไปอยู่ล่างสุดของ footer (Button 2 อยู่เหนือ)
- Logo เป็น fit-content ทั้ง desktop และ mobile — คอลัมน์ grid ไม่กินพื้นที่เกินขนาดจริงของโลโก้

### Fixed
- **ตัวเลือก Sticky Header ไม่มีผลจริง** — `.site-header-custom` ถูกตั้ง `position: sticky` ตายตัวใน CSS ปิด option แล้ว header ก็ยัง sticky อยู่ดี ตอนนี้ sticky ทำงานผ่าน class `.is-sticky` ตามค่า option แล้ว (base เป็น relative)
- **Polylang: field ต่อภาษาในหน้า Theme Options ไม่ขึ้น** — `hec_get_languages()` ใช้ `pll_the_languages()` ซึ่งคืนค่าว่างใน wp-admin ทำให้ field `hec_header_menu_{lang}` / `hec_cta_label_{lang}` ฯลฯ ไม่ถูก register เลย เพิ่ม fallback เป็น `pll_languages_list()` ซึ่งทำงานทุก context (frontend ยังได้ per-page URL จาก path เดิม)
- **Keyboard navigation ของ dropdown เมนูพัง** — กด Enter บนเมนูแม่ที่มี submenu ถูก `preventDefault()` (เปิดลิงก์ไม่ได้เลย) และการ toggle เขียน inline `display:none` ทับ submenu ทำให้ CSS `:hover`/`:focus-within` reveal ตายถาวรแม้ใช้เมาส์ ตอนนี้พึ่ง `:focus-within` เปิด submenu ตอน Tab เข้า, Enter ตามลิงก์ได้ปกติ และจัดการเฉพาะ `aria-expanded` ผ่าน focusin/focusout
- กัน fatal `count('')` (PHP 8) ใน `hec_header_language_switcher()` เมื่อ `pll_the_languages()` คืนค่าว่างนอก frontend (REST/preview)
- **ปุ่ม CTA บน tablet/mobile ดูถูกบีบ** — rule เก่า `padding: 8px 14px; font-size: 0.82rem` ที่ ≤991px บีบปุ่มให้แคบ/ตัวหนังสือเล็ก ทั้งที่ความสูงยัง 42px ลบออกให้ใช้ padding/font ฐาน (0 20px / 0.9rem) สมส่วนกับความสูง
- ลบ rule เก่าที่ซ่อนปุ่ม CTA ใน header เสมอเมื่อใช้ mobile menu แบบ Sidebar (เขียนก่อนมี per-device builder) — ตอนนี้ผู้ใช้เลือกเองได้ว่า device ไหนโชว์ปุ่มอะไร
- ระยะห่างปุ่ม/lang switcher กับ hamburger บน tablet/mobile — grid track แบบ `auto` เดิมถูกเบราว์เซอร์แจกพื้นที่เหลือจนคอลัมน์ toggle บานเป็น ~190px บวก margin เก่าซ้อนกับ gap ใหม่เป็น 22px แก้เป็น 2 คอลัมน์ space-between + ลบ margin เก่า เหลือ gap 10px ตามตั้งใจ

## [1.1.6] - 2026-07-04

### Fixed
- รวม folder `inc/plugin-update-checker` (PUC vendor) เข้า repo/zip — เดิมโดน gitignore ทำให้ theme updater ใช้งานไม่ได้หลังติดตั้งจาก release zip

## [1.1.5] - 2026-07-03

### Fixed
- โหลด `ebase-config.php` ก่อน fallback constants ใน `functions.php`

## [1.1.4] - 2026-07-03

### Added
- **Lang Menu Border Radius** theme option (Language Button tab, under "Lang Button Border Radius") — controls the border-radius of the language dropdown panel (`.lang-menu`) and its item links (`.lang-menu a`), wired through the new `--lang-menu-radius` CSS variable. Falls back to the previous hardcoded look (12px / 8px) when left blank.

## [1.1.3] - 2026-07-03

### Fixed
- Console warning "Blocked aria-hidden on an element because its descendant retained focus" when closing the off-canvas mobile menu with keyboard focus still on a `.ofc-trigger` button inside it. `closeOfc()` now moves focus back to the hamburger toggle before setting `aria-hidden="true"` on `#hec-offcanvas`, so the attribute is never applied to an ancestor of the focused element.

## [1.1.2] - 2026-07-03

### Fixed
- Mobile off-canvas menu icons (back arrow, close X, submenu chevron) rendering invisible — their `<svg>` computed width was collapsing to 0px (sometimes a few px) because `.ofc-back`/`.ofc-close`/`.ofc-trigger` are `display:flex; justify-content:center` buttons with the SVG as their only child, and flexbox's `flex-basis:auto` doesn't reliably resolve to an SVG's own width/height *attributes* (only a CSS `width` would count). Fixed by giving each icon an explicit CSS width/height + `flex-shrink:0`. Confirmed via computed-style inspection and visual zoom on stg2.angkul.com/en/.

## [1.1.1] - 2026-07-03

### Changed
- Per-language Header Menu / Mobile Menu fields moved from the "Multi-Language" tab into the "Menus" tab (grouped under the existing Default fields, with a "— TH —" / "— EN —" divider per language), so all menu-related settings live in one place instead of being split across two tabs. No change to option keys or fallback behavior — purely a Theme Options UI reorganization.

## [1.1.0] - 2026-07-02

### Added
- **Per-language Header Menu / Mobile Menu** — new fields on the "Multi-Language" tab (`Header Menu [XX]`, `Mobile Menu [XX]`, one pair per active Polylang language) let each language use a completely different WordPress menu. Falls back to the existing "Header Menu (Default)" / "Mobile Menu (Default)" fields on the Menus tab when left blank, so sites not using per-language menus are unaffected. Implemented via the theme's existing `hec_get_multilang_option()` helper (already used for CTA label/URL) — `hec_get_header_menu_id()` and `hec_get_mobile_menu_id()` now check for a `_{lang}` suffixed option first.

## [1.0.9] - 2026-07-02

### Added
- **Mega Panel Top Offset** and **Mega Panel Width** theme options (Menus tab) — the mega menu panel's vertical offset and width are no longer hardcoded, wired through `--mega-panel-top-offset`/`--mega-panel-width` CSS variables. Note: if the offset is set back to a non-zero gap, the JS hover-intent delay added in 1.0.8 (not the gap size) is what actually protects against the panel closing before the mouse reaches it.
- **"Enable Mega Menu" checkbox** on Appearance → Menus for top-level menu items, instead of requiring the `has-mega` CSS class to be typed manually via Screen Options → CSS Classes. Both methods control the same underlying class and can be mixed — the checkbox toggles `has-mega` on top of whatever's already saved from the CSS Classes field without clobbering other manually-added classes.

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