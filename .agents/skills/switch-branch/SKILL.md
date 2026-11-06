---
name: switch-branch
description: |
  Switch between Site Studio version branches (8.2.x, 8.3.x, develop) safely. Use when user needs to work on different versions, test compatibility, or prepare releases. Handles uncommitted changes and dependency updates.
---

# Switch Branch

## When to Use

- User says "switch to 8.3.x", "checkout develop branch", "change to 8.2.x"
- When working on different version releases
- When testing compatibility across versions
- When preparing backports or forward-ports
- When reviewing PRs on different branches

## Prerequisites

- Git repository initialized
- Remote branches available
- Working directory should be clean (or changes stashed)

## Steps

1. **Check current branch and status**
```bash
git branch --show-current
git status --short
```
- Expected output: Current branch name and file status
- Shows any uncommitted changes
- If changes exist: Proceed to step 2

2. **Handle uncommitted changes**

If you have uncommitted changes:
```bash
git stash push -m "WIP: switching branches"
```
- Expected output: Changes stashed
- Preserves work in progress
- If error: Commit or discard changes manually

3. **Switch to target branch**
```bash
git checkout <branch-name>
```
- Common branches: `8.2.x`, `8.3.x`, `develop`
- Expected output: "Switched to branch '<branch-name>'"
- If error: Branch may not exist locally, try step 4

4. **If branch doesn't exist locally, fetch and checkout**
```bash
git fetch origin
git checkout -b <branch-name> origin/<branch-name>
```
- Expected output: New branch created tracking remote
- If error: Check remote branch exists with `git branch -r`

5. **Pull latest changes**
```bash
git pull origin <branch-name>
```
- Expected output: "Already up to date" or changes pulled
- Ensures you have latest code
- If error: May have merge conflicts, resolve manually

6. **Update dependencies after branch switch**
```bash
npm install
```
- Expected output: Dependencies updated
- Different branches may have different dependencies
- If error: Try `rm -rf node_modules && npm install`

7. **If in apps directory, update those dependencies too**
```bash
cd apps && npm install && cd ..
```
- Expected output: React app dependencies updated
- Ensures webpack builds work correctly

8. **Restore stashed changes if needed**
```bash
git stash list
git stash pop
```
- Expected output: Changes restored
- Only if you stashed in step 2
- If conflicts: Resolve manually

## Output

- Switched to target branch
- Latest code pulled from remote
- Dependencies updated for branch
- Ready for development or testing
- Stashed changes restored (if applicable)

## Examples

**User says:** "Switch to 8.3.x branch"

**Result:** Checks status, stashes if needed, checks out 8.3.x, pulls latest, updates npm

**User says:** "I need to work on the 8.2.x version"

**Result:** Switches to 8.2.x, updates dependencies, confirms branch switch

**User says:** "Checkout develop and pull latest"

**Result:** Switches to develop, pulls from origin, updates all dependencies

**User says:** "I have uncommitted changes, switch to 8.3.x"

**Result:** Stashes changes, switches branch, offers to restore stash after switch
