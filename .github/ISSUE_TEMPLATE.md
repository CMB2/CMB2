### Expected Behavior:



### Actual Behavior:



### Steps to reproduce (I have confirmed I can reproduce this issue on the trunk branch):

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
