# Acquia Site Studio (cohesion-dev)

Core Drupal module for Site Studio. Main development repository.

## Tech Stack

Drupal 10/11 module with PHP 8.2+, Node.js 20+

## Repository Structure

```
apps/                     # React app + build (see apps/AGENTS.md)
cohesion-services/        # Docker services (see cohesion-services/AGENTS.md)
src/                      # PHP source
modules/                  # Drupal sub-modules
scss/                     # Stylesheets
tests/                    # PHPUnit tests
```

## Git Workflow

### Branches

- `8.2.x` - Drupal 10 stable
- `8.3.x` - Latest development
- `feature/JIRA-123-description` - Features
- `bugfix/JIRA-123-description` - Bug fixes

### Commands

```bash
# Start work
git checkout 8.3.x
git pull origin 8.3.x
git checkout -b feature/JIRA-123-description

# Switch branches
git checkout 8.3.x

# Keep updated
git fetch origin
git rebase origin/8.3.x

# Stash changes
git stash
git stash pop
```

### Commits

```
JIRA-123: Brief description

Details if needed
```

## Release Process

### Update RELEASE_NOTES.md

```markdown
## 8.3.x (unreleased)

### New features
- [JIRA-123] Feature description

### Bug fixes
- [JIRA-456] Fix description

### Breaking changes
- [JIRA-789] Breaking change + migration
```

## Pull Requests

### Template

```markdown
## JIRA
[JIRA-123](link)

## Description
Summary

## Changes
- Change 1
- Change 2

## Testing
1. Step 1
2. Expected result

## Screenshots
[If UI]

## Breaking Changes
[If any]
```

### Checklist

- [ ] Tests pass
- [ ] Code quality passes
- [ ] Builds successfully
- [ ] Tested in parent project
- [ ] RELEASE_NOTES.md updated
- [ ] Documentation updated

## Development

### Complete Workflow Guide

See [WORKFLOW_GUIDE.md](WORKFLOW_GUIDE.md) for detailed step-by-step instructions on:
- Starting new work with JIRA tickets
- Implementation patterns by type (React, PHP, SCSS, Services)
- Testing strategies
- PR creation checklist
- Agent-assisted development tips
- Common scenarios and troubleshooting

### JIRA Task (Quick Reference)

1. **Get ticket** - `"Look at JIRA-123 and propose plan"`
2. **Branch** - `git checkout -b feature/JIRA-123-description`
3. **Implement** - See apps/AGENTS.md or cohesion-services/AGENTS.md
4. **Update docs** - RELEASE_NOTES.md
5. **Create PR** - Follow template

### Where to Work

- **React/Build** → `apps/AGENTS.md`
- **Services** → `cohesion-services/AGENTS.md`
- **PHP** → Edit `src/` or `modules/`
- **SCSS** → Edit `scss/`, run `npm run compile:scss`

## Repository Tasks

```bash
# SCSS
npm run compile:scss
npm run lint:scss:fix

# Git
git log --oneline -20
git status
git diff
```

## Agent Prompts

- `"Look at JIRA-123 and create plan"`
- `"Update RELEASE_NOTES.md"`
- `"Generate PR description"`
- `"What branch for this work?"`

## Skills

`/switch-branch`, `/update-release-notes`

## Boundaries

Do NOT modify:
- `node_modules/`
- `vendor/`
- `.git/`
