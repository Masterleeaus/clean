#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="${1:-$(git rev-parse --show-toplevel 2>/dev/null || pwd)}"
PATCH_DIR="$REPO_ROOT/integrations/ai-site-builder/patches"
BUILDER_ROOT="$REPO_ROOT/external/ai-site-builder"
EXPECTED_SHA256="e20d5dcfb3f4268af27befdca1cb3259dbc9f0458a3f60e114d899dd406a10a4"

fail() {
  printf 'AI Site Builder bridge install failed: %s\n' "$*" >&2
  exit 1
}

command -v base64 >/dev/null || fail 'base64 is required'
command -v unzip >/dev/null || fail 'unzip is required'
command -v rsync >/dev/null || fail 'rsync is required'
command -v python3 >/dev/null || fail 'python3 is required'

test -f "$REPO_ROOT/artisan" || fail 'MagicAI Laravel root is not populated'
test -f "$REPO_ROOT/config/app.php" || fail 'Missing config/app.php'
test -f "$REPO_ROOT/app/Domains/WorkCore/System/Contracts/TenantContextContract.php" || fail 'Compatible WorkCore tenant contract is required'
test -f "$REPO_ROOT/app/Domains/WorkCore/System/Contracts/PermissionResolverContract.php" || fail 'Compatible WorkCore permission contract is required'
test -f "$BUILDER_ROOT/package.json" || fail 'Sanitised AI site builder source is not populated under external/ai-site-builder'
test -d "$BUILDER_ROOT/src" || fail 'Builder src directory is missing'
test -d "$BUILDER_ROOT/supabase" || fail 'Builder Supabase directory is missing'

mapfile -t chunks < <(find "$PATCH_DIR" -maxdepth 1 -type f -name 'patches.zip.b64.*' | sort)
(( ${#chunks[@]} >= 8 )) || fail 'Patch chunks are incomplete'

work="$(mktemp -d)"
trap 'rm -rf "$work"' EXIT
cat "${chunks[@]}" | tr -d '\r\n\t ' | base64 --decode > "$work/patches.zip"
printf '%s  %s\n' "$EXPECTED_SHA256" "$work/patches.zip" | sha256sum --check --strict
unzip -t "$work/patches.zip" >/dev/null
unzip -q "$work/patches.zip" -d "$work/extracted"

test -d "$work/extracted/magicai-overlay/app/Extensions/AiSiteBuilderBridge" || fail 'MagicAI overlay is missing'
test -d "$work/extracted/builder-overlay/src" || fail 'Builder overlay is missing'
test -f "$work/extracted/builder-overlay/supabase/migrations/20260730010000_add_titan_zero_bridge.sql" || fail 'Builder bridge migration is missing'

rsync -a "$work/extracted/magicai-overlay/app/" "$REPO_ROOT/app/"
rsync -a "$work/extracted/builder-overlay/" "$BUILDER_ROOT/"

REPO_ROOT="$REPO_ROOT" python3 <<'PY'
import os
from pathlib import Path

root = Path(os.environ['REPO_ROOT'])
config = root / 'config/app.php'
text = config.read_text()
provider = 'App\\Extensions\\AiSiteBuilderBridge\\System\\AiSiteBuilderBridgeServiceProvider::class,'
if provider not in text:
    anchors = [
        'App\\Domains\\WorkCore\\WorkCoreServiceProvider::class,',
        'App\\Providers\\AppServiceProvider::class,',
        'AppServiceProvider::class,',
    ]
    for anchor in anchors:
        if anchor in text:
            text = text.replace(anchor, anchor + '\n        ' + provider, 1)
            break
    else:
        raise SystemExit('Could not locate a safe provider insertion point in config/app.php')
    config.write_text(text)

env = root / '.env.example'
env_text = env.read_text() if env.exists() else ''
block = '''
# Titan Zero external AI site builder
AI_SITE_BUILDER_ENABLED=false
AI_SITE_BUILDER_BASE_URL=
AI_SITE_BUILDER_SUPABASE_URL=
AI_SITE_BUILDER_SUPABASE_SERVICE_ROLE_KEY=
AI_SITE_BUILDER_CLIENT_ID=magicai
AI_SITE_BUILDER_SHARED_SECRET=
AI_SITE_BUILDER_WEBHOOK_SECRET=
AI_SITE_BUILDER_TIMEOUT=15
'''
if 'AI_SITE_BUILDER_ENABLED=' not in env_text:
    env.write_text(env_text.rstrip() + '\n' + block)
PY

if find "$BUILDER_ROOT" -type f \( -name '.env' -o -name '*.pem' -o -name '*.p12' -o -name '*.pfx' -o -name 'id_rsa' -o -name 'id_ed25519' \) -print -quit | grep -q .; then
  fail 'Secret-bearing files were found in the builder tree'
fi

if command -v php >/dev/null; then
  find "$REPO_ROOT/app/Extensions/AiSiteBuilderBridge" -type f -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null
  php -l "$REPO_ROOT/config/app.php" >/dev/null
fi

cat > "$REPO_ROOT/integrations/ai-site-builder/PATCH_APPLIED.md" <<EOF
# AI Site Builder bridge patch applied

- Patch SHA-256: \`$EXPECTED_SHA256\`
- MagicAI target: repository root
- Builder target: \`external/ai-site-builder/\`
- WorkCore remains the authority for operational records.
- Branch: \`agent/ai-site-builder-bridge\`
EOF

printf 'AI Site Builder bridge applied to MagicAI and external/ai-site-builder.\n'
printf 'Configure matching secrets, run Laravel and Supabase migrations, then deploy both applications separately.\n'
