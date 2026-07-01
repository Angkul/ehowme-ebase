#!/bin/bash
# release.sh — สร้าง zip พร้อม folder name ที่ถูกต้องสำหรับ WordPress
# วิธีใช้: bash release.sh 1.0.1
#
# แล้วเอา ehowme-ebase-1.0.1.zip ไปแนบใน GitHub Release

VERSION=${1:-"1.0.0"}
THEME_SLUG="ehowme-ebase"
ZIP_NAME="${THEME_SLUG}-${VERSION}.zip"
PARENT_DIR=$(dirname "$(pwd)")

echo "Building ${ZIP_NAME}..."

# สร้าง zip จาก folder ปัจจุบัน โดยตัดไฟล์ที่ไม่ต้องการออก
cd "$PARENT_DIR" && zip -r "$ZIP_NAME" "$THEME_SLUG" \
  --exclude "*.git*" \
  --exclude "*/.DS_Store" \
  --exclude "*/desktop.ini" \
  --exclude "*/Thumbs.db" \
  --exclude "*/.idea/*" \
  --exclude "*/.vscode/*" \
  --exclude "*/node_modules/*" \
  --exclude "*/ebase-config.php" \
  --exclude "*/release.sh" \
  --exclude "*.zip"

mv "$PARENT_DIR/$ZIP_NAME" "$(pwd)/$ZIP_NAME"
echo "Done: $(pwd)/$ZIP_NAME"
