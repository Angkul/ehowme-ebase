<?php
/**
 * eBase Theme Config
 * แก้ไฟล์นี้ก่อน deploy ให้ลูกค้า
 *
 * @package eBase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── GitHub Update Settings ───────────────────────────────────────────────────
//
// EBASE_GITHUB_USER  : GitHub username ที่เป็นเจ้าของ repo
// EBASE_GITHUB_REPO  : ชื่อ repo (ควรตรงกับชื่อ folder theme)
// EBASE_GITHUB_PAT   : Personal Access Token สำหรับ private repo
//                      สร้างได้ที่ GitHub → Settings → Developer settings
//                      → Personal access tokens → Fine-grained tokens
//                      Permission ที่ต้องการ: Contents (read-only)
//
// ถ้าไม่ต้องการ auto-update ให้ตั้ง EBASE_GITHUB_USER และ EBASE_GITHUB_REPO เป็น ''

define( 'EBASE_GITHUB_USER', 'Angkul' );
define( 'EBASE_GITHUB_REPO', 'ehowme-ebase' );
define( 'EBASE_GITHUB_PAT',  '' ); // ใส่ token ตรงนี้เมื่อใช้ private repo
