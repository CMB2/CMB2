# Security Policy

## Reporting a vulnerability

**Please report security issues privately — not in a public issue.**

Use GitHub's [private vulnerability reporting](https://github.com/CMB2/CMB2/security/advisories/new). It goes directly to the maintainers, and nothing is public until an advisory is published. If you can't use GitHub, email hello@cmb2.io instead.

If you have also reported the issue elsewhere (Wordfence, Patchstack, WPScan or another CNA), please say so, so the issue gets a single CVE.

Please include:

1. The CMB2 and WordPress versions you tested.
2. Steps to reproduce, plus the metabox/field registration code needed to trigger it (a [gist](https://gist.github.com/) is fine).
3. The impact: what an attacker can do, and what role or capability they need to do it.
4. Whether the issue still reproduces on the [`develop`](https://github.com/CMB2/CMB2/tree/develop) branch.

## Out of scope

* Field data a site has chosen to expose through `show_in_rest`. REST reads follow WordPress core's `show_in_rest` conventions, and the `cmb2_api_*_permissions_check` filters let a site restrict them.
* Issues that require the `manage_options` or `unfiltered_html` capability.
* Issues in a theme's or plugin's own box/field registration or callback code, rather than in CMB2 itself.

## Supported versions

CMB2 maintains a single line — the latest release. Security fixes land in the next patch release; there are no backports to older versions. Note that CMB2 is often bundled inside a theme or plugin rather than installed from wordpress.org, so "upgrade" may mean updating that bundled copy. When several products bundle CMB2, the newest copy loads for the whole site, so updating any one of them to a fixed version protects the site.

## What to expect

* Acknowledgement within 7 days.
* An assessment — accepted, needs more information, or not a vulnerability — within 14 days.
* A fix in the next patch release, with a GitHub Security Advisory crediting you unless you ask otherwise. We request a CVE through GitHub, unless you already have one from another CNA.

Please give us up to 90 days to ship a fix before disclosing publicly; we publish the advisory once the fixed version is live on wordpress.org. If you have had no response after 14 days, open a public issue to nudge the thread — without any vulnerability details.
