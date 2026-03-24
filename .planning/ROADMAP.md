# Roadmap: Bricks MCP

## Overview

This roadmap delivers a commercial WordPress plugin that turns Bricks Builder into an MCP server, enabling users to build and edit pages conversationally from Claude Code or Gemini. The journey starts with core Bricks CRUD operations and element schema discovery (Phase 1), adds design features and template integration (Phase 2), implements Polar licensing for distribution (Phase 3), and completes with self-hosted updates and onboarding (Phase 4).

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [x] **Phase 1: Core Bricks Integration** - Bricks CRUD operations, element schema discovery, and validation (completed 2026-02-17)
- [x] **Phase 2: Design & Template Features** - Global classes, responsive settings, and template context (completed 2026-02-17)
- [x] **Phase 3: Licensing** - Polar license validation with caching and admin integration (completed 2026-02-17)
- [x] **Phase 4: Updates & Onboarding** - Self-hosted updates and MCP connection instructions (completed 2026-02-17)
- [x] **Phase 5: Error Handling & Code Cleanup** - Fix P0 WP_Error bug, remove dead code, clean unused settings (completed 2026-02-19)
- [x] **Phase 6: Phase 4 Verification & Traceability** - Verify Phase 4 requirements, fix UPDT-03 partial, update REQUIREMENTS.md (completed 2026-02-19)

## Phase Details

### Phase 1: Core Bricks Integration
**Goal**: Users can create, read, update, and delete Bricks page content via MCP tools with validated element structures and schema discovery to prevent AI hallucination
**Depends on**: Nothing (first phase)
**Requirements**: CRUD-01, CRUD-02, CRUD-03, CRUD-04, CRUD-05, CRUD-06, BRKS-01, BRKS-02, BRKS-03, BRKS-04, BRKS-05, SCHM-01, SCHM-02, SCHM-03, STNG-05
**Success Criteria** (what must be TRUE):
  1. User can create a new page with Bricks editing enabled and basic element structure from Claude Code
  2. User can read any existing page's Bricks JSON content and WordPress metadata via MCP tools
  3. User can update Bricks element settings and post metadata, with changes persisting correctly in Bricks editor
  4. User can delete posts/pages and remove individual elements while maintaining parent-child linkage integrity
  5. User can list and search posts by status, type, and content from Claude Code
  6. User can discover available Bricks element types and their valid settings schemas to prevent invalid JSON
  7. Plugin gracefully handles when Bricks Builder is not installed (clear error messages, no fatal errors)
**Plans**: 3 plans

Plans:
- [ ] 01-01-PLAN.md — Services foundation + Bricks read tools (BricksService, ElementIdGenerator, get_bricks_content, list_pages, search_pages)
- [ ] 01-02-PLAN.md — Write tools + ElementNormalizer (create_bricks_page, update_bricks_content, update_page, delete_page, duplicate_page, add/update/remove element)
- [ ] 01-03-PLAN.md — Schema discovery + validation (SchemaGenerator, ValidationService, get_element_schemas, Opis JSON Schema integration)

### Phase 2: Design & Template Features
**Goal**: Users can apply global CSS classes, set responsive breakpoint values, and reference existing Bricks templates for consistent design patterns
**Depends on**: Phase 1
**Requirements**: DSGN-01, DSGN-02, DSGN-03, DSGN-04
**Success Criteria** (what must be TRUE):
  1. User can list all global CSS classes with their styles as reference when building pages
  2. User can apply global classes to elements by name and they render correctly with defined styles
  3. User can set element settings for specific breakpoints (mobile/tablet/desktop) using composite key syntax
  4. User can read existing Bricks template content as style reference for new pages
**Plans**: 2 plans

Plans:
- [ ] 02-01-PLAN.md — Global CSS class tools (get_global_classes, apply_global_class, remove_global_class)
- [ ] 02-02-PLAN.md — Breakpoints + template tools (get_breakpoints, list_templates, get_template_content, schema responsive flags)

