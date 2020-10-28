---
name: Bug report
about: Create a report to help us improve
title: ''
labels: ''
assignees: ''

---

<!--
Before posting a new issue:
- Please post general support and questions at https://www.wordpress.org/support/plugin/cmb2/. We will move to GitHub once a confirmed bug.
- Please check if your issue is addressed in the CMB2 Wiki Troubleshooting page: https://github.com/CMB2/CMB2/wiki/Troubleshooting
- Please review the contributing guidelines: https://github.com/CMB2/CMB2/blob/develop/CONTRIBUTING.md.
-->

## Describe the bug
A clear and concise description of what the bug is.

## Steps to reproduce (I have confirmed I can reproduce this issue on the [`develop`](https://github.com/CMB2/CMB2/tree/develop) branch):
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

## Possible Solution
<!--- Not required, but suggest a fix/reason for the bug, -->
<!--- or ideas how to implement the addition or change -->

### Possible Solution's Risk Level
<!--- Document the potential risks for your proposed fix, -->
<!--- E.g. admin-only = minimal risk, or major user feature = high risk -->

## Screenshots
If applicable, add screenshots to help explain your problem.

## CMB2 Field Registration Code:

```php
add_action( 'cmb2_admin_init', 'yourprefix_register_demo_metabox' );
function yourprefix_register_demo_metabox() {

	$cmb = new_cmb2_box( array(
		// Box Config...
	) );

	$cmb->add_field( array(
		// Field Config...
	) );

	// Additional fields...
}
```

## Your Environment
<!--- Include as many relevant details about the environment you experienced the bug in -->
Browser name and version:
Operating System and version (desktop or mobile):

## Additional context
Add any other context about the problem here.
