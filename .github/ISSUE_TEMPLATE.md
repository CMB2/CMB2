<!--
Before posting a new issue:
- Please post general support and questions at https://www.wordpress.org/support/plugin/cmb2/. We will move to GitHub once a confirmed bug.
- Please check if your issue is addressed in the CMB2 Wiki Troubleshooting page: https://github.com/CMB2/CMB2/wiki/Troubleshooting
- Please review the contributing guidelines: https://github.com/CMB2/CMB2/blob/develop/CONTRIBUTING.md.
-->
## Expected Behavior:
<!--- If you're describing a bug, tell us what should happen -->
<!--- If you're suggesting a change/improvement, tell us how it should work -->

## Actual Behavior:
<!--- If describing a bug, tell us what happens instead of the expected behavior -->
<!--- If suggesting a change/improvement, explain the difference from current behavior -->

## Possible Solution
<!--- Not required, but suggest a fix/reason for the bug, -->
<!--- or ideas how to implement the addition or change -->

### Possible Solution's Risk Level
<!--- Document the potential risks for your proposed fix, -->
<!--- E.g. admin-only = minimal risk, or major user feature = high risk -->

## Steps to reproduce (I have confirmed I can reproduce this issue on the [`develop`](https://github.com/CMB2/CMB2/tree/develop) branch):
<!--- Provide a link to a live example, or an unambiguous set of steps to -->
<!--- reproduce this bug. Include code to reproduce, if relevant -->
1.
2.
3.
4.

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

## Screenshots (if appropriate)
<!--- Include screenshots of the console if errors are present -->
