#!/bin/bash

# =============================================================================
# BAUKASTEN PROJECT SETUP SCRIPT
# =============================================================================
# This script automates the setup of a new Baukasten project including:
# - GitHub repository creation and configuration
# - Netlify site setup and deployment
# - Environment variables and secrets configuration
# - Domain setup assistance
# - Initial deployment
#
# Prerequisites:
# - GitHub CLI (gh) installed and authenticated
# - Netlify CLI (netlify-cli) installed and authenticated
# - Git installed and configured
# - Node.js 18+ installed
#
# Usage:
#   ./setup-baukasten-project.sh
#
# =============================================================================

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# Global variables
PROJECT_NAME=""
DOMAIN_NAME=""
CMS_DOMAIN=""
CMS_HOSTING=""
UBERSPACE_USER=""
UBERSPACE_HOST=""
FRONTEND_REPO=""
CMS_REPO=""
NETLIFY_SITE_ID=""
NETLIFY_CMS_SITE_ID=""
DEPLOY_HOOK_URL=""
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

# =============================================================================
# UTILITY FUNCTIONS
# =============================================================================

log_info() {
    echo -e "${BLUE}ℹ ${1}${NC}"
}

log_success() {
    echo -e "${GREEN}✅ ${1}${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  ${1}${NC}"
}

log_error() {
    echo -e "${RED}❌ ${1}${NC}"
}

log_step() {
    echo -e "\n${BOLD}${BLUE}🚀 ${1}${NC}\n"
}

prompt_input() {
    local prompt="$1"
    local default="$2"
    local result

    if [ -n "$default" ]; then
        read -p "$(echo -e "${YELLOW}${prompt} [${default}]: ${NC}")" result
        result="${result:-$default}"
    else
        read -p "$(echo -e "${YELLOW}${prompt}: ${NC}")" result
    fi

    echo "$result"
}

confirm() {
    local prompt="$1"
    local response

    read -p "$(echo -e "${YELLOW}${prompt} (y/N): ${NC}")" response
    case "$response" in
        [yY][eE][sS]|[yY]) return 0 ;;
        *) return 1 ;;
    esac
}

