---
name: update-release-notes
description: |
  Add entries to RELEASE_NOTES.md for significant changes. Use when implementing new features, fixing bugs, or making breaking changes that need to be documented for releases. Follows the established format and categorization.
---

# Update Release Notes

## When to Use

- User says "add to release notes", "document this change", "update changelog"
- After implementing a new feature
- After fixing a significant bug
- When making breaking changes
- Before creating a pull request for major changes
- When preparing for a release

## Prerequisites

- RELEASE_NOTES.md file exists in repository root
- Change has been implemented and tested
- Ticket ID available (if applicable)

## Steps

1. **Read current RELEASE_NOTES.md structure**
```bash
head -50 RELEASE_NOTES.md
```
- Expected output: Current release notes format
- Understand version structure and categories
- Note the latest version number

2. **Determine change category**
- Categories typically include:
  - New features
  - Bug fixes
  - Breaking changes
  - Improvements
  - Security updates
  - Deprecations

3. **Format the entry**
```
- [TICKET-ID] Brief description of change
  - Additional details if needed
  - Impact on users or developers
```

4. **Add entry to appropriate section**
- Add under the current development version
- Place in the correct category
- Maintain chronological order (newest first)
- Use consistent formatting with existing entries

5. **Verify formatting**
```bash
head -100 RELEASE_NOTES.md
```
- Expected output: New entry visible and properly formatted
- Check markdown syntax
- Ensure proper indentation

## Output

- RELEASE_NOTES.md updated with new entry
- Change documented for next release
- Proper categorization and formatting
- Ready for PR review

## Examples

**User says:** "Add this bug fix to release notes: Fixed EditInputInPlace validation"

**Result:** 
```markdown
## 8.3.x (unreleased)

### Bug fixes
- [ACO-4088] Fixed EditInputInPlace component validation not triggering on blur
  - Validation now properly executes when field loses focus
  - Prevents invalid data from being saved
```

**User says:** "Document the new React component migration feature"

**Result:**
```markdown
## 8.3.0

### New features
- [ACO-3987] Migrated React app assets to be bundled directly with Drupal module
  - React app now builds to module directory instead of external location
  - Simplifies deployment and version management
  - Breaking change: Update Gulpfile.js target paths in custom implementations
```

**User says:** "This is a breaking change, add it to release notes"

**Result:** Adds entry under "Breaking changes" section with clear migration instructions

**User says:** "Update release notes for the security fix"

**Result:** Adds entry under "Security updates" with appropriate severity indication
