# eBase — Changelog

All notable changes to this project will be documented in this file.

---

## [1.0.0] - 2026-06-30

### Initial Release

**Header & Navigation**
- Custom sticky header (`template-parts/custom-header.php`)
- Mega menu support via `has-mega` CSS class on menu items
- Elementor template inside mega panel: ใส่ `elementor:ID` ใน Description ของ menu item
- Custom `Walker_Nav_Menu` (`HEC_Nav_Walker`) รองรับ dropdown + mega

**Mobile Menu**
- Off-canvas slide-step menu พร้อม multi-level panel navigation
- Theme Option เลือกรูปแบบ: Dropdown / Sidebar Right / Sidebar Left
- เลือก menu แยกสำหรับมือถือได้ใน Theme Options
- CTA button แสดงใน off-canvas footer

**Theme Options** (Appearance → Theme Options)
- อัปโหลด logo + retina 2x, ปรับความสูง
- ปุ่ม CTA: label / URL พร้อม per-language support
- เปิด/ปิด sticky header
- เลือก mobile menu style และ menu

**Multi-language**
- รองรับ Polylang และ WPML แบบ native
- Language switcher dropdown
- CTA label/URL แปลต่างภาษาได้
- hreflang SEO tags ใน `<head>` อัตโนมัติ

**อื่นๆ**
- GitHub Auto-Updater: อัปเดต theme ผ่าน WordPress dashboard ได้เลย
- Security: ABSPATH guard, output escaping, nonce, capability checks ทุกจุด
