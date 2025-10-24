#!/bin/bash
set -e

if [ ! -d "../.git" ]; then
    echo "Git repository not found. Skipping hook installation."
    exit 0
fi

echo "Installing CaptainHook Git hooks..."

vendor/bin/captainhook install -f --git-directory="../.git" > /dev/null 2>&1

for hook in ../.git/hooks/commit-msg ../.git/hooks/pre-push ../.git/hooks/pre-commit ../.git/hooks/prepare-commit-msg ../.git/hooks/post-commit ../.git/hooks/post-merge ../.git/hooks/post-checkout ../.git/hooks/post-rewrite; do
    if [ -f "$hook" ]; then
        sed -i 's|symfony/vendor/bin/captainhook|backend/vendor/bin/captainhook|g; s|--configuration=symfony/captainhook.json|--configuration=backend/captainhook.json|g' "$hook"
    fi
done

echo "CaptainHook hooks installed successfully."
