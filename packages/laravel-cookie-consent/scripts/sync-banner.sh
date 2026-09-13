#!/usr/bin/env bash
# Copy beegoodit/laravel-cookie-consent into a TallCMS plugin tree (ZIP-legal).
# Usage: sync-banner.sh <laravel-cookie-consent-path> <plugin-root>
set -euo pipefail

SRC="${1:-}"
DEST="${2:-}"

if [[ -z "${SRC}" || -z "${DEST}" ]]; then
  echo "Usage: $0 <laravel-cookie-consent-path> <plugin-root>" >&2
  exit 1
fi

SRC="$(cd "${SRC}" && pwd)"
DEST="$(cd "${DEST}" && pwd)"

if [[ ! -f "${SRC}/src/Livewire/CookieConsent.php" ]]; then
  echo "Not a laravel-cookie-consent package: ${SRC}" >&2
  exit 1
fi

BANNER="${DEST}/src/Banner"
LANG_DIR="${DEST}/src/Lang"
VIEWS="${DEST}/resources/views"

rm -rf "${BANNER}" "${LANG_DIR}"
mkdir -p "${BANNER}" "${LANG_DIR}" "${VIEWS}/livewire"

FROM_NS='BeegoodIT\LaravelCookieConsent'
TO_NS='Beegoodit\CookieConsent\Banner'

copy_php() {
  local rel="$1"
  local from="${SRC}/src/${rel}"
  local to="${BANNER}/${rel}"
  mkdir -p "$(dirname "${to}")"
  python3 - "$from" "$to" "${FROM_NS}" "${TO_NS}" <<'PY'
import pathlib, sys
src, dest, from_ns, to_ns = sys.argv[1:5]
text = pathlib.Path(src).read_text()
text = text.replace(from_ns, to_ns)
text = text.replace("config('cookie-consent.", "config('beegoodit-cookie-consent.")
text = text.replace('config("cookie-consent.', 'config("beegoodit-cookie-consent.')
text = text.replace("cookie-consent::", "beegoodit-cookie-consent::")
header = """<?php

/**
 * Generated from beegoodit/laravel-cookie-consent (MIT). Do not edit.
 * See src/Banner/GENERATED.md
 */

"""
if text.startswith("<?php"):
    rest = text[5:].lstrip("\n")
    if rest.startswith("declare(strict_types=1);"):
        text = header.rstrip() + "\n\n" + rest
    else:
        text = header + rest
pathlib.Path(dest).write_text(text)
PY
}

# PHP (never the MIT service provider)
while IFS= read -r -d '' file; do
  rel="${file#${SRC}/src/}"
  if [[ "${rel}" == "LaravelCookieConsentServiceProvider.php" ]]; then
    continue
  fi
  copy_php "${rel}"
done < <(find "${SRC}/src" -name '*.php' -print0)

# Views
for view in cookie-consent.blade.php cookie-consent-daisy.blade.php; do
  if [[ -f "${SRC}/resources/views/livewire/${view}" ]]; then
    python3 - "${SRC}/resources/views/livewire/${view}" "${VIEWS}/livewire/${view}" <<'PY'
import pathlib, sys
src, dest = sys.argv[1:3]
text = pathlib.Path(src).read_text()
text = text.replace("cookie-consent::", "beegoodit-cookie-consent::")
pathlib.Path(dest).write_text(text)
PY
  fi
done

# Translations: en + de only
for locale in en de; do
  mkdir -p "${LANG_DIR}/${locale}"
  cp "${SRC}/resources/lang/${locale}/messages.php" "${LANG_DIR}/${locale}/messages.php"
done

cp "${SRC}/LICENSE" "${BANNER}/LICENSE"

PIN="$(git -C "${SRC}" describe --tags --always --dirty 2>/dev/null || echo unknown)"

cat > "${BANNER}/GENERATED.md" <<EOF
# Generated banner

Do **not** edit files in this directory. They are copied from \`beegoodit/laravel-cookie-consent\`.

- Source: \`${SRC}\`
- Pin: \`${PIN}\`
- License: MIT (see \`LICENSE\`)

Regenerate:

\`\`\`bash
packages/laravel-cookie-consent/scripts/sync-banner.sh \\
  packages/laravel-cookie-consent \\
  /path/to/tallcms-cookie-consent-plugin
\`\`\`
EOF

echo "Synced banner into ${DEST} (pin ${PIN})"