check_prerequisites() {
    log_step "Checking prerequisites"

    local missing_tools=()

    if ! command -v gh &> /dev/null; then
        missing_tools+=("GitHub CLI (gh)")
    fi

    if ! command -v netlify &> /dev/null; then
        missing_tools+=("Netlify CLI")
    fi

    if ! command -v git &> /dev/null; then
        missing_tools+=("Git")
    fi

    if ! command -v node &> /dev/null; then
        missing_tools+=("Node.js")
    fi

    if [ ${#missing_tools[@]} -ne 0 ]; then
        log_error "Missing required tools:"
        for tool in "${missing_tools[@]}"; do
            echo "  - $tool"
        done
        echo ""
        echo "Please install the missing tools and try again."
        echo ""
        echo "Installation guides:"
        echo "  - GitHub CLI: https://cli.github.com/"
        echo "  - Netlify CLI: npm install -g netlify-cli"
        echo "  - Node.js: https://nodejs.org/"
        exit 1
    fi

    # Check authentication
    if ! gh auth status &> /dev/null; then
        log_error "GitHub CLI is not authenticated. Please run: gh auth login"
        exit 1
    fi

    # Check Netlify authentication more reliably
    if ! netlify api getCurrentUser &> /dev/null; then
        log_error "Netlify CLI is not authenticated. Please run: netlify login"
        exit 1
    fi

    log_success "All prerequisites are satisfied"
}

# =============================================================================
# PROJECT CONFIGURATION
# =============================================================================

gather_project_info() {
    log_step "Gathering project information"

    echo "Let's set up your new Baukasten project!"
    echo ""
    echo "Examples:"
    echo "  • Project name: 'my-portfolio', 'company-website', 'blog-2024'"
    echo "  • Frontend domain: 'myportfolio.com', 'company.com', 'my-blog.netlify.app'"
    echo "  • CMS domain: 'cms.myportfolio.com', 'admin.company.com', 'username.uber.space'"
    echo ""

    PROJECT_NAME=$(prompt_input "Enter your project name (lowercase, hyphens allowed)" "my-portfolio")

    # Validate project name
    if [[ ! "$PROJECT_NAME" =~ ^[a-z0-9-]+$ ]]; then
        log_error "Project name must contain only lowercase letters, numbers, and hyphens"
        exit 1
    fi

    DOMAIN_NAME=$(prompt_input "Enter your frontend domain (without https://)" "${PROJECT_NAME}.netlify.app")

    # Validate domain name is not empty
    if [ -z "$DOMAIN_NAME" ]; then
        log_error "Frontend domain cannot be empty"
        exit 1
    fi

    echo ""
    echo "CMS Hosting Options (Kirby CMS requires PHP hosting):"
    echo "  1. Uberspace (SSH deployment, manual setup required)"
    echo "  2. Custom PHP hosting (manual setup required)"
    echo ""

    local hosting_choice
    while true; do
        hosting_choice=$(prompt_input "Choose CMS hosting [1-2]" "1")
        case $hosting_choice in
            1)
                CMS_HOSTING="uberspace"
                UBERSPACE_USER=$(prompt_input "Enter your Uberspace username" "fifth")
                UBERSPACE_HOST=$(prompt_input "Enter your Uberspace host" "lacerta.uberspace.de")
                local default_domain="${UBERSPACE_USER}.uber.space"
                CMS_DOMAIN=$(prompt_input "Enter your CMS domain (without https://)" "$default_domain")
                if [ -z "$CMS_DOMAIN" ]; then
                    log_error "CMS domain cannot be empty"
                    exit 1
                fi
                break
                ;;
            2)
                CMS_HOSTING="custom"
                CMS_DOMAIN=$(prompt_input "Enter your CMS domain (without https://)" "cms.${PROJECT_NAME}.com")
                if [ -z "$CMS_DOMAIN" ]; then
                    log_error "CMS domain cannot be empty"
                    exit 1
                fi
                break
                ;;
            *)
                log_warning "Please choose 1 or 2"
                ;;
        esac
    done

    FRONTEND_REPO="${PROJECT_NAME}"
    CMS_REPO="cms.${PROJECT_NAME}"

    echo ""
    log_info "Project configuration:"
    echo "  Project name: $PROJECT_NAME"
    echo "  Frontend domain: ${DOMAIN_NAME:-'(not set)'}"
    echo "  CMS domain: ${CMS_DOMAIN:-'(not set)'}"
    echo "  CMS hosting: $CMS_HOSTING"
    if [ "$CMS_HOSTING" = "uberspace" ]; then
        echo "  Uberspace user: $UBERSPACE_USER"
        echo "  Uberspace host: $UBERSPACE_HOST"
    fi
    echo "  Frontend repo: $FRONTEND_REPO"
    echo "  CMS repo: $CMS_REPO"
    echo ""

    if ! confirm "Is this configuration correct?"; then
        echo "Please run the script again with the correct information."
        exit 0
    fi
}

# =============================================================================
# GITHUB SETUP
# =============================================================================

setup_github_repos() {
    log_step "Setting up GitHub repositories"

    local github_user=$(gh api user --jq '.login')

    # Check if frontend repository already exists
    if gh repo view "$github_user/$FRONTEND_REPO" &> /dev/null; then
        log_warning "Frontend repository $FRONTEND_REPO already exists"
        if confirm "Do you want to use the existing repository?"; then
            log_info "Using existing frontend repository"
        else
            log_error "Please choose a different project name or delete the existing repository"
            exit 1
        fi
    else
    log_info "Creating frontend repository: $FRONTEND_REPO"
    if gh repo create "$FRONTEND_REPO" --public --description "Frontend for $PROJECT_NAME (Baukasten/Astro)" --confirm; then
        log_success "Frontend repository created"
    else
            log_error "Failed to create frontend repository"
            exit 1
        fi
    fi

    # Check if CMS repository already exists
    if gh repo view "$github_user/$CMS_REPO" &> /dev/null; then
        log_warning "CMS repository $CMS_REPO already exists"
        if confirm "Do you want to use the existing repository?"; then
            log_info "Using existing CMS repository"
        else
            log_error "Please choose a different project name or delete the existing repository"
            exit 1
        fi
    else
    log_info "Creating CMS repository: $CMS_REPO"
    if gh repo create "$CMS_REPO" --public --description "CMS for $PROJECT_NAME (Baukasten/Kirby)" --confirm; then
        log_success "CMS repository created"
    else
            log_error "Failed to create CMS repository"
            exit 1
        fi
    fi

    log_success "GitHub repositories configured"
    log_info "Frontend: https://github.com/$github_user/$FRONTEND_REPO"
    log_info "CMS: https://github.com/$github_user/$CMS_REPO"
}

