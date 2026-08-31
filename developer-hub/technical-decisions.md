# Vava Living — Technical Decisions

Record decisions that affect maintainability, architecture, deployment, or product behavior.

## Decision template

### ADR-VAVA-XXX — Decision title
- **Date:** YYYY-MM-DD
- **Status:** Proposed / Accepted / Superseded
- **Context:**
- **Decision:**
- **Reasoning:**
- **Consequences:**
- **Related VAVA tasks:**

---

### ADR-VAVA-001 — Keep custom development isolated from WordPress core
- **Date:** 2026-08-31
- **Status:** Accepted
- **Context:** The repository contains a full WordPress installation and a Vava-specific custom theme.
- **Decision:** New Vava application/UI work should normally be implemented in `wp-content/themes/vava-living-theme-ar-v1`, custom plugins, MU plugins, or controlled configuration instead of editing WordPress core files.
- **Reasoning:** This reduces upgrade risk, makes patches easier to review, and keeps Vava-specific code identifiable.
- **Consequences:** Any exception that modifies WordPress core should be explicitly documented in the task and release log.
- **Related VAVA tasks:** VAVA-001