### Phase 3: Licensing
**Goal**: Plugin validates Polar license keys with 24-hour caching, blocks MCP tool access when invalid, and provides admin UI for activation
**Depends on**: Phase 2
**Requirements**: LCNS-01, LCNS-02, LCNS-03, LCNS-04, STNG-03
**Success Criteria** (what must be TRUE):
  1. Plugin validates license key against Polar API on activation and caches result for 24 hours
  2. Plugin blocks all MCP tool access when license is invalid, expired, or missing (with clear error message)
  3. User can activate/deactivate license from admin settings page with status feedback
  4. Settings page shows license status, expiration, and activation count clearly
**Plans**: 2 plans

Plans:
- [ ] 03-01-PLAN.md — Polar API client, LicenseManager service, Plugin bootstrap, Router license gating
- [ ] 03-02-PLAN.md — Admin settings license section with AJAX activation/deactivation and status display

### Phase 4: Updates & Onboarding
**Goal**: Plugin checks custom update server for new versions, provides one-click WordPress admin updates, and displays copy-paste MCP configuration for Claude Code and Gemini
**Depends on**: Phase 3
**Requirements**: UPDT-01, UPDT-02, UPDT-03, UPDT-04, STNG-01, STNG-02, STNG-04
**Success Criteria** (what must be TRUE):
  1. Plugin checks GitHub releases for updates on WordPress admin load and shows notification when available
  2. User can update plugin with one click from WordPress admin (standard update flow)
  3. Update server validates license before serving download (license-gated distribution)
  4. Settings page shows ready-to-copy MCP configuration snippets for Claude Code with correct endpoint URLs
  5. Settings page shows ready-to-copy MCP configuration snippets for Gemini with authentication details
  6. Settings page displays current plugin version and update availability status
**Plans**: 3 plans

Plans:
- [ ] 04-01-PLAN.md — UpdateChecker class, Update URI header, Plugin bootstrap (plugin-side update pipeline)
- [ ] 04-02-PLAN.md — Cloudflare Worker update server + GitHub Action release build
- [ ] 04-03-PLAN.md — Settings page: version card, MCP config tabs (Claude Code + Gemini), test connection

### Phase 5: Error Handling & Code Cleanup
**Goal**: Fix P0 WP_Error serialization bug so AI clients receive proper error responses, and clean up dead code and unused settings
**Depends on**: Phase 4
**Requirements**: (none — cross-cutting fix affecting error paths for CRUD-01 through SCHM-03, DSGN-02, DSGN-04)
**Gap Closure**: Closes gaps from audit
**Success Criteria** (what must be TRUE):
  1. Router.execute_tool() returns proper error responses (not `{}`) when tool handlers return WP_Error
  2. Dead code (Response::jsonrpc, Response::jsonrpc_error) is removed
  3. Unused rate limiting settings are removed from settings page and Server::check_permissions()
  4. BRICKS_MCP_PURCHASE_URL points to actual product page or is removed
**Plans**: 1 plan

Plans:
- [ ] 05-01-PLAN.md — WP_Error handling fix, dead code removal, settings cleanup

### Phase 6: Phase 4 Verification & Traceability
**Goal**: Formally verify all Phase 4 requirements, fix UPDT-03 partial implementation, and update REQUIREMENTS.md traceability to reflect all verified work
**Depends on**: Phase 5
**Requirements**: UPDT-01, UPDT-02, UPDT-03, UPDT-04, STNG-01, STNG-02, STNG-04
**Gap Closure**: Closes gaps from audit
**Success Criteria** (what must be TRUE):
  1. Phase 4 VERIFICATION.md exists with all success criteria checked
  2. Phase 4 SUMMARY files have proper YAML frontmatter with requirements-completed
  3. UPDT-03 handles unlicensed users gracefully (clear message instead of silent failure)
  4. All 31 v1 requirements are checked in REQUIREMENTS.md traceability table
  5. REQUIREMENTS.md coverage count reflects actual status
