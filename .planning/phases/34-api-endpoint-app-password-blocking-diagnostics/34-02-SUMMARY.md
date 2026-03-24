---
phase: 34-api-endpoint-app-password-blocking-diagnostics
plan: "02"
subsystem: admin-ui
tags: [diagnostics, site-health, activation, settings, builder-guide]
dependency_graph:
  requires: [34-01]
  provides: [diagnostic-ui, site-health-checks, activation-checks, troubleshooting-docs]
  affects: [includes/Admin/Settings.php, includes/Admin/SiteHealth.php, includes/Activator.php, includes/Plugin.php, docs/BUILDER_GUIDE.md, includes/MCP/Router.php]
tech_stack:
  added: []
  patterns: [wp-ajax, site-health-api, transient-cache, inline-js]
key_files:
  created:
    - includes/Admin/SiteHealth.php
  modified:
    - includes/Admin/Settings.php
    - includes/Activator.php
    - includes/Plugin.php
    - docs/BUILDER_GUIDE.md
    - includes/MCP/Router.php
decisions:
  - Test Connection removed per D-07; DiagnosticRunner provides richer coverage via McpEndpointCheck
  - Activation checks use pure PHP (no HTTP) per D-17/D-19; transient stored only when issues found
  - SiteHealth initialised unconditionally in init_admin() alongside Settings
  - connection_troubleshooting added to Router section_map so get_builder_guide(section) works
metrics:
  duration: 12min
  completed: "2026-03-24"
  tasks: 2
  files: 6
---

# Phase 34 Plan 02: Diagnostic UI Integration Summary

Wire the diagnostic engine into all user-facing surfaces: admin settings panel (replacing Test Connection), WP Site Health, plugin activation checks, and Builder Guide troubleshooting docs.

## Tasks Completed

| # | Task | Commit | Files |
|---|------|--------|-------|
| 1 | Add diagnostic panel to admin settings page, remove Test Connection (D-07) | dc46ea5 | includes/Admin/Settings.php |
| 2 | Add Site Health integration, activation checks, and Builder Guide troubleshooting | 8d5cac5 | includes/Admin/SiteHealth.php, includes/Activator.php, includes/Plugin.php, docs/BUILDER_GUIDE.md, includes/MCP/Router.php |

## What Was Built

**Settings.php — Diagnostic Panel (replaces Test Connection per D-07):**
- `render_diagnostic_panel()` renders a "System Status" box with Run Diagnostics + Copy Results buttons
- `ajax_run_diagnostics()` AJAX handler instantiates `DiagnosticRunner`, calls `register_defaults()` + `run_all()`, returns structured JSON
- On-success JS renders colored checklist (pass=green, warn=orange, fail=red, skipped=gray) with Dashicons
- Copy Results formats all results as plain text (`[PASS] Label: message\n  Fix: step`) and copies to clipboard
- Activation notice: reads `bricks_mcp_activation_checks` transient on page load, shows dismissible warning if any fails
- `ajax_test_connection()` method and Test Connection HTML block fully removed

**SiteHealth.php — WP Site Health integration:**
- Registers 3 direct tests: `bricks_mcp_rest_api`, `bricks_mcp_app_passwords`, `bricks_mcp_bricks_active`
- Each test delegates to the corresponding check class (`RestApiReachableCheck`, `AppPasswordsAvailableCheck`, `BricksActiveCheck`)
- `format_site_health_result()` maps pass→good, warn→recommended, fail→critical, skipped→recommended
- Each result includes badge `{label: 'Bricks MCP', color: 'blue'}`

**Activator.php — Lightweight activation checks:**
- `run_activation_checks()` runs 3 pure-PHP checks: REST API enabled (via `rest_enabled` filter), App Passwords available, Bricks active via `class_exists('\Bricks\Elements')`
- Results stored as `bricks_mcp_activation_checks` transient with 1h TTL, but only when at least one check fails

**Plugin.php:**
- `init_admin()` now also instantiates and inits `SiteHealth`

**docs/BUILDER_GUIDE.md:**
- New `## Connection Troubleshooting` section with 6 subsections: HTTPS, Application Passwords disabled, REST API blocked (per-plugin fixes), Permalink structure, Hosting-specific issues (WP Engine, Kinsta, Flywheel, Pantheon, Cloudflare), Full diagnostics call

**includes/MCP/Router.php:**
- `connection_troubleshooting` added to `section_map` pointing to `## Connection Troubleshooting`

## Decisions Made

- Test Connection removed; its functionality is superseded by McpEndpointCheck in the diagnostic suite (D-07)
- Activation checks are intentionally limited to pure-PHP checks — no HTTP loopback on activation (D-17/D-19)
- Transient only stored when there are failures, keeping activation fast on healthy sites
- SiteHealth initialized inside `is_admin()` block in Plugin.php, same context as Settings

## Deviations from Plan

**1. [Rule 3 - Blocking] Worktree missing Plan 01 files**
- Found during: Task 1 start
- Issue: Worktree branch was created before Plan 01 commits landed on main
- Fix: `git rebase main` in the worktree to pull in all Plan 01 commits (DiagnosticRunner, Checks, etc.)
- Files modified: None (rebase only)

**2. [Rule 2 - Missing functionality] Router section_map lacked connection_troubleshooting**
- Found during: Task 2
- Issue: Plan specified adding `connection_troubleshooting` to section_map but the map was not mentioned in task files list; added Router.php to the commit
- Fix: Added entry to section_map in Router.php
- Files modified: includes/MCP/Router.php

## Known Stubs

None — all functionality is fully wired.

## Self-Check: PASSED

Files confirmed present:
- includes/Admin/SiteHealth.php: EXISTS
- includes/Admin/Settings.php: contains render_diagnostic_panel, ajax_run_diagnostics, NO ajax_test_connection
- includes/Activator.php: contains run_activation_checks
- includes/Plugin.php: contains SiteHealth init
- docs/BUILDER_GUIDE.md: contains ## Connection Troubleshooting

Commits confirmed:
- dc46ea5: Task 1 — Settings.php diagnostic panel
- 8d5cac5: Task 2 — SiteHealth, Activator, Plugin, BUILDER_GUIDE, Router
