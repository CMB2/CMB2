<!--
Before posting a new issue:
- Please post general support and questions at https://www.wordpress.org/support/plugin/cmb2/. We will move to GitHub once a confirmed bug.
- Please check if your issue is addressed in the CMB2 Wiki Troubleshooting page: https://github.com/CMB2/CMB2/wiki/Troubleshooting
- Please review the contributing guidelines: https://github.com/CMB2/CMB2/blob/develop/CONTRIBUTING.md.
-->
### Expected Behavior:



### Actual Behavior:



### Steps to reproduce (I have confirmed I can reproduce this issue on the [`develop`](https://github.com/CMB2/CMB2/tree/develop) branch):

1.
2.

### CMB2 Field Registration Code:

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