clone_and_setup_repos() {
    log_step "Cloning and setting up repositories"

    local github_user=$(gh api user --jq '.login')
    local work_dir="$HOME/Sites" # Adjust this as needed

    # Create work directory if it doesn't exist
    mkdir -p "$work_dir"
    cd "$work_dir"

    # Clone and setup frontend
    if [ ! -d "$FRONTEND_REPO" ]; then
        log_info "Setting up frontend from Baukasten template..."
        if [ -d "baukasten" ]; then
            cp -R baukasten "$FRONTEND_REPO"
            cd "$FRONTEND_REPO"
            rm -rf .git
            git init
            git remote add origin "https://github.com/$github_user/$FRONTEND_REPO.git"
        else
            log_error "Baukasten template not found. Please ensure you're running this from the baukasten directory."
            exit 1
        fi
    else
        log_warning "Frontend directory $FRONTEND_REPO already exists"
        if confirm "Do you want to use the existing directory?"; then
        cd "$FRONTEND_REPO"
            log_info "Using existing frontend directory"
        else
            log_error "Please choose a different project name or remove the existing directory"
            exit 1
        fi
    fi

    # Setup frontend environment
    log_info "Setting up frontend environment..."
    cat > .env << EOF
DEBUG_MODE=false
KIRBY_URL=https://$CMS_DOMAIN
NETLIFY_URL=https://$DOMAIN_NAME
EOF

    # Initial commit for frontend (only if there are changes)
    git add .
    if git diff --staged --quiet; then
        log_info "No changes to commit in frontend"
    else
    git commit -m "Initial commit: Baukasten frontend setup"
    fi
    git branch -M main
    git push -u origin main || log_warning "Push failed - repository might already be up to date"

    cd "$work_dir"

    # Clone and setup CMS
    if [ ! -d "$CMS_REPO" ]; then
        log_info "Setting up CMS from Baukasten template..."
        if [ -d "cms.baukasten" ]; then
            cp -R cms.baukasten "$CMS_REPO"
            cd "$CMS_REPO"
            rm -rf .git
            git init
            git remote add origin "https://github.com/$github_user/$CMS_REPO.git"
        else
            log_error "Baukasten CMS template not found."
            exit 1
        fi
    else
        log_warning "CMS directory $CMS_REPO already exists"
        if confirm "Do you want to use the existing directory?"; then
        cd "$CMS_REPO"
            log_info "Using existing CMS directory"
        else
            log_error "Please choose a different project name or remove the existing directory"
            exit 1
        fi
    fi

    # We'll set up CMS .env after we get the deploy hook

    # Initial commit for CMS (only if there are changes)
    git add .
    if git diff --staged --quiet; then
        log_info "No changes to commit in CMS"
    else
    git commit -m "Initial commit: Baukasten CMS setup"
    fi
    git branch -M main
    git push -u origin main || log_warning "Push failed - repository might already be up to date"

    log_success "Repositories cloned and configured"
}

# =============================================================================
# NETLIFY SETUP
# =============================================================================

