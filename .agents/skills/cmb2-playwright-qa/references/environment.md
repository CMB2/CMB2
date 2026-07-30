# Environment reference

Configure the repository-root `.env.local`; it is ignored. `playwright.config.js` loads it with `dotenv` without overriding values already supplied by the shell or CI.

| Key | Default | Purpose |
| --- | --- | --- |
| `WP_BASE_URL` | `http://localhost:2623` | WordPress base URL used by Playwright. |
| `WP_USERNAME` | `admin` | WordPress account used by the auth setup. |
| `WP_PASSWORD` | `password` | Password used by the auth setup. |
| `SKIP_WP_SERVER` | unset | Set to `1`, `true`, or `yes` only when a WordPress site is already running. |
| `SKIP_WP_CHECK` | unset | Set to `1`, `true`, or `yes` only when the availability check is intentionally skipped. |

The default isolated environment is `.wp-env-tests.json` on port `2623`. It maps `tests/playwright/fixtures/test-fields.php` as a mu-plugin and provides the CMB2 integration metabox used by the durable Playwright tests.

Use `npm run test:e2e` for the normal suite. It starts the isolated environment unless `SKIP_WP_SERVER` is enabled. Use `npx playwright test tests/playwright/auth.setup.js --project=setup` to refresh the ignored reusable login state before a direct exploratory script.

Treat every non-default site as stateful: use a dedicated account, name any temporary content predictably, and remove only content created by the run when cleanup is authorized.

**The `cmb2` plugin may be inactive in the tests env.** Starting or re-provisioning it (`npm run phptests`, `npm run env:tests:start`) can leave `cmb2` deactivated — PHPUnit loads CMB2 through its own bootstrap and never notices, but nothing CMB2 renders in the browser. Before any browser run, check `wp-env run --config .wp-env-tests.json cli wp plugin list` and activate `cmb2` if needed. Symptom: an expected metabox/notice is simply absent with no error.
