# CMB2 Conventions & Blast-Radius Notes

CMB2 is an opinionated, 10+-year-old library bundled inside hundreds of themes
and plugins. This file is the compounding knowledge base of its conventions,
architectural seams, and back-compat blast radii — written for both human
contributors and AI agents.

**Reading rule:** consult this file before changing anything under `includes/`,
`bootstrap.php`, or `init.php`.

**Capture rule:** when a code review catches a convention violation or a
back-compat near-miss, the fix must land alongside a new or updated entry here,
on the same branch. Each entry cites the canonical in-code example — the code
is the spec; this file is the index to it.

**Delegation rule (AI agents):** subagents don't inherit this context. Work
orders delegating changes must quote the relevant entries verbatim.

---

## C1 — Per-box features hook up via `cmb2_init_hookup_{$cmb_id}`

**Rule:** A feature that attaches to boxes (hookup, REST, notices, anything
box-aware) registers a `maybe_init_and_hookup( CMB2 $cmb )` static on the
box-specific `cmb2_init_hookup_{$cmb_id}` action, added in the `CMB2`
constructor. The feature class *self-guards*: it receives every box and decides
for itself whether it applies (e.g. `CMB2_REST` checks `show_in_rest`).

**Canonical example:** `includes/CMB2.php` constructor (the
`CMB2_Hookup`/`CMB2_REST` `add_action` pair); fired from `bootstrap.php`'s
`cmb2_init_hookup_{$cmb->cmb_id}` loop; contract in
`CMB2_Hookup_Base::maybe_init_and_hookup()`.

**Anti-pattern:** registering a global hook elsewhere and scanning
`CMB2_Boxes::get_all()` to find relevant boxes. Two sources of truth for
"which boxes does this apply to", misses late-registered boxes, and bypasses
the per-box action seam developers use to unhook features from a single box.

**Blast radius:** extenders unhook/re-order `cmb2_init_hookup_*` callbacks per
box; a feature wired outside that seam won't respect their customizations.

## C2 — `bootstrap.php` is generic lifecycle only

**Rule:** `bootstrap.php` contains only the init ceremony: `do_action` /
`apply_filters` lifecycle events and the box-instantiation loops. No
feature-specific class references, ever. Feature wiring belongs in the `CMB2`
constructor (per-box features, see C1) or in the feature's own file.

**Canonical example:** `bootstrap.php` — note it ends "End. That's it, folks!"
and has barely changed since 2.2.0.

## C3 — The self-electing loader makes every behavior change site-wide

**Rule:** `init.php` version-elects: when multiple copies of CMB2 are bundled
(themes + plugins commonly each ship one), the *newest* copy wins and serves
ALL registered boxes on the site. Any default-behavior change therefore
propagates to every bundling product the moment one of them updates.

**Consequence:** behavior flips ship staged — opt-in first (default-off filter
+ notice + docs), default-on only in a later release. Never flip a default in
the same release that introduces the switch.

**Canonical example:** `init.php` (the `CMB2_Bootstrap_*` priority dance);
staged-rollout example: the `cmb2_rest_enforce_options_page_read_permissions`
filter (default false).

## C4 — REST reads are public by design; permission filters are the escape hatch

**Rule:** Boxes with `show_in_rest` readable expose reads publicly — this
mirrors WP core's `show_in_rest` semantics for object meta and is intentional,
not an oversight. Per-request overrides go through the existing
`cmb2_api_get_box_permissions_check` / `cmb2_api_get_field_permissions_check`
filters, which always run last and have final say. New permission logic must
run *before* them and must not remove them.

**Canonical example:** `CMB2_REST_Controller_Boxes::get_item_permissions_check_filter()`;
pinned by tests in `tests/test-cmb-rest-controllers.php`.

**Blast radius:** headless/decoupled front-ends read box data anonymously for
public display. Tightening reads by default breaks them — hence the staged
approach in C3, scoped only to options-page boxes (matching core's
settings-vs-meta distinction).

## C5 — The `capability` box prop means "who sees the admin screen"

**Rule:** `capability` (default `manage_options` for options pages) governs the
admin page. Reusing it for other gates (REST, front-end) conflates concerns —
acceptable only where the semantic genuinely matches (e.g. options-page REST
reads mirror the settings screen), and even then behind its own switch.

**Canonical example:** `includes/CMB2.php` `$mb_defaults['capability']` usage in
`CMB2_Options_Hookup`.

## C6 — Autoloader paths are case-sensitive by prefix

**Rule:** `cmb2_autoload_classes()` maps `CMB2_REST`/`CMB2_REST_*` (exact case)
to `includes/rest-api/`, `CMB2_Type*` to `includes/types/`, everything else to
`includes/`. A class named `CMB2_Rest_Foo` (lowercase "est") deliberately lands
in `includes/` — name classes with full awareness of which directory the prefix
routes to, and match filename to class name exactly.

**Canonical example:** `includes/helper-functions.php` `cmb2_autoload_classes()`.

## C7 — PHP floor is 7.4; local tests don't prove it

**Rule:** All code must run on PHP 7.4. Local `npm run phptests` runs PHP 8.3
only; the 7.4 floor is guarded exclusively by the CI matrix (`phpunit.yml`).
"Passes locally" is necessary, never sufficient. (Tooling details: CLAUDE.md
"Testing Strategy".)