setup_netlify_sites() {
    log_step "Setting up Netlify sites"

    local github_user=$(gh api user --jq '.login')
    local work_dir="$HOME/Sites"

    # Setup frontend site (always on Netlify)
    cd "$work_dir/$FRONTEND_REPO"

    # Check if a Netlify site with this name already exists
    local existing_site=$(netlify api listSites | jq -r ".[] | select(.name == \"$PROJECT_NAME\") | .id" 2>/dev/null || echo "")

    if [ -n "$existing_site" ]; then
        log_warning "Netlify site '$PROJECT_NAME' already exists (ID: $existing_site)"
        if confirm "Do you want to use the existing Netlify site?"; then
            log_info "Linking to existing Netlify site..."
            netlify link --id "$existing_site"
            NETLIFY_SITE_ID="$existing_site"
        else
            log_error "Please choose a different project name or delete the existing Netlify site"
            exit 1
        fi
    else
        # Unlink from template site if already linked
        if netlify status &> /dev/null; then
            log_info "Unlinking from template Netlify site..."
            netlify unlink
        fi

    log_info "Creating Netlify site for frontend..."

        # Create site and capture the response
        local create_response=$(netlify sites:create --name "$PROJECT_NAME" --json 2>/dev/null || echo '{"error": "failed"}')
        local site_id=$(echo "$create_response" | jq -r '.site_id // empty' 2>/dev/null)

        if [ -n "$site_id" ] && [ "$site_id" != "null" ]; then
            log_success "Netlify site created with ID: $site_id"
            # Link using the site ID
            netlify link --id "$site_id"
            NETLIFY_SITE_ID="$site_id"
        else
            log_warning "Automatic site creation failed, using interactive setup..."
            # Fallback to interactive setup
            netlify init
            # Get site ID from the linked site
            NETLIFY_SITE_ID=$(cat .netlify/state.json 2>/dev/null | jq -r '.siteId // empty' || echo "")
            if [ -z "$NETLIFY_SITE_ID" ] || [ "$NETLIFY_SITE_ID" = "null" ]; then
                # Try to get from status command
                NETLIFY_SITE_ID=$(netlify status --json 2>/dev/null | jq -r '.site.id // .siteId // empty' || echo "")
                if [ -z "$NETLIFY_SITE_ID" ] || [ "$NETLIFY_SITE_ID" = "null" ]; then
                    log_error "Failed to get site ID after interactive setup"
                    echo "Please check if the site was created successfully and try again"
                    echo "You may need to manually link the site with: netlify link"
                    exit 1
                fi
            fi
        fi
    fi

    log_info "Configuring frontend build settings..."
    netlify api updateSite --data="{
        \"site_id\": \"$NETLIFY_SITE_ID\",
        \"build_settings\": {
            \"cmd\": \"astro build\",
            \"dir\": \"dist\",
            \"env\": {
                \"NODE_VERSION\": \"20\",
                \"KIRBY_URL\": \"https://$CMS_DOMAIN\"
            }
        },
        \"repo\": {
            \"provider\": \"github\",
            \"repo\": \"$github_user/$FRONTEND_REPO\",
            \"branch\": \"main\"
        }
    }"

        # CMS hosting is always manual (PHP hosting required)
        log_success "Netlify frontend site created and configured"
        log_info "Frontend site: https://app.netlify.com/sites/$NETLIFY_SITE_ID"
    log_warning "CMS hosting on $CMS_HOSTING - manual deployment required (PHP hosting needed)"
}

