#!/bin/bash

# ============================================================================
# Git Hooks Installation Script
# ============================================================================
# This script installs pre-commit and pre-push hooks to ensure code quality
# Run this script after cloning the repository
# ============================================================================

set -e

if [ -d "/git/hooks" ]; then
    HOOKS_DIR="/git/hooks"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
    HOOKS_DIR="$PROJECT_ROOT/.git/hooks"
fi

if [ ! -d "$HOOKS_DIR" ]; then
    echo "[WARNING] Git hooks directory not found. Skipping hooks installation."
    echo "          This is normal if you're not in a git repository."
    exit 0
fi

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo ""
echo "============================================================================"
echo "  Git Hooks Installation"
echo "============================================================================"
echo ""

echo "[1/3] Creating pre-commit hook..."
cat > "$HOOKS_DIR/pre-commit" << 'EOF'
#!/bin/sh

# Check if any PHP files in backend/ are being committed
PHP_FILES=$(git diff --cached --name-only --diff-filter=ACM | grep '^backend/.*\.php$')

if [ -z "$PHP_FILES" ]; then
    echo ""
    echo "========================================"
    echo "  NO BACKEND PHP FILES MODIFIED"
    echo "========================================"
    echo ""
    echo "Skipping PHP CS Fixer and PHPStan checks."
    echo ""
    exit 0
fi

echo ""
echo "========================================"
echo "  PRE-COMMIT CHECKS (Backend)"
echo "========================================"
echo ""
echo "Modified PHP files:"
echo "$PHP_FILES" | sed 's/^/  - /'
echo ""

echo "[1/2] Running PHP CS Fixer..."
echo "----------------------------------------"
docker compose exec -T backend vendor/bin/php-cs-fixer fix --dry-run --diff

if [ $? -ne 0 ]; then
    echo ""
    echo "[ERROR] PHP CS Fixer found issues."
    echo ""
    echo "To fix automatically, run:"
    echo "  docker compose exec backend vendor/bin/php-cs-fixer fix"
    echo ""
    exit 1
fi

echo "[SUCCESS] PHP CS Fixer passed"
echo ""

echo "[2/2] Running PHPStan..."
echo "----------------------------------------"
docker compose exec -T backend vendor/bin/phpstan analyse --memory-limit=512M --no-progress

if [ $? -ne 0 ]; then
    echo ""
    echo "[ERROR] PHPStan found errors."
    echo "Please fix the errors above before committing."
    echo ""
    exit 1
fi

echo "[SUCCESS] PHPStan passed"
echo ""
echo "========================================"
echo "  ALL CHECKS PASSED"
echo "========================================"
echo ""
EOF

echo "      [OK] pre-commit hook created"
echo ""

echo "[2/3] Creating pre-push hook..."
cat > "$HOOKS_DIR/pre-push" << 'EOF'
#!/bin/sh

# Read the branch being pushed
while read local_ref local_sha remote_ref remote_sha
do
    # Extract branch name from remote_ref (refs/heads/branch-name)
    branch=$(echo "$remote_ref" | sed 's/refs\/heads\///')

    # Check if pushing to main or master
    if [ "$branch" = "main" ] || [ "$branch" = "master" ]; then
        echo ""
        echo "========================================"
        echo "  PUSH BLOCKED"
        echo "========================================"
        echo ""
        echo "[ERROR] Direct push to '$branch' is forbidden!"
        echo ""
        echo "Please create a pull request instead:"
        echo "  1. Push to a feature branch"
        echo "  2. Create a PR on GitHub"
        echo ""
        exit 1
    fi
done

exit 0
EOF

echo "      [OK] pre-push hook created"
echo ""

echo "[3/3] Setting permissions..."
chmod +x "$HOOKS_DIR/pre-commit"
chmod +x "$HOOKS_DIR/pre-push"
echo "      [OK] Hooks are now executable"
echo ""

echo "============================================================================"
echo "  INSTALLATION COMPLETE"
echo "============================================================================"
echo ""
echo "Installed hooks:"
echo "  > pre-commit  : Runs PHP CS Fixer and PHPStan before each commit"
echo "  > pre-push    : Prevents direct push to main/master branches"
echo ""
echo "To bypass hooks (NOT recommended):"
echo "  > git commit --no-verify"
echo "  > git push --no-verify"
echo ""
echo "For more information, see: scripts/README.md"
echo ""
