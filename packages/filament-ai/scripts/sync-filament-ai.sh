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

# Refuse to copy into a plugin that asks for an older laravel/ai than this package.
# The plugin never runs Composer against the package, so nothing else enforces it.
python3 - "${SRC}/composer.json" "${DEST}/composer.json" "${DEST}/plugin.json" <<'PY'
import json, pathlib, re, sys

PACKAGE = "laravel/ai"


def lower_bound(constraint):
    """Lowest version any alternative of a Composer constraint allows."""
    bounds = []
    for alternative in re.split(r"\s*\|\|?\s*", constraint.strip()):
        match = re.search(r"\d+(?:\.\d+)*", alternative)
        if match:
            bounds.append(tuple(int(part) for part in match.group(0).split(".")))
    if not bounds:
        raise SystemExit(f"Cannot read a version from constraint [{constraint}].")
    return min(bounds, key=lambda bound: bound + (0,) * (4 - len(bound)))


def padded(bound):
    return bound + (0,) * (4 - len(bound))


source = json.loads(pathlib.Path(sys.argv[1]).read_text())
required = source.get("require", {}).get(PACKAGE)
if required is None:
    raise SystemExit(f"{sys.argv[1]} does not require {PACKAGE}.")
minimum = lower_bound(required)

failures = []
for path, section in ((sys.argv[2], "require"), (sys.argv[3], "requires_packages")):
    file = pathlib.Path(path)
    if not file.is_file():
        continue
    packages = json.loads(file.read_text()).get(section)
    stated = packages.get(PACKAGE) if isinstance(packages, dict) else None
    if stated is None:
        failures.append(f"  {path}: {section} has no {PACKAGE} entry (need {required})")
    elif padded(lower_bound(stated)) < padded(minimum):
        failures.append(f"  {path}: {section} allows {PACKAGE} {stated} (need {required})")

if failures:
    print(f"Refusing to sync: filament-ai requires {PACKAGE} {required}.", file=sys.stderr)
    print("\n".join(failures), file=sys.stderr)
    print("Raise the plugin's requirement first, then sync again.", file=sys.stderr)
    raise SystemExit(1)
PY

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