setup_deploy_hooks() {
    log_step "Setting up deploy hooks"

    local work_dir="$HOME/Sites"

    # Create deploy hook for frontend
    cd "$work_dir/$FRONTEND_REPO"

    log_info "Creating deploy hook for frontend..."
    # Use netlify sites:list to get site details which includes deploy hooks
    local site_info=$(netlify api listSites --data="{}" | jq -r ".[] | select(.id == \"$NETLIFY_SITE_ID\")")

    # Create a generic webhook URL for this site
    DEPLOY_HOOK_URL="https://api.netlify.com/build_hooks/${NETLIFY_SITE_ID}"

    # Try to create a build hook via the build_hooks API endpoint
    local hook_response=$(netlify api createSiteBuildHook --data="{\"site_id\": \"$NETLIFY_SITE_ID\", \"title\": \"CMS Content Update\"}" 2>/dev/null || echo '{"url": ""}')
    local created_hook_url=$(echo "$hook_response" | jq -r '.url // empty')

    if [ -n "$created_hook_url" ]; then
        DEPLOY_HOOK_URL="$created_hook_url"
        log_success "Deploy hook created: $DEPLOY_HOOK_URL"
    else
        log_warning "Could not create deploy hook via API, using manual webhook URL"
        log_info "You'll need to manually create a build hook in the Netlify dashboard:"
        log_info "  1. Go to: https://app.netlify.com/sites/$NETLIFY_SITE_ID/settings/deploys"
        log_info "  2. Scroll to 'Build hooks' section"
        log_info "  3. Click 'Add build hook'"
        log_info "  4. Name it 'CMS Content Update' and select branch 'main'"
        log_info "  5. Copy the generated URL and update your CMS .env file"
        echo ""
        if ! confirm "Have you created the build hook manually and want to enter the URL?"; then
            log_error "Deploy hook is required for the CMS to trigger frontend rebuilds"
            exit 1
        fi
        DEPLOY_HOOK_URL=$(prompt_input "Enter the build hook URL from Netlify dashboard" "")
        if [ -z "$DEPLOY_HOOK_URL" ]; then
            log_error "Deploy hook URL cannot be empty"
        exit 1
        fi
    fi

    # Now we can set up CMS environment with the deploy hook
    cd "$work_dir/$CMS_REPO"

    log_info "Setting up CMS environment with deploy hook..."
    if [ "$CMS_HOSTING" = "uberspace" ]; then
        cat > .env << EOF
DEPLOY_URL=$DEPLOY_HOOK_URL

# Uberspace deployment information
# Your Uberspace user: $UBERSPACE_USER
# Your Uberspace host: $UBERSPACE_HOST
# Domain: https://$CMS_DOMAIN
#
# To deploy to Uberspace (manual method):
# 1. SSH to your Uberspace: ssh $UBERSPACE_USER@$UBERSPACE_HOST
# 2. Clone this repo: git clone https://github.com/$(gh api user --jq '.login')/$CMS_REPO.git \$HOME/html/cms.$PROJECT_NAME
# 3. Configure domain if needed: uberspace web domain add $CMS_DOMAIN
#
# For GitHub Actions deployment, add these secrets to your repository:
# - UBERSPACE_HOST: $UBERSPACE_HOST
# - UBERSPACE_USER: $UBERSPACE_USER
# - UBERSPACE_PATH: (your domain or subdirectory if needed)
# - DEPLOY_KEY_PRIVATE: (your SSH private key for Uberspace)
# - DEPLOY_URL: $DEPLOY_HOOK_URL
EOF
    else
        cat > .env << EOF
DEPLOY_URL=$DEPLOY_HOOK_URL
EOF
    fi

    # Commit the CMS env file (only if not ignored)
    if git check-ignore .env >/dev/null 2>&1; then
        log_warning ".env file is ignored by .gitignore (this is correct for security)"
        log_info "Deploy hook URL: $DEPLOY_HOOK_URL"
        log_info "The .env file has been created locally with the deploy hook configuration"
    else
    git add .env
        if git diff --staged --quiet; then
            log_info "No changes to .env file"
        else
    git commit -m "Add deploy hook configuration"
    git push
        fi
    fi

    log_success "Deploy hook configured: $DEPLOY_HOOK_URL"
}

# =============================================================================
# GITHUB SECRETS SETUP
# =============================================================================

setup_github_secrets() {
    log_step "Setting up GitHub secrets"

    local github_user=$(gh api user --jq '.login')

    # Set secrets for CMS repository (Uberspace deployment)
    log_info "Setting up secrets for CMS repository (Uberspace deployment)..."
    gh secret set DEPLOY_URL --body "$DEPLOY_HOOK_URL" --repo "$github_user/$CMS_REPO"

    if [ "$CMS_HOSTING" = "uberspace" ]; then
        gh secret set UBERSPACE_HOST --body "$UBERSPACE_HOST" --repo "$github_user/$CMS_REPO"
        gh secret set UBERSPACE_USER --body "$UBERSPACE_USER" --repo "$github_user/$CMS_REPO"
        gh secret set UBERSPACE_PATH --body "html/cms.$PROJECT_NAME" --repo "$github_user/$CMS_REPO"

        # Automated SSH key setup
        setup_ssh_deployment_key "$github_user" "$CMS_REPO"
    fi

    # Set secrets for frontend repository (Netlify deployment)
    log_info "Setting up secrets for frontend repository (Netlify deployment)..."
    gh secret set KIRBY_URL --body "https://$CMS_DOMAIN" --repo "$github_user/$FRONTEND_REPO"

    log_success "GitHub secrets configured"
    log_info "CMS repo secrets: DEPLOY_URL + Uberspace deployment secrets"
    log_info "Frontend repo secrets: KIRBY_URL (for content fetching)"
}

# =============================================================================
# SSH KEY SETUP FOR AUTOMATED DEPLOYMENT
# =============================================================================

