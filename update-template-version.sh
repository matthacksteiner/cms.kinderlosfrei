#!/bin/sh

# Function to get the latest release tag from the template repository
get_template_release() {
    git ls-remote --tags template | grep -v '{}' | cut -d'/' -f3 | sort -V | tail -n1
}

# Check if template remote exists
if ! git remote | grep -q "^template$"; then
    echo "Adding template remote..."
    git remote add template https://github.com/matthacksteiner/cms.baukasten
fi

# Fetch all remotes including tags
echo "Fetching updates from template..."
git fetch --all --tags

# Check if we're in the middle of a merge
if [ -f .git/MERGE_HEAD ]; then
    echo "Detected an ongoing merge. Please complete the merge first:"
    echo "1. Resolve any conflicts"
    echo "2. git add . "
    echo "3. git commit"
    exit 1
fi

# Merge template changes
echo "Merging template changes..."
if ! git merge template/main --allow-unrelated-histories; then
    echo "Error: Merge failed. Please resolve conflicts manually."
    echo "After resolving conflicts:"
    echo "1. git add ."
    echo "2. git commit"
    echo "3. Run this script again"
    exit 1
fi

# Get the latest release version
TEMPLATE_VERSION=$(get_template_release)

if [ -z "$TEMPLATE_VERSION" ]; then
    echo "Warning: Could not determine template version"
    exit 0
fi

# Update README.md with the new version
echo "Updating README.md with template version ${TEMPLATE_VERSION}..."

# Create a temporary file
tmp_file=$(mktemp)

# Use awk with a more specific pattern matching
awk -v ver="$TEMPLATE_VERSION" '
    {
        # Only replace the first occurrence of **Template Release:** that is at the start of a line
        if ($0 ~ /^[[:space:]]*\*\*Template Release:\*\*/) {
            print "**Template Release:** " ver
        } else {
            print $0
        }
    }' README.md > "$tmp_file"

# Copy the temp file back to README.md
cp "$tmp_file" README.md
rm "$tmp_file"

echo "Done! README.md has been updated."