**Plans**: 1 plan

Plans:
- [ ] 06-01-PLAN.md — Phase 4 verification, UPDT-03 fix, REQUIREMENTS.md traceability update

## Progress

**Execution Order:**
Phases execute in numeric order: 1 → 2 → 3 → 4 → 5 → 6

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Core Bricks Integration | 3/3 | Complete | 2026-02-17 |
| 2. Design & Template Features | 2/2 | Complete | 2026-02-17 |
| 3. Licensing | 2/2 | Complete | 2026-02-17 |
| 4. Updates & Onboarding | 3/3 | Complete | 2026-02-17 |
| 5. Error Handling & Code Cleanup | 1/1 | Complete | 2026-02-19 |
| 6. Phase 4 Verification & Traceability | 1/1 | Complete | 2026-02-19 |
| 7. Add Streamable HTTP to the plugin | 2/2 | Complete    | 2026-02-19 |
| 28. Pre-Deploy Fixes | 1/1 | Complete    | 2026-03-04 |
| 29. Traceability Cleanup | 1/1 | Complete    | 2026-03-04 |
| 30. Verification Sweep | 2/2 | Complete    | 2026-03-04 |
| 31. Security Gate Fix & Code Cleanup | 1/1 | Complete    | 2026-03-04 |

### Phase 7: Add Streamable HTTP to the plugin

**Goal:** Replace custom REST endpoints with a single Streamable HTTP MCP endpoint at /mcp speaking JSON-RPC 2.0 over SSE, with updated settings page config snippets for Claude Code and Gemini CLI
**Depends on:** Phase 6
**Plans:** 2/2 plans complete

Plans:
- [x] 07-01-PLAN.md — StreamableHttpHandler + Server.php refactor (JSON-RPC dispatch, SSE output, CORS, legacy endpoint removal) — completed 2026-02-19
- [ ] 07-02-PLAN.md — Settings page updates (config snippet URLs, test connection, custom base URL override)

### Phase 07.1: Fix MCP Tool Bugs and Missing Enable Bricks Tool (INSERTED)

**Goal:** Fix bugs in existing Bricks MCP tools, add enable/disable Bricks editor tools, improve tool response quality and descriptions, ensure all tools have proper input validation with helpful error messages
**Depends on:** Phase 7
**Plans:** 2/2 plans complete

Plans:
- [x] 07.1-01-PLAN.md — Add enable_bricks/disable_bricks tools + fix not_bricks_page error message
- [ ] 07.1-02-PLAN.md — Fix add_element position bug + sweep error messages and tool descriptions

### Phase 8: Template CRUD Tools

**Goal:** Full CRUD operations for Bricks templates (headers, footers, popups, content, sections, archives, etc.) via MCP tools — creation, metadata updates, deletion, duplication, condition management, template resolution, and taxonomy organization
**Depends on:** Phase 7
**Plans:** 3/3 plans complete

Plans:
- [x] 08-01-PLAN.md — Core template CRUD (create, update, delete, duplicate) + enhance list_templates filters
- [x] 08-02-PLAN.md — Condition management (set_template_conditions, get_condition_types, resolve_templates)
- [x] 08-03-PLAN.md — Taxonomy CRUD (list/create/delete template tags and bundles)

### Phase 9: Global Classes CRUD Tools

**Goal:** Full lifecycle management of Bricks global CSS classes — create, update, delete (soft-delete to trash), batch operations, category CRUD, and CSS import with breakpoint/pseudo-state mapping
**Depends on:** Phase 8
**Requirements:** DSGN-05
**Plans:** 3 plans

Plans:
- [x] 09-01-PLAN.md — Core class CRUD (create, update, delete) + enhance get_global_classes with category filter
- [x] 09-02-PLAN.md — Batch operations (bulk create/delete) + category CRUD (list, create, delete)
- [x] 09-03-PLAN.md — CSS import tool (parse CSS string to Bricks classes with breakpoint/pseudo mapping)