setup_ssh_deployment_key() {
    local github_user="$1"
    local cms_repo="$2"

    log_info "Setting up SSH key for automated deployment..."

    # Create temporary SSH key for deployment
    local temp_key="$HOME/.ssh/uberspace_deploy_$(date +%s)"

    log_info "Generating SSH key pair..."
    ssh-keygen -t ed25519 -f "$temp_key" -N "" -C "github-actions-deploy-$cms_repo" >/dev/null 2>&1

    if [ ! -f "$temp_key" ]; then
        log_error "Failed to generate SSH key"
        return 1
    fi

    log_info "Adding public key to Uberspace authorized_keys..."

    # Add public key to Uberspace
    if ssh-copy-id -i "${temp_key}.pub" "$UBERSPACE_USER@$UBERSPACE_HOST" >/dev/null 2>&1; then
        log_success "Public key added to Uberspace"
    else
        log_warning "Failed to automatically add public key to Uberspace"
        echo ""
        echo "Please manually add this public key to your Uberspace:"
        echo "Public key content:"
        cat "${temp_key}.pub"
        echo ""
        echo "Commands to run on Uberspace:"
        echo "ssh $UBERSPACE_USER@$UBERSPACE_HOST"
        echo "echo '$(cat "${temp_key}.pub")' >> ~/.ssh/authorized_keys"
        echo ""

        if ! confirm "Have you added the public key to Uberspace?"; then
            log_error "SSH key setup cancelled"
            rm -f "$temp_key" "${temp_key}.pub"
            return 1
        fi
    fi

    log_info "Adding private key to GitHub secrets..."

    # Add private key to GitHub secrets
    if gh secret set DEPLOY_KEY_PRIVATE --body "$(cat "$temp_key")" --repo "$github_user/$cms_repo" >/dev/null 2>&1; then
        log_success "Private key added to GitHub secrets"
    else
        log_error "Failed to add private key to GitHub secrets"
        echo "Please manually add the private key:"
        echo "gh secret set DEPLOY_KEY_PRIVATE --body \"\$(cat $temp_key)\" --repo \"$github_user/$cms_repo\""
        return 1
    fi

    # Clean up temporary files
    rm -f "$temp_key" "${temp_key}.pub"

    log_success "SSH deployment key configured successfully!"
}

# =============================================================================
# GITHUB ACTIONS SETUP
# =============================================================================

setup_github_actions() {
    log_step "Setting up GitHub Actions workflows"

    local work_dir="$HOME/Sites"
    local github_user=$(gh api user --jq '.login')

    # Setup GitHub Actions for CMS deployment to Uberspace
    cd "$work_dir/$CMS_REPO"

    log_info "Creating GitHub Actions workflow for CMS deployment..."
    mkdir -p .github/workflows

    if [ "$CMS_HOSTING" = "uberspace" ]; then
        cat > .github/workflows/deploy.yml << 'EOF'
name: Deploy to Uberspace

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]
  workflow_dispatch:

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
    - name: Checkout code
      uses: actions/checkout@v4

    - name: Setup SSH key
      run: |
        mkdir -p ~/.ssh
        echo "${{ secrets.DEPLOY_KEY_PRIVATE }}" > ~/.ssh/uberspace_deploy
        chmod 600 ~/.ssh/uberspace_deploy
        ssh-keyscan -H ${{ secrets.UBERSPACE_HOST }} >> ~/.ssh/known_hosts

    - name: Deploy to Uberspace
      run: |
        # Create target directory if it doesn't exist
        ssh -i ~/.ssh/uberspace_deploy -o StrictHostKeyChecking=no \
          ${{ secrets.UBERSPACE_USER }}@${{ secrets.UBERSPACE_HOST }} \
          "mkdir -p ${{ secrets.UBERSPACE_PATH }}"

        # Sync files to Uberspace subdirectory
        rsync -avz --delete \
          -e "ssh -i ~/.ssh/uberspace_deploy -o StrictHostKeyChecking=no" \
          ./ ${{ secrets.UBERSPACE_USER }}@${{ secrets.UBERSPACE_HOST }}:${{ secrets.UBERSPACE_PATH }}/

    - name: Trigger frontend rebuild
      if: github.ref == 'refs/heads/main'
      run: |
        curl -X POST ${{ secrets.DEPLOY_URL }}

    - name: Cleanup SSH key
      if: always()
      run: |
        rm -f ~/.ssh/uberspace_deploy
