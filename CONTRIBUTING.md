# Contributing to CMB2

Thank you for your interest in contributing back to CMB2. Please help us review your issues and/or merge your pull requests by following the below guidelines.

#### NOTE: The issues section is for bug reports and feature requests only.
_Support is not offered for this library, and the likelihood that the maintainers will respond is very low. If you need help, please use [stackoverflow](http://stackoverflow.com/search?q=cmb), or the [wordpress.org plugin forums](http://wordpress.org/support/plugin/cmb2)._

Before reporting a bug
---
1. Please review the [documentation](https://github.com/CMB2/CMB2/wiki). Most questions revolve around the [field types](https://github.com/CMB2/CMB2/wiki/Field-Types), [field parameters](https://github.com/CMB2/CMB2/wiki/Field-Parameters), or are addressed in the [troubleshooting](https://github.com/CMB2/CMB2/wiki/Troubleshooting) section.
2. Search [issues](https://github.com/CMB2/CMB2/issues) to see if the issue has been previously reported.
3. Install the [`develop`](https://github.com/CMB2/CMB2/tree/develop) version of CMB2 and test there.


How to report a bug
---
1. Specify the version number for both WordPress and CMB2.
3. Describe the problem in detail. Explain what happened, and what you expected would happen.
4. Provide a small test-case and a link to a [gist](https://gist.github.com/) containing your entire metabox registration code.
5. If helpful, include a screenshot. Annotate the screenshot for clarity.


How to contribute to CMB2
---
All contributions welcome. If you would like to submit a pull request, please follow the steps below.

1. Make sure you have a GitHub account.
2. Fork the repository on GitHub.
3. **Check out the [`develop`](https://github.com/CMB2/CMB2/tree/develop) version of CMB2.** If you submit to the master branch, the PR will be closed with a link back to this document.
4. **Verify your issue still exists in the [`develop`](https://github.com/CMB2/CMB2/tree/develop) branch.**
5. Make changes to your clone of the repository.
	1. Please follow the [WordPress code standards](https://make.wordpress.org/core/handbook/coding-standards).
	2. If possible, and if applicable, please also add/update unit tests for your changes.
	3. Please add documentation to any new functions, methods, actions and filters.
	4. When committing, reference your issue (if present) and include a note about the fix.
6. [Submit a pull request](https://help.github.com/articles/creating-a-pull-request/).

**Note:** You may gain more ground and avoid unecessary effort if you first open an issue with the proposed changes, but this step is not necessary.

What we expect from a pull request
---
CMB2 runs on hundreds of thousands of WordPress sites, often bundled inside other plugins and themes, and people have relied on it behaving the same way for over a decade. A regression reaches sites whose owners never touched their code and won't connect the breakage to a CMB2 update. So we review for back-compatibility first and correctness second, and we ask the same of you:

1. **Keep PRs small, and remember that a small diff isn't a small change.** A one-line edit in `CMB2_Field` or `CMB2_Options` runs on every save of every box. Before you open the PR, list every caller of the code you changed and what each one expects from it.
2. **Find out why the code is the way it is before you change it (Chesterton's fence).** Code that looks odd is often handling a case you haven't hit. Check `git log -S`, `git blame`, and old issues and PRs first. See [this checklist](https://github.com/jtsternberg/claude-plugins/blob/main/plugins/thinking-tools/skills/chestertons-fence/SKILL.md).
3. **Check your diagnosis against the code, not just the bug report.** Anything you state in the PR description as fact (the root cause, "no other behavior changes") should be something you've confirmed in the code.
4. **Prove your regression test.** It has to go through the path the bug report goes through, and it has to fail with your fix reverted. Say in the PR that you checked this.
5. **List every behavior change,** including side effects: extra database writes, hooks or filters that fire a different number of times or with different data, and changed return values. If existing sites could notice the difference, it needs a filter or a staged rollout (see `CONVENTIONS.md`, C3).
6. **Support PHP 7.4 and later.** CI runs the tests on PHP 7.4–8.3.

Translations
---
If you are looking to provide language translation files, Please do so via [WordPress Plugin Translations](https://translate.wordpress.org/projects/wp-plugins/cmb2).

Creating/Running Tests
---
We use PHPUnit and the WordPress test suite for our unit/integration tests.

The quickest way to run them is `npm install && npm run phptests`. It needs only Docker, and it starts a wp-env test environment for you. To set the suite up by hand instead:

1. You can install the WordPress test suite [using the installer](https://github.com/CMB2/CMB2/blob/develop/tests/bin/install-wp-tests.sh#L3): `bash tests/bin/install-wp-tests.sh wordpress_test root ''`. (this will install the test suite in the temp folder on your computer, using a test database with those given credentials)
1. Install PHPUnit via composer, `composer install`.
1. Once Composer and the WordPress test sutie are installed, you can run phpunit via `./vendor/bin/phpunit` in the CMB2 directory.

Additional Resources
---

* [CMB2 Documentation Wiki](https://github.com/CMB2/CMB2/wiki)
* [CMB2 Snippet Library](https://github.com/CMB2/CMB2-Snippet-Library)
* [CMB2 API Documentation](http://cmb2.io/api/)
* [General GitHub Documentation](http://help.github.com/)
* [GitHub Pull Request documentation](http://help.github.com/send-pull-requests/)
* [PHPUnit Tests Guide](http://phpunit.de/manual/current/en/writing-tests-for-phpunit.html)