### Phase 10: Theme Styles & Typography Scales Tools

**Goal:** Full CRUD for Bricks theme styles (site-wide typography H1-H6/body, link colors, conditions) and native typography scales (CSS variables + utility classes via bricks_global_variables system), enabling AI to manage complete design systems and swap entire site appearances
**Depends on:** Phase 9
**Requirements:** DSGN-06
**Plans:** 2 plans

Plans:
- [ ] 10-01-PLAN.md — Theme style CRUD (list, get, create, update with deep-merge partial updates, delete/deactivate) with before/after diff and active-style warnings
- [ ] 10-02-PLAN.md — Typography scale CRUD (get, create, update, delete) on bricks_global_variables system with feature-checked CSS regeneration

### Phase 11: Global Colors & Variables Tools

**Goal:** Full CRUD for Bricks color palettes (multi-palette color management with CSS variables, parent/child relationships, utility classes) and global variables (CSS custom properties for spacing, borders, and design tokens with category organization and batch creation)
**Depends on:** Phase 10
**Requirements:** DSGN-06
**Plans:** 2 plans

Plans:
- [ ] 11-01-PLAN.md — Color palette CRUD (list, create/rename/delete palettes, add/update/delete colors with auto-derived CSS vars and parent/child hierarchies)
- [ ] 11-02-PLAN.md — Global variable CRUD (list, create/rename/delete categories, create/update/delete variables, batch create) with Phase 10 scale category boundary guards

### Phase 12: Bricks Builder Settings Tools

**Goal:** Expose Bricks Builder global settings as read-only MCP tools (with API key masking and restricted setting flags), add per-page settings read/write tools with allowlist validation, enhance breakpoints metadata, and add a dangerous actions toggle for unrestricted access on development sites
**Depends on:** Phase 11
**Requirements:** DSGN-06
**Plans:** 3 plans

Plans:
- [ ] 12-01-PLAN.md — get_bricks_settings tool (read-only, category filtering, API key masking, restricted flags) + enhanced get_breakpoints metadata
- [ ] 12-02-PLAN.md — get_page_settings + update_page_settings tools (allowlist validation, JS gated behind dangerous mode, CSS with Bricks-first warning)
- [ ] 12-03-PLAN.md — Dangerous Actions toggle in admin settings (checkbox with red warning, defaults off)

### Phase 13: Images & Media Management

**Goal:** Add Unsplash image search, URL-based image sideloading into WordPress media library, featured image management, and image element setting helpers so AI can build pages with real images instead of wireframes
**Depends on:** Phase 12
**Requirements:** MDIA-01, MDIA-02, MDIA-03
**Plans:** 2 plans

Plans:
- [ ] 13-01-PLAN.md — MediaService + search_unsplash + sideload_image + get_media_library tools
- [ ] 13-02-PLAN.md — set_featured_image + remove_featured_image + get_image_element_settings tools

### Phase 14: Navigation Menus

**Goal:** Create, update, and assign WordPress navigation menus via MCP tools so AI can build multi-page sites with linked navigation, including menu item CRUD and Bricks nav element integration
**Depends on:** Phase 13
**Plans:** 2/2 plans complete

Plans:
- [x] TBD (run /gsd:plan-phase 14 to break down) (completed 2026-02-20)

### Phase 15: Tool Consolidation

**Goal:** Consolidate 90 individual MCP tools into ~16 domain-grouped tools with action parameters so AI agents can reliably select the right tool (research shows accuracy drops from 95% to 41% above 50 tools), while preserving all existing functionality and license gating
**Depends on:** Phase 14
**Requirements:** CONSOL-01
**Plans:** 3/3 plans complete

Plans:
- [ ] 15-01-PLAN.md — Foundation: require_license() helper + consolidate wordpress, bricks, page, element tools (22 tools to 6)
- [ ] 15-02-PLAN.md — Design system: consolidate template, template_condition, template_taxonomy, global_class, theme_style, typography_scale tools (36 tools to 6)
- [ ] 15-03-PLAN.md — Remaining: consolidate color_palette, global_variable, media, menu tools (30 tools to 4) + update test-all-tools.sh