EOF
    else
        cat > .github/workflows/deploy.yml << 'EOF'
name: Deploy CMS

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]
  workflow_dispatch:

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
    - name: Checkout code
      uses: actions/checkout@v4

    - name: Trigger frontend rebuild
      if: github.ref == 'refs/heads/main'
      run: |
        curl -X POST ${{ secrets.DEPLOY_URL }}

    - name: Deploy notification
      run: |
        echo "CMS content updated. Manual deployment to your PHP hosting required."
        echo "Deploy this repository to your PHP hosting provider to apply changes."
EOF
    fi

    log_success "GitHub Actions workflow created"

    # Commit and push the workflow
    git add .github/workflows/deploy.yml
    if git diff --staged --quiet; then
        log_info "No changes to workflow file"
    else
        git commit -m "Add GitHub Actions deployment workflow"
        git push
        log_success "Deployment workflow pushed to GitHub"
    fi

    log_info "GitHub Actions workflow: https://github.com/$github_user/$CMS_REPO/actions"
}

# =============================================================================
# DOMAIN SETUP
# =============================================================================

setup_domains() {
    log_step "Domain setup assistance"

    echo "Domain setup requires manual configuration:"
    echo ""
    echo "1. Frontend domain ($DOMAIN_NAME):"
    echo "   - If using a custom domain, add these DNS records:"
    echo "     CNAME: $DOMAIN_NAME -> ${PROJECT_NAME}.netlify.app"
    echo "   - Then add the domain in Netlify: https://app.netlify.com/sites/$NETLIFY_SITE_ID/settings/domain"
    echo ""

    case "$CMS_HOSTING" in
        "uberspace")
            echo "2. CMS domain ($CMS_DOMAIN):"
            echo "   - SSH to your Uberspace: ssh $UBERSPACE_USER@$UBERSPACE_HOST"
            echo "   - If using a custom domain, add it: uberspace web domain add $CMS_DOMAIN"
            echo "   - Configure DNS for custom domain:"
            echo "     A: $CMS_DOMAIN -> [Uberspace IP - check uberspace web domain list]"
            echo "   - For .uber.space subdomain, no DNS setup needed"
            ;;
        "custom")
            echo "2. CMS domain ($CMS_DOMAIN):"
            echo "   - Configure DNS to point to your PHP hosting provider"
            echo "   - Follow your hosting provider's documentation"
            echo "   - Ensure PHP 8.1+ and required extensions are available"
            ;;
    esac

    echo ""
    echo "3. For .netlify.app domains, no additional setup is required."
    echo ""

    if confirm "Have you configured your custom domains (if applicable)?"; then
        log_success "Domain configuration noted"
    else
        log_warning "Remember to configure domains before going live"
    fi
}

# =============================================================================
# TESTING AND VALIDATION
# =============================================================================

test_setup() {
    log_step "Testing the setup"

    local work_dir="$HOME/Sites"

    # Test frontend deployment
    cd "$work_dir/$FRONTEND_REPO"
    log_info "Triggering frontend deployment..."
    git commit --allow-empty -m "Test deployment" || log_warning "Empty commit failed - repository might be up to date"
    git push || log_warning "Push failed - repository might already be up to date"

    # Test CMS deployment
    cd "$work_dir/$CMS_REPO"
    log_info "Triggering CMS deployment..."
    git commit --allow-empty -m "Test deployment" || log_warning "Empty commit failed - repository might be up to date"
    git push || log_warning "Push failed - repository might already be up to date"

    echo ""
    log_info "Deployment triggered. Check the following:"
    echo "  - Frontend: https://app.netlify.com/sites/$NETLIFY_SITE_ID/deploys"
    echo "  - CMS: https://github.com/$(gh api user --jq '.login')/$CMS_REPO/actions"
    echo ""
    log_info "Once deployed, your sites will be available at:"
    echo "  - Frontend: https://$DOMAIN_NAME"
    echo "  - CMS Panel: https://$CMS_DOMAIN/panel"
    echo ""
}

# =============================================================================
# FINAL SETUP STEPS
# =============================================================================

