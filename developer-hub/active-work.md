# Vava Living — Active Work

Current implementation work for the Vava Living WordPress project.

## Current sprint

### VAVA-001 — Developer Hub foundation
- **Status:** In Progress
- **Priority:** P1
- **Branch:** `feature/vava-developer-hub`
- **Scope:** Development workflow and documentation
- **Repository:** `Vava-living-website`
- **Primary application area:** WordPress custom theme `wp-content/themes/vava-living-theme-ar-v1`
- **Patch repository:** `Vava-living-website-patches`

### Definition of Done
- [x] Hub root created
- [x] Backlog structure created
- [x] Active-work tracker created
- [ ] Release log created
- [ ] Technical decision log created
- [ ] GitHub issue templates created
- [ ] Pull request opened
- [ ] Merge to `main`

## Work rules
1. One task = one VAVA ID.
2. Development should use a dedicated branch whenever practical.
3. Every code change must link back to its VAVA task.
4. Patch-only delivery must reference the corresponding patch repository artifact.
5. Test before marking Done.
6. Record whether the change is not deployed, staging, or production.
7. Changes to WordPress core files should be avoided unless specifically required; application work should normally stay in custom theme/plugin or controlled configuration areas.
