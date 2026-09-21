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

**Where the semantic does match:** an options-page target's *object id is the
option name* the data lands in, so "may write this option" and "may use this
settings screen" are the same question — see
`CMB2_Ajax::can_cache_for_options_page()`. Note the shape: the box that
declared the option key is also the only place a capability for it exists, so
looking the box up both supplies the check and bounds the accepted option keys
to ones a box declared. An id no box claims has no capability to consult, and
is therefore not a target.

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

## C8 — Configuration over code: box props are the primary interface

**Rule:** A new per-box behavior must be reachable as a **box registration
property**. The registration array is the interface CMB2 developers actually
use — and the one a bundling theme/plugin can ship per box — so it is the
primary layer; filters are the secondary, site-wide layer for cross-cutting
policy. When a behavior has both, the box prop wins outright and the filter
governs only the boxes that left the prop unset. That way a box declares its
intent once, in the array where it was registered, and keeps it across a later
default flip (C3).

**Canonical examples:** `includes/CMB2.php` `$mb_defaults` — `show_in_rest`,
`capability`, and `rest_read_capability`. The last one is the shape to copy,
reading exactly as plain English: `false` = "no" (REST reads disabled for
everyone, via core's `do_not_allow`); `true` = "yes, everyone" (alias of
`'exist'`, the capability WP grants every visitor); `'box-capability'` = the
box's own `capability` prop (the only non-duplicating spelling — naming the cap
copies a value that lives elsewhere and can drift); any other capability string
= only its holders; unset = the default policy (and the staged flip, C3). It
cascades field → box → default like `show_in_rest`. Precedence is resolved in one
place, `CMB2_REST::get_rest_read_capability()`, and consumed by
`CMB2_REST_Controller::maybe_gate_read_by_capability()`.

**Value semantics — a prop value must read correctly in plain English.** A
config value is documentation; if its plain-English reading is ambiguous or
contradicts its behavior, it's the wrong value. `rest_read_capability` went
through this exact wringer: `false` originally meant "reads are public (no
capability required)" — but it *reads* as "REST read capability? no", i.e.
reads disabled. The fix wasn't banning the boolean; it was making the meaning
match the reading (`false` now disables reads; `true` now means everyone).
Corollaries: booleans are fine only when yes/no is the honest answer to the
prop's name read as a question; when the answer is a *which* (which
capability?), every value is a real answer; and prefer WP-canonical vocabulary
over invented sentinels — `'exist'` (granted to everyone) and `do_not_allow`
(denied to everyone) bound the range, so neither "public" nor "disabled" needs
special-casing.

**Anti-pattern:** shipping a filter-only interface for per-box behavior. It
forces every developer to write a callback that re-derives "which box is this?"
from the filter args, cannot be expressed in the registration array a theme or
plugin already ships, and leaves no way to declare per-box intent — the filter
answers site-wide, so a site with a single box needing the old behavior has to
special-case every box by hand.

**Blast radius:** props are public API forever — name them for the behavior,
not the implementation. Adding one to `$mb_defaults` also changes the array
pinned by `Test_CMB2_Core::test_defaults_set()`; update that fixture in the same
commit.
