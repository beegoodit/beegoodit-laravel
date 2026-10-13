#!/usr/bin/env bash
# Pack-copy beegoodit/filament-ai into a TallCMS AI plugin tree (ZIP-legal).
# Keeps BeegoodIT\FilamentAi\ namespaces — no TallCMS rewrite / aliases.
# Usage: sync-filament-ai.sh <filament-ai-path> <plugin-root>
set -euo pipefail

SRC="${1:-}"
DEST="${2:-}"

if [[ -z "${SRC}" || -z "${DEST}" ]]; then
  echo "Usage: $0 <filament-ai-path> <plugin-root>" >&2
  exit 1
fi

SRC="$(cd "${SRC}" && pwd)"
DEST="$(cd "${DEST}" && pwd)"

if [[ ! -f "${SRC}/src/Capabilities/CapabilityRegistry.php" ]]; then
  echo "Not a filament-ai package: ${SRC}" >&2
  exit 1
fi

if [[ ! -f "${DEST}/plugin.json" ]] && [[ ! -f "${DEST}/src/Providers/AiServiceProvider.php" ]]; then
  echo "Not a tallcms-ai plugin root: ${DEST}" >&2
  exit 1
fi

OUT="${DEST}/src/FilamentAi"
PIN="$(git -C "${SRC}" describe --tags --always --dirty 2>/dev/null || echo unknown)"

rm -rf "${OUT}"
# Drop legacy sync destinations from the Shell-era layout
rm -rf "${DEST}/src/Shell"
rm -rf "${DEST}/resources/views/shell"
rm -f "${DEST}/lang/en/shell.php" "${DEST}/lang/de/shell.php"

mkdir -p "${OUT}"

# Copy package src; skip host-only provider/plugin (TallCMS uses AiServiceProvider + TallcmsAiPlugin)
while IFS= read -r -d '' file; do
  rel="${file#${SRC}/src/}"
  case "${rel}" in
    FilamentAiServiceProvider.php|Filament/FilamentAiPlugin.php)
      continue
      ;;
  esac
  dest_file="${OUT}/${rel}"
  mkdir -p "$(dirname "${dest_file}")"
  python3 - "$file" "$dest_file" <<'PY'
import pathlib, sys
src, dest = pathlib.Path(sys.argv[1]), pathlib.Path(sys.argv[2])
text = src.read_text()
header = """<?php

/**
 * Generated from beegoodit/filament-ai (MIT). Do not edit.
 * See src/FilamentAi/GENERATED.md
 */

"""
if text.startswith("<?php"):
    rest = text[5:].lstrip("\n")
    if rest.startswith("declare(strict_types=1);"):
        text = "<?php\n\n/**\n * Generated from beegoodit/filament-ai (MIT). Do not edit.\n * See src/FilamentAi/GENERATED.md\n */\n\n" + rest
    else:
        text = header + rest
dest.write_text(text)
PY
done < <(find "${SRC}/src" -name '*.php' -print0)

# Lang + views under FilamentAi resources (keep filament-ai:: keys)
mkdir -p "${OUT}/resources/lang" "${OUT}/resources/views"
if [[ -d "${SRC}/resources/lang" ]]; then
  cp -R "${SRC}/resources/lang/." "${OUT}/resources/lang/"
fi
if [[ -d "${SRC}/resources/views" ]]; then
  cp -R "${SRC}/resources/views/." "${OUT}/resources/views/"
fi

cp "${SRC}/LICENSE" "${OUT}/LICENSE"

cat > "${OUT}/GENERATED.md" <<EOF
# Generated \`beegoodit/filament-ai\` (MIT)

Do **not** edit files under \`src/FilamentAi/\`.

- Source: \`${SRC}\`
- Pin: \`${PIN}\`
- Namespace: \`BeegoodIT\\\\FilamentAi\\\\\` (same as Composer package)
- Autoload: plugin \`composer.json\` maps that NS → \`src/FilamentAi/\`

TallCMS glue (\`Providers/\`, \`Tools/\`, \`Filament/\` live UI, \`Models/SiteAiSetting.php\`, …) stays hand-written.

## Regenerate

\`\`\`bash
packages/filament-ai/scripts/sync-filament-ai.sh \\
  packages/filament-ai \\
  /path/to/tallcms-ai-plugin
\`\`\`

Also sync Chamaleon: \`plugins/beegoodit/tallcms-ai/\`.
EOF

echo "Synced filament-ai into ${DEST}/src/FilamentAi (pin ${PIN})"
