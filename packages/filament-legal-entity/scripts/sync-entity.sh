#!/usr/bin/env bash
# Copy beegoodit/filament-legal-entity into a TallCMS plugin tree (ZIP-legal).
# Usage: sync-entity.sh <filament-legal-entity-path> <plugin-root>
set -euo pipefail

SRC="${1:-}"
DEST="${2:-}"

if [[ -z "${SRC}" || -z "${DEST}" ]]; then
  echo "Usage: $0 <filament-legal-entity-path> <plugin-root>" >&2
  exit 1
fi

SRC="$(cd "${SRC}" && pwd)"
DEST="$(cd "${DEST}" && pwd)"

if [[ ! -f "${SRC}/src/Models/LegalEntity.php" ]]; then
  echo "Not a filament-legal-entity package: ${SRC}" >&2
  exit 1
fi

ENTITY="${DEST}/src/Entity"
MIGRATIONS="${DEST}/database/migrations"
LANG_DIR="${DEST}/src/Lang"

rm -rf "${ENTITY}"
mkdir -p "${ENTITY}" "${MIGRATIONS}" "${LANG_DIR}"

FROM_NS='BeegoodIT\FilamentLegalEntity'
TO_NS='Beegoodit\Compliance\Entity'

copy_php() {
  local from="$1"
  local to="$2"
  mkdir -p "$(dirname "${to}")"
  python3 - "$from" "$to" "${FROM_NS}" "${TO_NS}" <<'PY'
import pathlib, re, sys
src, dest, from_ns, to_ns = sys.argv[1:5]
text = pathlib.Path(src).read_text()
text = text.replace(from_ns, to_ns)
text = text.replace("filament-legal-entity::", "beegoodit-compliance::")
# Pack-copy has no factories.
text = text.replace("use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;\n", "")
text = re.sub(r"\nuse Beegoodit\\Compliance\\Entity\\Database\\Factories\\LegalEntityFactory;\n", "\n", text)
text = text.replace("    use HasFactory, HasUuids;\n", "    use HasUuids;\n")
text = text.replace("    use HasFactory;\n", "")
text = re.sub(
    r"\n    protected static function newFactory\(\): LegalEntityFactory\n    \{\n        return LegalEntityFactory::new\(\);\n    \}\n",
    "\n",
    text,
)
header = """<?php

/**
 * Generated from beegoodit/filament-legal-entity. Do not edit.
 * See src/Entity/GENERATED.md
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

while IFS= read -r -d '' file; do
  rel="${file#${SRC}/src/}"
  case "${rel}" in
    FilamentLegalEntityServiceProvider.php|FilamentLegalEntityPlugin.php|Filament/Resources/*)
      continue
      ;;
  esac
  copy_php "${file}" "${ENTITY}/${rel}"
done < <(find "${SRC}/src" -name '*.php' -print0)

while IFS= read -r -d '' file; do
  name="$(basename "${file}")"
  cp "${file}" "${MIGRATIONS}/${name}"
done < <(find "${SRC}/database/migrations" -name '*.php' -print0)

for locale in en de; do
  mkdir -p "${LANG_DIR}/${locale}"
  cp "${SRC}/lang/${locale}/entity.php" "${LANG_DIR}/${locale}/entity.php"
done

cp "${SRC}/LICENSE" "${ENTITY}/LICENSE"

PIN="$(git -C "${SRC}" describe --tags --always --dirty 2>/dev/null || echo unknown)"

cat > "${ENTITY}/GENERATED.md" <<EOF
# Generated legal entity

Do **not** edit files in this directory. They are copied from \`beegoodit/filament-legal-entity\`.

- Source: \`${SRC}\`
- Pin: \`${PIN}\`
- License: MIT (see \`LICENSE\`)

Regenerate:

\`\`\`bash
./scripts/sync-entity \\
  /path/to/beegoodit-laravel/packages/filament-legal-entity \\
  /path/to/tallcms-compliance-plugin
\`\`\`
EOF

echo "Synced entity into ${DEST} (pin ${PIN})"
