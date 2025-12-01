#!/bin/bash

set -e

if [ -d "vendor/bin" ]; then
    PROJECT_ROOT=".."
    BACKEND_DIR="."
else
    PROJECT_ROOT="."
    BACKEND_DIR="backend"
fi

GIT_DIR="${PROJECT_ROOT}/.git"

if [ ! -d "$GIT_DIR" ]; then
    echo "⚠️  Git repository not found. Skipping hook installation."
    exit 0
fi

echo "🔧 Installing CaptainHook Git hooks..."

if ! command -v php &> /dev/null; then
    echo "📦 PHP not found on host, using Docker..."

    if ! docker compose ps 2>/dev/null | grep -q "timemanager_backend.*Up"; then
        echo "❌ Error: Backend container is not running"
        echo "   Please start the containers first: docker compose up -d"
        exit 1
    fi

    cd "$PROJECT_ROOT"
    docker compose exec backend vendor/bin/captainhook install -f --git-directory=/var/www/.git

    echo "🔧 Configuring hooks to use Docker..."
    for hook in commit-msg pre-push pre-commit prepare-commit-msg post-commit post-merge post-checkout post-rewrite; do
        HOOK_FILE=".git/hooks/${hook}"
        if [ -f "$HOOK_FILE" ]; then
            sed -i 's|symfony/vendor/bin/captainhook|backend/vendor/bin/captainhook|g; s|--configuration=symfony/captainhook.json|--configuration=backend/captainhook.json|g' "$HOOK_FILE"
            sed -i 's|backend/vendor/bin/captainhook|docker compose exec -T backend vendor/bin/captainhook|g; s|--configuration=backend/captainhook.json|--configuration=captainhook.json --git-directory=/var/www/.git|g' "$HOOK_FILE"
            echo "   ✓ ${hook} (configured for Docker)"
        fi
    done
else
    echo "📦 Using local PHP installation..."
    cd "$BACKEND_DIR"
    vendor/bin/captainhook install -f --git-directory="../.git"

    for hook in ../.git/hooks/commit-msg ../.git/hooks/pre-push ../.git/hooks/pre-commit ../.git/hooks/prepare-commit-msg ../.git/hooks/post-commit ../.git/hooks/post-merge ../.git/hooks/post-checkout ../.git/hooks/post-rewrite; do
        if [ -f "$hook" ]; then
            sed -i 's|symfony/vendor/bin/captainhook|backend/vendor/bin/captainhook|g; s|--configuration=symfony/captainhook.json|--configuration=backend/captainhook.json|g' "$hook"
        fi
    done
fi

cd "$PROJECT_ROOT"
echo ""
echo "✅ CaptainHook hooks installed successfully!"
echo ""
echo "📋 Installed hooks:"
ls -1 .git/hooks/ | grep -v ".sample" || echo "   (none)"
echo ""
if ! command -v php &> /dev/null; then
    echo "ℹ️  Note: Hooks run via Docker, ensure 'docker compose up -d' is running"
fi
