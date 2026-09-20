# Security Policy

## Reporting a vulnerability

**Please report security issues privately — not in a public issue.**

Use GitHub's [private vulnerability reporting](https://github.com/CMB2/CMB2/security/advisories/new). It goes directly to the maintainers, and nothing is public until an advisory is published.

Please include:

1. The CMB2 and WordPress versions you tested.
2. Steps to reproduce, plus the metabox/field registration code needed to trigger it (a [gist](https://gist.github.com/) is fine).
3. The impact: what an attacker can do, and what role or capability they need to do it.
4. Whether the issue still reproduces on the [`develop`](https://github.com/CMB2/CMB2/tree/develop) branch.

## Supported versions

CMB2 maintains a single line — the latest release. Security fixes land in the next patch release; there are no backports to older versions. Note that CMB2 is often bundled inside a theme or plugin rather than installed from wordpress.org, so "upgrade" may mean updating that bundled copy.

## What to expect

* Acknowledgement within 7 days.
* An assessment — accepted, needs more information, or not a vulnerability — within 14 days.
* A fix in the next patch release, with a GitHub Security Advisory crediting you unless you ask otherwise.

Please give us a reasonable window to ship a fix before disclosing publicly. If you have had no response after 14 days, open a public issue to nudge the thread — without any vulnerability details.
