# Site Studio Development Workflow Guide

Complete guide for working with JIRA tasks and implementing features in the cohesion-dev repository.

## Repository Structure

```
cohesion-dev/                  # Site Studio module
├── AGENTS.md                  # Module development workflows
├── apps/                      # React + build system
│   └── AGENTS.md              # React/build workflows
├── cohesion-services/         # Docker services
│   └── AGENTS.md              # Service workflows
├── src/                       # PHP source
├── modules/                   # Drupal sub-modules
├── scss/                      # Stylesheets
└── tests/                     # PHPUnit tests
```

## Starting New Work

### 1. Get JIRA Ticket

**With Atlassian Plugin (Recommended):**
```bash
# In Claude Code
"Look at JIRA-123 and propose implementation plan"
```

**Manual:**
- Review ticket in JIRA
- Note requirements, acceptance criteria
- Check for related tickets

### 2. Create Branch

```bash
git checkout 8.3.x  # or appropriate base branch
git pull origin 8.3.x
git checkout -b feature/JIRA-123-short-description
```

### 3. Plan with Agent

**Enter Planning Mode:**
- Press `Shift+Tab` in Claude Code
- Or type `/clear` to clear context

**Example Planning Prompts:**
```
"Analyze JIRA-123 and propose implementation approach"
"Review existing EditInputInPlace component and suggest improvements"
"What files need to be modified for this feature?"
"Propose test strategy for this change"
```

## Implementation by Type

### React Component (New)

**Location:** `apps/react/src/components/`

**Steps:**
```bash
# 1. Create component structure
cd apps/react/src/components
mkdir MyComponent
cd MyComponent
touch MyComponent.js MyComponent.test.js index.js

# 2. Implement component (use agent)
# "Create React component for user profile with form validation"

# 3. Write tests
# "Generate Jest tests for MyComponent"

# 4. Start watch mode for development
cd ../../../..  # Back to apps/
npm run watch

# 5. Test in another terminal
cd react
npm test -- MyComponent.test.js --watch

# 6. When ready, build for production
cd ..
npm run production-drupal
```

**Agent Skills:**
- `/watch-and-build` - Auto-rebuild during development
- `/test-component` - Run Jest tests

### React Component (Fix Bug)

**Steps:**
```bash
# 1. Locate component
cd apps/react/src/components/ComponentName

# 2. Reproduce with test
cd ../../..  # Back to react/
npm test -- ComponentName.test.js

# 3. Fix issue (use agent)
# "Fix validation bug in ComponentName where..."

# 4. Verify fix
npm test -- ComponentName.test.js

# 5. Rebuild
cd ..
npm run production-drupal
```

### PHP Module Update

**Location:** `src/` or `modules/`

**Steps:**
```bash
# 1. Edit PHP files
cd src

# 2. Implement changes (use agent)
# "Add new service method for user validation"

# 3. Write/update tests
cd ../tests
# Add PHPUnit tests

# 4. Verify changes
# Tests run in parent project environment
```

**Agent Skills:**
- `/run-code-quality` - PHP quality checks
- `/run-cohesion-rebuild` - Rebuild Site Studio

### SCSS Update

**Location:** `scss/`

**Steps:**
```bash
# 1. Edit SCSS files
cd scss

# 2. Compile
cd ..
npm run compile:scss

# 3. Lint and fix
npm run lint:scss:fix
```

**Agent Skills:**
- `/compile-scss` - Compile and lint SCSS

### Docker Service Update

**Location:** `cohesion-services/`

**Steps:**
```bash
# 1. Edit service code
cd cohesion-services/dx8-gateway

# 2. Rebuild service
cd ..
docker-compose up -d --build dx8-gateway

# 3. Check logs
docker-compose logs -f dx8-gateway

# 4. Test endpoint
# Test API functionality
```

**Agent Skills:**
- `/manage-services` - Docker operations

## Testing Strategy

### React Tests (Required)

```bash
cd apps/react

# Run all tests
npm test

# Run specific test
npm test -- ComponentName.test.js

# Watch mode
npm test -- --watch

# Coverage
npm test -- --coverage

# View coverage report
open coverage/lcov-report/index.html
```

### PHP Tests

```bash
# Tests run in parent project environment
# See parent project AGENTS.md for commands
```

## Before Creating PR

### Checklist

- [ ] All tests pass
  ```bash
  cd apps/react && npm test
  ```

- [ ] Code quality passes
  ```bash
  npm run lint:scss:fix  # For SCSS changes
  ```

- [ ] Builds successfully
  ```bash
  cd apps && npm run production
  ```

