# Permission Debugging Guide - Phase 10

## Current Issue
Form submissions (add/update/delete line items) are redirecting with "permission denied" error.

## Root Cause Hypothesis
`current_user_can( 'manage_options' )` is returning FALSE even though user is admin.
Possible causes:
1. Session timeout during form submission
2. Admin context not fully loaded in `admin-post.php` handler
3. Cookie/session configuration issue
4. Nonce validation failure

## How to Debug

### Step 1: Check Debug Log
**Location:** `wp-content/debug.log`

**Look for these log entries when you submit a form:**
```
[AYS_Invoices_Tab::handle_add_invoice_item] Handler called
[Permission] current_user_can(manage_options): TRUE/FALSE
[Auth] User ID: (number or 0)
[Auth] User roles: admin,editor,etc
```

### Step 2: Test Procedure
1. Log in as admin
2. Navigate to Invoices → Edit an invoice
3. **Immediately** fill the "Add line" form and click "Add line"
4. Check `wp-content/debug.log` for above entries
5. Note which log shows the permission denial

### Step 3: Interpret Results

**If you see:**
- `[Permission] current_user_can(manage_options): FALSE`
- `[Auth] User ID: 0`
→ **Session is lost during form submission**

**Solution:** Review session configuration in `wp-config.php`:
```php
// These should be set:
define( 'COOKIE_DOMAIN', 'projectstudios.local' );
define( 'COOKIEPATH', '/' );
define( 'AUTH_COOKIE_EXPIRATION', 172800 );
```

**If you see:**
- `[Permission] current_user_can(manage_options): TRUE`
- `[Auth] User ID: 1`
→ **Permission check is passing, but something else is wrong**

**Solution:** Look for nonce errors or database issues

### Step 4: Temporary Bypass (Testing Only)
If you need to test if the rest of the form submission works, you can temporarily comment out the permission check:

```php
// TEMPORARY FOR TESTING ONLY
// if ( ! current_user_can( 'manage_options' ) ) {
//     error_log( '[BLOCKED] Permission denied' );
//     wp_safe_redirect( ... );
//     exit;
// }
```

Then test if the line item actually saves. If it does, the issue is **ONLY** with the permission check, not the rest of the code.

## What Changed
Added detailed logging to all three handlers:
- `handle_add_invoice_item()`
- `handle_update_invoice_item()`
- `handle_delete_invoice_item()`

Each handler now logs:
1. When it's called
2. Permission check result
3. Current user ID
4. Current user roles
5. Data being processed

## Next Steps After Debugging

Once you identify the issue:

1. **Session timeout**: Fix in `wp-config.php` or add session refresh
2. **Permission context**: May need to move handler to different hook
3. **Nonce issue**: Check nonce field name matches between form and handler

Then remove the logging and commit a clean version.

## Files Modified
- `includes/invoices/ays-class-invoices-tab.php` (added logging to 3 handlers)

## To Remove Logging Later
Search for `error_log( '[AYS_Invoices_Tab` and remove those lines
Or search for `error_log( '[Permission]` and remove that section
