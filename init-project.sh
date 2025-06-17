#!/bin/bash

# init-project.sh
#
# This script removes template maintenance files that are not needed
# in child Kirby CMS repositories. Run this script once after creating
# a new project from the Baukasten CMS template.

echo "Initializing Kirby CMS project by removing template maintenance files..."

# FIRST: Setup default content if available (before removing template files)
if [ -f "baukasten-default-content.zip" ]; then
  echo "Found baukasten-default-content.zip, setting up default content..."

  # Check if unzip is available
  if ! command -v unzip &> /dev/null; then
    echo "⚠️  Warning: unzip command not found. Please install unzip to extract default content."
    echo "Default content setup skipped."
  else
    # Remove existing content if present
    if [ -d "content" ]; then
      rm -rf content
      echo "✓ Removed existing content"
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

      # Set proper permissions for content folder
      if [ -d "content" ]; then
        chmod -R 755 content/
        echo "✓ Set permissions for content folder"
      else
        echo "⚠️  Warning: content folder not found after extraction"
      fi

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

# SECOND: Remove template files (including the zip file after it's been used)
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