### Phase 16: Dynamic Content & Archives

**Goal:** Expose Bricks dynamic data tags, query loop type reference, and archive template patterns so AI can build blog listings, portfolio archives, and dynamic content pages that pull live WordPress data
**Depends on:** Phase 15
**Requirements:** BRKS-08, BRKS-09
**Plans:** 1 plan

Plans:
- [ ] 16-01-PLAN.md — Dynamic tag discovery (get_dynamic_tags, get_query_types actions on bricks tool) + Builder Guide dynamic data/query loop/archive sections

### Phase 17: Forms

**Goal:** Add MCP tools for creating and configuring Bricks form elements with field definitions, validation rules, and form actions (email, webhook, redirect) so AI can build functional contact forms and lead capture pages
**Depends on:** Phase 16
**Plans:** 1 plan

Plans:
- [ ] 17-01-PLAN.md — Form schema example, get_form_schema action on bricks tool, Builder Guide forms section

### Phase 18: Bricks Element Animations & GSAP Integration

**Goal:** Add get_interaction_schema action to the bricks tool and expand BUILDER_GUIDE.md animations section so AI has complete knowledge of all 17 Bricks interaction triggers, 17 actions, all Animate.css animation types, chained animation patterns, and GSAP integration via the javascript action
**Depends on:** Phase 17
**Plans:** 1 plan

Plans:
- [ ] 18-01-PLAN.md — get_interaction_schema action + expanded BUILDER_GUIDE.md animations section with full trigger/action taxonomy and GSAP patterns

### Phase 19: Components & Slots

**Goal:** Add MCP tools for Bricks Components (2.0+) — create reusable components from element trees, manage component slots for flexible content insertion, instantiate components on pages, update component definitions with automatic propagation to all instances, and list/organize the component library
**Depends on:** Phase 18
**Requirements:** BRKS-07
**Plans:** 2 plans

Plans:
- [ ] 19-01-PLAN.md — Component tool registration + definition CRUD (list, get, create, update, delete) + get_component_schema on bricks tool
- [ ] 19-02-PLAN.md — Instance operations (instantiate, update_properties, fill_slot) + Builder Guide components section

### Phase 20: Popups & Modals

**Goal:** Add MCP tools for reading and writing Bricks popup display settings (close behavior, backdrop, sizing, display limits, AJAX loading, breakpoint visibility) on popup-type templates, plus a popup schema reference and Builder Guide section so AI can build marketing popups, notification modals, and lightbox experiences
**Depends on:** Phase 19
**Requirements:** BRKS-10
**Plans:** 1 plan

Plans:
- [ ] 20-01-PLAN.md — Popup settings CRUD (get/set on template tool), get_popup_schema on bricks tool, Builder Guide popups section

### Phase 21: Query Loops & Filters

**Goal:** Add MCP tools for configuring Bricks query loops on elements — post/CPT queries with taxonomy and meta filters, sorting, pagination (load more/infinite scroll), REST API data sources, and native Bricks filter elements for AJAX-powered content filtering so AI can build dynamic listings, portfolios, and filterable archives
**Depends on:** Phase 20
**Requirements:** BRKS-09
**Plans:** 2/2 plans complete

Plans:
- [ ] 21-01-PLAN.md — Extend get_query_types (API/array types, pagination), add get_filter_schema action, update Builder Guide
- [ ] 21-02-PLAN.md — Global query CRUD (get_global_queries, set_global_query, delete_global_query) on bricks tool

### Phase 22: Element Conditions & Visibility

**Goal:** Add MCP tools for setting element-level visibility conditions — show/hide based on user role, login status, date/time, post type, custom fields, and dynamic data with AND/OR logic and condition sets so AI can build personalized content that adapts to user context
**Depends on:** Phase 21
**Plans:** 2/2 plans complete