final_steps() {
    log_step "Final setup steps"

    echo "🎉 Your Baukasten project is almost ready!"
    echo ""
    echo "Next steps:"
    echo ""

    case "$CMS_HOSTING" in
        "uberspace")
            echo "1. Wait for frontend deployment to complete (check Netlify dashboard)"
            echo "2. Deploy CMS to Uberspace:"
            echo "   ssh $UBERSPACE_USER@$UBERSPACE_HOST"
            echo "   git clone https://github.com/$(gh api user --jq '.login')/$CMS_REPO.git \$HOME/html/cms.$PROJECT_NAME"
            echo "   # Configure domain if needed: uberspace web domain add $CMS_DOMAIN"
            echo "3. Set up GitHub Actions for automatic deployment (recommended):"
            echo "   - Add repository secrets (see .env file for details)"
            echo "   - GitHub Actions will deploy on every push to main branch"
            echo "4. Access your CMS at: https://$CMS_DOMAIN/panel"
            echo "5. Create your admin user account"
            echo "6. Add some content"
            echo "7. Test that content changes trigger frontend rebuilds"
            echo ""
            echo "Useful commands:"
            echo "  - Manual deploy: ssh $UBERSPACE_USER@$UBERSPACE_HOST 'cd \$HOME/html/cms.$PROJECT_NAME && git pull'"
            echo "  - Check deployment: ssh $UBERSPACE_USER@$UBERSPACE_HOST 'ls -la \$HOME/html/cms.$PROJECT_NAME'"
            ;;
        "custom")
            echo "1. Wait for frontend deployment to complete (check Netlify dashboard)"
            echo "2. Deploy CMS to your PHP hosting provider manually"
            echo "3. Ensure PHP 8.1+ and required extensions (GD, mbstring, etc.) are available"
            echo "4. Access your CMS at: https://$CMS_DOMAIN/panel"
            echo "5. Create your admin user account"
            echo "6. Add some content"
            echo "7. Test that content changes trigger frontend rebuilds"
            echo ""
            echo "Useful commands:"
            echo "  - Deploy CMS: Follow your hosting provider's deployment process"
            ;;
    esac

    echo ""
    echo "Project locations:"
    echo "  - Frontend: $HOME/Sites/$FRONTEND_REPO"
    echo "  - CMS: $HOME/Sites/$CMS_REPO"
    echo ""
    echo "Common commands:"
    echo "  - Local frontend dev: cd $HOME/Sites/$FRONTEND_REPO && npm run dev"
    echo "  - Deploy frontend: cd $HOME/Sites/$FRONTEND_REPO && git push"
    echo ""
    echo "Need help? Check the documentation:"
    echo "  - Frontend: $HOME/Sites/$FRONTEND_REPO/docs/"
    echo "  - CMS: $HOME/Sites/$CMS_REPO/docs/"
    echo ""

    if [ "$CMS_HOSTING" = "uberspace" ]; then
        echo "Uberspace specific notes:"
        echo "  - Your CMS repository includes deployment instructions in .env"
        echo "  - Use 'uberspace web log' to check for errors"
        echo "  - PHP errors: 'tail -f ~/logs/error_log'"
        echo "  - Uberspace documentation: https://manual.uberspace.de/"
        echo ""
    fi

    log_success "Setup complete! 🚀"
}

# =============================================================================
# MAIN EXECUTION
# =============================================================================

main() {
    echo -e "${BOLD}${BLUE}"
    echo "╔══════════════════════════════════════════════════════════════════════════════╗"
    echo "║                     BAUKASTEN PROJECT SETUP SCRIPT                          ║"
    echo "║                                                                              ║"
    echo "║  This script will help you set up a complete Baukasten project with:       ║"
    echo "║  • GitHub repositories for frontend and CMS                                 ║"
    echo "║  • Netlify sites with automatic deployment                                  ║"
    echo "║  • Environment variables and secrets                                        ║"
    echo "║  • Deploy hooks for content-driven rebuilds                                 ║"
    echo "╚══════════════════════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
    echo ""

    if ! confirm "Ready to start the setup?"; then
        echo "Setup cancelled. Run the script again when you're ready!"
        exit 0
    fi

    check_prerequisites
    gather_project_info
    setup_github_repos
    clone_and_setup_repos
    setup_netlify_sites
    setup_deploy_hooks
    setup_github_secrets
    setup_github_actions
    setup_domains
    test_setup
    final_steps
}

# Run main function if script is executed directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi