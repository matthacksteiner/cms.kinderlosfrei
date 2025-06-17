#!/bin/bash

# init-project.sh
#
# This script removes template maintenance files that are not needed
# in child Kirby CMS repositories. Run this script once after creating
# a new project from the Baukasten CMS template.

echo "Initializing Kirby CMS project by removing template maintenance files..."

# Read files to remove from .templateignore
if [ -f ".templateignore" ]; then
  echo "Reading template files to remove from .templateignore..."

  while IFS= read -r line; do
    # Skip comments and empty lines
    if [[ "$line" =~ ^[[:space:]]*# ]] || [[ -z "$line" ]]; then
      continue
    fi

    # Remove leading/trailing whitespace
    file=$(echo "$line" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')

    if [ -f "$file" ]; then
      rm "$file"
      echo "✓ Removed $file"
    elif [ -d "$file" ]; then
      rm -rf "$file"
      echo "✓ Removed directory $file"
    else
      echo "✗ $file not found"
    fi
  done < .templateignore

  # Remove the .templateignore file itself
  if [ -f ".templateignore" ]; then
    rm ".templateignore"
    echo "✓ Removed .templateignore"
  fi
else
  echo "Warning: .templateignore not found, falling back to hardcoded file list"

  # Fallback to hardcoded files for backwards compatibility

  if [ -f .github/workflows/update-child-repos.yml ]; then
    rm .github/workflows/update-child-repos.yml
    echo "✓ Removed update-child-repos.yml"
  else
    echo "✗ update-child-repos.yml not found"
  fi

  if [ -f .github/child-repositories.json ]; then
    rm .github/child-repositories.json
    echo "✓ Removed child-repositories.json"
  else
    echo "✗ child-repositories.json not found"
  fi

  # Note: deploy.yml is kept as it's a useful template for GitHub Actions deployment
fi

# Setup default content if available
if [ -f "baukasten-default-content.zip" ]; then
  echo "Found baukasten-default-content.zip, setting up default content..."

  # Check if unzip is available
  if ! command -v unzip &> /dev/null; then
    echo "⚠️  Warning: unzip command not found. Please install unzip to extract default content."
    echo "Default content setup skipped."
  else
    # Backup existing content if present
    if [ -d "content" ]; then
      backup_name="content.backup.$(date +%s)"
      mv content "$backup_name"
      echo "✓ Backed up existing content to $backup_name"
    fi

    # Backup existing languages if present
    if [ -d "site/languages" ]; then
      backup_name="site/languages.backup.$(date +%s)"
      mv site/languages "$backup_name"
      echo "✓ Backed up existing languages to $backup_name"
    fi

    # Extract default content
    echo "Extracting default content..."
    if unzip -o -q baukasten-default-content.zip; then
      echo "✓ Default content extracted successfully"

      # Handle nested directory structures - look for content folder and move to root if needed
      content_dir=$(find . -name "content" -type d -not -path "./content" | head -1)
      if [ -n "$content_dir" ] && [ "$content_dir" != "./content" ]; then
        echo "Moving content from $content_dir to root..."
        mv "$content_dir" ./content
        echo "✓ Content moved to root directory"
      fi

      # Handle nested directory structures - look for languages folder and move to site/ if needed
      languages_dir=$(find . -name "languages" -type d -not -path "./site/languages" | head -1)
      if [ -n "$languages_dir" ] && [ "$languages_dir" != "./site/languages" ]; then
        echo "Moving languages from $languages_dir to site/..."
        mkdir -p site/
        mv "$languages_dir" ./site/languages
        echo "✓ Languages moved to site/ directory"
      fi

      # Set proper permissions for content folder
      if [ -d "content" ]; then
        chmod -R 755 content/
        echo "✓ Set permissions for content folder"
      else
        echo "⚠️  Warning: content folder not found after extraction"
      fi

      # Set proper permissions for languages folder
      if [ -d "site/languages" ]; then
        chmod -R 755 site/languages/
        echo "✓ Set permissions for languages folder"
      fi

      # Remove the zip file after successful extraction
      rm baukasten-default-content.zip
      echo "✓ Removed baukasten-default-content.zip"

      echo "✓ Default content setup complete!"
    else
      echo "✗ Failed to extract default content"
      echo "The baukasten-default-content.zip file may be corrupted or invalid."
      echo "Please check the file and try again manually."
    fi
  fi
else
  echo "No baukasten-default-content.zip found, skipping default content setup."
fi

# Ensure default German language file exists
echo "Setting up default language configuration..."
mkdir -p site/languages

if [ ! -f "site/languages/de.php" ]; then
  echo "Creating default German language file..."
  cat > site/languages/de.php << 'EOF'
<?php

return [
    'code' => 'de',
    'default' => true,
    'direction' => 'ltr',
    'locale' => [
        'LC_ALL' => 'de_DE'
    ],
    'name' => 'Deutsch',
    'translations' => [

    ],
    'url' => NULL
];
EOF
  echo "✓ Created default German language file"
else
  echo "✓ German language file already exists"
fi

# Ensure German is set as default language
if [ -f "site/languages/de.php" ]; then
  # Check if German is already set as default
  if ! grep -q "'default' => true" site/languages/de.php; then
    echo "Setting German as default language..."
    # Use sed to update the default setting
    sed -i.bak "s/'default' => false/'default' => true/g" site/languages/de.php
    rm site/languages/de.php.bak 2>/dev/null || true
    echo "✓ German language set as default"
  else
    echo "✓ German is already set as default language"
  fi

  # Ensure other language files have default => false
  for lang_file in site/languages/*.php; do
    if [ "$lang_file" != "site/languages/de.php" ] && [ -f "$lang_file" ]; then
      if grep -q "'default' => true" "$lang_file"; then
        echo "Updating $(basename "$lang_file") to not be default..."
        sed -i.bak "s/'default' => true/'default' => false/g" "$lang_file"
        rm "${lang_file}.bak" 2>/dev/null || true
        echo "✓ Updated $(basename "$lang_file")"
      fi
    fi
  done
fi

echo "✓ Language configuration complete!"

# Create a basic .env file for child repositories
echo "Creating basic .env file for child repository..."
cat > .env << 'EOF'
# Kirby CMS Environment Variables
# Copy this file to .env and configure for your environment

# Get this from your Netlify site settings > Build & deploy > Build hooks
DEPLOY_URL=https://yourdomain.com

EOF
echo "✓ Created .env template"

echo ""
echo "⚠️  Important: Configure your .env file with the correct settings for your environment"
echo ""

# Optional: Remove this script itself
read -p "Do you want to remove this script (init-project.sh) as well? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
  rm -- "$0"
  echo "✓ Removed init-project.sh"
fi

echo "Initialization complete! Your Kirby CMS project is now ready for development."
echo ""
echo "Next steps:"
echo "1. Configure your .env file with the correct settings"
echo "2. Set up your deployment workflow if needed"
echo "3. Configure your content structure in the Panel"