- [ ] Documentation updated
  - [ ] RELEASE_NOTES.md (if significant change)
  - [ ] CONTRIBUTING.md (if workflow change)
  - [ ] AGENTS.md (if new pattern)

- [ ] No console errors in browser

- [ ] Accessibility checked (if UI change)

### Create PR

**PR Description Template:**
```markdown
## JIRA Ticket
[JIRA-123](link-to-jira)

## Description
Brief description of changes

## Changes Made
- Added new component X
- Fixed bug in Y
- Updated Z

## Testing Instructions
1. Step 1
2. Step 2
3. Expected result

## Screenshots/Videos
[If UI changes]

## Breaking Changes
[If any]

## Checklist
- [x] Tests pass
- [x] Code quality passes
- [x] Builds successfully
- [x] Documentation updated
```

## Agent-Assisted Development

### Effective Prompts

**Planning:**
```
"Look at JIRA-123 and create detailed implementation plan"
"Analyze existing EditInputInPlace component and suggest refactoring"
"What's the best approach to add validation to this form?"
```

**Implementation:**
```
"Create React component for user profile with these fields: [list]"
"Add Jest tests for MyComponent covering these scenarios: [list]"
"Fix bug where component doesn't validate on blur"
"Update SCSS for responsive layout on mobile"
```

**Testing:**
```
"Generate comprehensive Jest tests for this component"
"Create test cases for edge cases in validation"
"Debug why this test is failing"
```

**Documentation:**
```
"Update RELEASE_NOTES.md with this feature"
"Generate PR description for these changes"
"Update AGENTS.md with this new pattern"
```

**Code Review:**
```
"Review this code for accessibility issues"
"Check this component for performance problems"
"Suggest improvements for this implementation"
```

### Using Skills

**Module (cohesion-dev):**
- `/compile-scss` - Compile styles
- `/build-react-app` - Build React
- `/switch-branch` - Branch management
- `/update-release-notes` - Document changes

**Apps:**
- `/watch-and-build` - Auto-rebuild
- `/test-component` - Run tests

**Services:**
- `/manage-services` - Docker operations

## Common Scenarios

### Scenario 1: New Form Component

```bash
# 1. Review JIRA
"Look at JIRA-123 for new form component requirements"

# 2. Create branch
git checkout -b feature/JIRA-123-user-form

# 3. Plan
"Propose implementation for user form with validation"

# 4. Create component
cd apps/react/src/components
mkdir UserForm
# Use agent to generate component and tests

# 5. Develop with watch
cd ../../..
npm run watch

# 6. Test
cd react && npm test -- UserForm.test.js

# 7. Build
cd .. && npm run production-drupal

# 8. Create PR
"Generate PR description for user form feature"
```

### Scenario 2: Fix Validation Bug

```bash
# 1. Review JIRA
"Look at JIRA-456 about validation bug"

# 2. Create branch
git checkout -b bugfix/JIRA-456-validation-fix

# 3. Locate issue
"Find where validation happens in EditInputInPlace"

# 4. Reproduce with test
cd apps/react
npm test -- EditInputInPlace.test.js

# 5. Fix
"Fix validation to trigger on blur event"

# 6. Verify
npm test -- EditInputInPlace.test.js

# 7. Build
cd .. && npm run production-drupal

# 8. Create PR
"Generate PR description for validation fix"
```

### Scenario 3: Update API Service

```bash
# 1. Review JIRA
"Look at JIRA-789 for API update"

# 2. Create branch
git checkout -b feature/JIRA-789-api-endpoint

# 3. Update service
cd cohesion-services/dx8-gateway/node
# Make changes

# 4. Rebuild
cd ..
docker-compose up -d --build dx8-gateway

# 5. Test
docker-compose logs -f dx8-gateway
# Test endpoint

# 6. Create PR
"Generate PR description for API update"
```

## Tips for Success

1. **Always use planning mode** for complex tasks
2. **Write tests first** when fixing bugs
3. **Use watch mode** during active development
4. **Update documentation** as you go
5. **Ask agent for help** when stuck
6. **Review existing patterns** before implementing
7. **Keep PRs focused** on single issue
8. **Include testing instructions** in PR
9. **Update RELEASE_NOTES.md** for significant changes

## Troubleshooting

### Build Fails
```bash
# Clean and rebuild
rm -rf apps/react/.tmp
cd apps && npm run production
```

### Tests Fail
```bash
# Check for missing dependencies
cd apps/react && npm install
# Run specific test
npm test -- ComponentName.test.js
```

### Services Won't Start
```bash
cd cohesion-services
docker-compose down
docker-compose up -d --build
docker-compose logs -f
```

---

**Remember:** Use agent skills and planning mode to maximize productivity!