Plans:
- [x] 22-01-PLAN.md — Condition schema reference (bricks:get_condition_schema) + read conditions (element:get_conditions)
- [x] 22-02-PLAN.md — Set conditions with validation (element:set_conditions) + Builder Guide conditions section

### Phase 23: WooCommerce Builder Integration

**Goal:** Add MCP tools for building WooCommerce stores with Bricks — create product/shop/cart/checkout/account templates, configure WooCommerce-specific elements (product gallery, add-to-cart, price display, variation swatches), expose WooCommerce dynamic data tags, and manage product display settings so AI can build complete e-commerce experiences
**Depends on:** Phase 22
**Plans:** 3 plans

Plans:
- [x] 23-01-PLAN.md — Register woocommerce tool with status, get_elements, get_dynamic_tags read-only actions
- [x] 23-02-PLAN.md — Add WooCommerce section to Builder Guide with template patterns, element reference, dynamic data tags
- [x] 23-03-PLAN.md — Implement scaffold_template and scaffold_store actions for pre-populated WooCommerce templates

### Phase 24: SEO & Page Settings

**Goal:** Add SEO plugin integration layer to the page tool — detect active SEO plugin (Yoast, Rank Math, SEOPress, Slim SEO) at runtime, read/write normalized SEO fields to the correct plugin meta keys with inline audit, and document the workflow in Builder Guide so AI can optimize pages for search engines regardless of which SEO plugin is active
**Depends on:** Phase 23
**Requirements:** SEO-PLUGIN-INTEGRATION, SEO-AUDIT, SEO-BUILDER-GUIDE
**Plans:** 1/1 plans complete

Plans:
- [ ] 24-01-PLAN.md — SEO plugin detection + get_seo/update_seo page actions + BricksService methods + Builder Guide SEO section

### Phase 25: Import & Export System [COMPLETE]

**Goal:** Add MCP tools for Bricks template and design system import/export — export single templates or bulk ZIP archives, import templates from JSON/ZIP files, export/import global classes and CSS variables, and support remote template URLs for cross-site template sharing
**Depends on:** Phase 24
**Plans:** 1/1 plans executed

Plans:
- [x] 25-01-PLAN.md — Template export/import + global class export/import + Builder Guide section

### Phase 26: Custom Fonts Management [COMPLETE]

**Goal:** Add MCP tools for managing fonts in Bricks — list available fonts (system, Google, Adobe, custom), configure Google Fonts with weight/style selection, register Adobe Fonts via project ID, upload custom font files (WOFF2/WOFF/TTF), and apply fonts to elements and theme styles
**Depends on:** Phase 25
**Plans:** 1/1 plans executed

Plans:
- [x] 26-01-PLAN.md — Font status/settings methods + font tool dispatcher + Builder Guide font section

### Phase 27: Custom Code & Scripts [COMPLETE]

**Goal:** Add MCP tools for managing custom code in Bricks — get/set global custom CSS and JavaScript, manage page-specific code (header/body scripts), configure code element settings (PHP/HTML/CSS/JS execution), and control script placement (header vs body) with role-based execution permissions
**Depends on:** Phase 26
**Plans:** 1/1 plans executed

Plans:
- [x] 27-01-PLAN.md — Page code read/write methods + code tool dispatcher + Builder Guide custom code section

### Phase 28: Pre-Deploy Fixes

**Goal:** Fix deploy-blocking empty Polar org ID, remove dead requires_license parameter, normalize Bricks detection methods, and add missing builder guide section keys — closing all integration findings from v1 audit
**Depends on:** Phase 27
**Gap Closure:** Closes FINDING-01, FINDING-02, FINDING-03, FLOW-GAP-01 from v1 audit
**Plans:** 1/1 plans complete

Plans:
- [ ] 28-01-PLAN.md — Set BRICKS_MCP_POLAR_ORG_ID, remove dead requires_license param, normalize Bricks detection, add 4 missing builder guide sections

### Phase 29: Traceability Cleanup

