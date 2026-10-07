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

## C9 — The registered-setting `sanitize_callback` sits on CMB2's own save path

**Rule:** The `sanitize_callback` passed to `register_setting()` must be a
passthrough. Core attaches it to `sanitize_option_{$option_name}`, which
`update_option()` fires — so it runs on every CMB2 options-page save, not only
on settings submitted through core's `options.php`. Real sanitization happens
earlier and per field type, in `CMB2_Sanitize`, before the option array is
assembled.

**Consequence:** anything that transforms values in that callback applies one
field type's rule to every field type at once. A `map_deep( $value,
'sanitize_text_field' )` there strips HTML from `wysiwyg` fields, collapses
newlines in `textarea`s, and flattens group/repeatable arrays — silently, on
save, with no recovery. The callback exists only so wp.org's Plugin Check
(`PluginCheck.CodeAnalysis.SettingSanitization`) sees one; it is not a
sanitization seam.

**Canonical example:** `cmb2_sanitize_option_passthrough()` in
`includes/helper-functions.php`, passed from
`CMB2_Options_Hookup::hooks()`; the save path it lands in is
`CMB2_Options_Hookup::save_options()` → `CMB2::save_fields()` →
`CMB2_Options::set()` → `update_option()`.

**Blast radius:** every options page in every bundling product, and any
third-party `update_option()` on a CMB2 option key. Requested in
CMB2/CMB2#1533 with a transforming callback attached; that patch was declined
for this reason.

**Related:** new `cmb2_*` helpers in `includes/helper-functions.php` are *not*
wrapped in `function_exists()` — the version election (C3) loads exactly one
copy of `includes/`. Only the PHP-polyfill functions at the end of that file
are guarded, because those names can come from elsewhere.

## C10 — Escaping-exception types escape values in their final context

**Rule:** A field type listed by `CMB2_Field::escaping_exception()` receives
its stored value without generic escaping. Its renderer must validate the
value shape and escape each value for the exact HTML context where it is used.
`CMB2_Utils::concat_attrs()` only assembles pre-escaped attributes.

**Canonical example:** `CMB2_Type_File_List::render()` validates its positive
integer attachment IDs, ignores malformed stored entries, runs each URL
through `CMB2_Sanitize::sanitize_and_secure_url()` (the same sanitizer the
save path uses, so render and save agree), then applies `esc_attr()` before
passing it into a hidden input attribute. URL sanitization and attribute
escaping are separate jobs: `esc_attr()` alone leaves a `javascript:` URL
intact.

**Blast radius:** escaping an entire structured field before rendering can
corrupt values needed by type-specific output. Leaving a renderer boundary
unescaped lets malformed stored values alter the generated markup.

## C11 — Default field displays escape stored text at the output boundary

**Rule:** `textarea_code` is a source-code editor, so its save path preserves
valid PHP, HTML, and JavaScript rather than applying `wp_kses_post()` or a text
sanitizer that would corrupt the code. `CMB2_Sanitize::textarea_code()` accepts
scalar input while retaining its historical entity-decoding and slash-removal
behavior. Default field renderers must escape stored field values for the output
context, including values loaded directly from metadata or written by custom
callbacks. This rule applies both to admin columns and to front-end values
rendered by the default `display_cb`.

**Canonical examples:** `CMB2_Display_Textarea_Code::_display()` applies
`htmlspecialchars()` with `ENT_SUBSTITUTE` and an explicit UTF-8 charset inside
a `<pre class="cmb2-code">` wrapper. It double-encodes existing entities so
users see the original source literally, and substitutes malformed character
sequences instead of blanking the value. The old `<xmp>` raw-text element
displayed entities verbatim but let its closing tag break out. The fallback
`CMB2_Field_Display::_display()` and
`CMB2_Display_Text_Money::_display()` use `esc_html()` for their plain-text
values, where WordPress's normal entity handling applies. Literal code needs the
more specific encoding above so existing entities remain part of the displayed
source text. Select and multicheck option labels and `show_option_none` come
from developer-supplied configuration, not stored field values; those labels
may intentionally contain markup and retain their existing rendering contract.

**Blast radius:** filtering markup on save breaks the field's core purpose and
silently rewrites code stored by existing integrations. Escaping only on save
does not protect legacy values or values written directly through metadata APIs.
The textarea-code column wrapper changed from `xmp.cmb2-code` to
`pre.cmb2-code`; integrations styling or selecting the old element name must
update their selector. The default `display_cb` also uses these renderers for
front-end value output, not only admin columns.
Custom `display_cb` and `display_class` implementations own their own output
escaping and are not changed by the default renderer rules above.
The repository PHPCS rules exclude `WordPress.Security.EscapeOutput`, so these
renderer boundaries rely on this convention and their regression tests.

## C12 — REST `object_type`/`object_id` are validated against the box's registration

**Rule:** on the CMB2 boxes/fields endpoints, the request's `object_type` and
`object_id` choose the storage a box reads from and writes to, while the
permission logic (C4, C5, the options-page read gate) reasons from the box's
*registered* types. The two must agree, so the request is validated against the
box before either value is applied:

- `object_type` must be a storage type the box is registered for. Registered
  types map to storage types the way `CMB2::mb_object_type()` maps them: core
  object types (`post`, `user`, `comment`, `term`, `options-page`) store as
  themselves, any other registered type (a post type such as `page`) stores as
  `post`. An options-page box includes back-compat `show_on` configs
  (`is_options_page_mb()`).
- For `options-page`, `object_id` is the option name, so it must be one of the
  box's own `options_page_keys()` — the same bound as
  `CMB2_Ajax::can_cache_for_options_page()` (see C5).

A mismatch is a 400 `WP_Error` (`cmb2_rest_invalid_object_type` /
`cmb2_rest_invalid_object_id`). It is request validation, not a permission, so
it sits outside the permission filters: a filter returning `true` does not make
an unregistered target valid. Code acting on the target afterwards (e.g. the
options-page `set()` in `modify_field_value()`) uses the validated values on the
box, not the raw request.

**Not staged (C3):** this check is on by default in the release that adds it.
A mismatched target was never a supported request: permission decisions
already assumed the registered type, an options-page key outside the box's own
keys has no capability to consult (C5), and those keys are fixed when the box is
registered (`init_options_mb()`), so the admin screen and REST always see the
same keys. No legitimate configuration relies on the old behavior, so there is
nothing to stage.

**Canonical example:** `CMB2_REST_Controller::initiate_rest_box()` →
`validate_request_object()` / `get_box_storage_types()`; pinned by the
`*_object_type_*` and `*_option_key` tests in
`tests/test-cmb-rest-controllers.php`.

**Blast radius:** every boxes/fields route (box read, fields collection, field
read/update/delete) passes through `initiate_rest_box()`. The core-route
`cmb2` field (`CMB2_REST::register_cmb2_fields()`) takes its object type from
the WordPress route, not the request, and is outside this check.