**Goal:** Add extended requirements (v2 pulled forward) to REQUIREMENTS.md traceability table, formally define CONSOL-01, and add requirements-completed frontmatter to SUMMARY.md files across all phases
**Depends on:** Phase 28
**Gap Closure:** Closes traceability gaps from v1 audit
**Plans:** 1/1 plans complete

Plans:
- [ ] 29-01-PLAN.md — Extended requirement traceability, CONSOL-01 definition, SUMMARY.md frontmatter sweep

### Phase 30: Verification Sweep — Orphaned Extended Requirements

**Goal:** Formally verify all 7 orphaned extended requirements (code wired but unverified) by running API-level verification against Phases 9, 10, 11, 12, 13, 16, and 19, creating VERIFICATION.md files, and updating REQUIREMENTS.md statuses from Partial to Satisfied
**Depends on:** Phase 29
**Requirements:** BRKS-07, BRKS-08, MDIA-01, MDIA-02, MDIA-03, DSGN-05, DSGN-06
**Gap Closure:** Closes all 7 orphaned requirement gaps from v1 audit
**Plans:** 2/2 plans complete

Plans:
- [ ] 30-01-PLAN.md — Verify global classes (Phase 9), media (Phase 13), dynamic content (Phase 16) — DSGN-05, MDIA-01/02/03, BRKS-08
- [ ] 30-02-PLAN.md — Verify design system (Phases 10-12), components (Phase 19) — DSGN-06, BRKS-07 + update REQUIREMENTS.md

### Phase 31: Security Gate Fix & Code Cleanup

**Goal:** Fix missing `dangerous_actions` gate on `set_page_scripts` action in `tool_code()` dispatcher and remove unnecessary `require_bricks()` call from `tool_menu()` — closing all integration/flow findings from v1.0 audit
**Depends on:** Phase 30
**Gap Closure:** Closes SECURITY-01, ARCH-01, FLOW-01 from v1.0 audit
**Plans:** 1/1 plans complete

Plans:
- [ ] 31-01-PLAN.md — Add dangerous_actions gate to tool_code() set_page_scripts + remove require_bricks() from tool_menu()

### Phase 32: Deep security audit and hardening

**Goal:** Remove CORS wildcard headers, implement per-user rate limiting with configurable threshold and HTTP 429 responses, and document the complete security posture in docs/SECURITY.md
**Requirements**: SEC-01, SEC-02, SEC-03, SEC-04
**Depends on:** Phase 31
**Plans:** 2/2 plans complete

Plans:
- [x] 32-01-PLAN.md — Remove CORS headers from Server.php and StreamableHttpHandler.php, add per-user rate limiting with transient counters, add rate limit RPM settings field
- [x] 32-02-PLAN.md — Create docs/SECURITY.md documenting auth model, rate limiting, dangerous actions toggle, and security boundaries

### Phase 33: Add move_element, bulk update_elements, and global_variable management tools

**Goal:** [To be planned]
**Requirements**: TBD
**Depends on:** Phase 32
**Plans:** 0 plans

Plans:
- [ ] TBD (run /gsd:plan-phase 33 to break down)

### Phase 34: API Endpoint & App Password Blocking Diagnostics

**Goal:** Implement a comprehensive diagnostic system that detects and reports why MCP clients cannot connect — covering REST API blocking, App Password failures, security plugin interference, and server misconfigurations — surfaced via MCP tool action, admin settings panel, and WP Site Health integration
**Requirements**: DIAG-01, DIAG-02, DIAG-03, DIAG-04, DIAG-05, DIAG-06
**Depends on:** Phase 33
**Plans:** 2/2 plans complete

Plans:
- [x] 34-01-PLAN.md — DiagnosticCheck interface, 8 check classes, DiagnosticRunner, MCP get_site_info diagnose action, unit tests
- [x] 34-02-PLAN.md — Admin settings diagnostic panel, WP Site Health integration, activation checks, Builder Guide troubleshooting section
