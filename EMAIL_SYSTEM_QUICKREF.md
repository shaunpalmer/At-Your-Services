# Email System - Quick Reference Card

## For Developers (4 weeks from now)

### Current Setup
- **Transport**: File logging (development)
- **Log File**: `C:\xampp\htdocs\projectstudios\wp-content\email-log.txt`
- **Status**: ✅ Fully functional for local testing

### View Recent Emails
```powershell
Get-Content C:\xampp\htdocs\projectstudios\wp-content\email-log.txt -Tail 50
```

### Settings
**Location**: WordPress Admin → Leads → Settings

**Edit templates with these tokens**:
- `{name}` `{email}` `{phone}` `{service}`
- `{booking_date}` `{booking_time}` `{notes}`
- `{lead_id}` `{admin_url}` `[all-fields]`

### Key Files
| File | Purpose |
|------|---------|
| `includes/notifications/AYS_Notifier.php` | Builds & sends emails |
| `includes/notifications/AYS_Notification_Router.php` | Listens for lead creation |
| `includes/notifications/transport/MailTransportFactory.php` | Chooses delivery method |
| `includes/notifications/transport/FileLoggingTransport.php` | Logs to file (dev) |
| `includes/notifications/AYS_Notification_Settings.php` | Manages settings |
| `includes/helpers/autoloader.php` | Loads classes |

### Troubleshooting
```php
// Test if email system is working
if (class_exists('Mailer')) {
    $result = Mailer::instance()->send(
        'test@example.com',
        'Test',
        'Body',
        ['Content-Type: text/html; charset=UTF-8']
    );
    echo $result ? 'OK' : 'FAILED';
}
```

### Check Logs
```sql
SELECT * FROM wp_ays_notification_log ORDER BY ts DESC LIMIT 20;
```

---

## For Production Deployment

### Step 1: Choose Email Delivery
- [ ] Option A: Configure server mail (Postfix/Sendmail)
- [ ] Option B: Use external SMTP (Gmail, SendGrid, etc.)
- [ ] Option C: Use Mercury/32 SMTP relay

### Step 2: Update Factory
Edit `includes/notifications/transport/MailTransportFactory.php`:
```php
// Change from:
$type = apply_filters('ays_mail_transport', 'file_logging');

// To:
$type = apply_filters('ays_mail_transport', 'wp_mail');
```

### Step 3: Configure SMTP (if using external service)
Create `wp-content/mu-plugins/my-smtp-config.php`:
```php
<?php
add_action('phpmailer_init', function($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host = 'smtp.gmail.com';
    $phpmailer->Port = 587;
    $phpmailer->SMTPAuth = true;
    $phpmailer->SMTPSecure = 'tls';
    $phpmailer->Username = 'your-email@gmail.com';
    $phpmailer->Password = 'your-app-password';
}, 10, 1);
```

### Step 4: Test
1. Submit test lead through form
2. Check admin received email
3. Check customer received auto-reply
4. Monitor `wp_ays_notification_log` for errors

### Step 5: Delete Development Files
- Delete `/wp-content/email-log.txt`
- Delete/disable `/wp-content/mu-plugins/mercury-smtp-config.php`

---

## Documentation Files
- **Full Guide**: `EMAIL_SYSTEM_DOCUMENTATION.md`
- **This File**: `EMAIL_SYSTEM_QUICKREF.md`
- **Inline Docs**: See comments in each PHP file

---

## Support Checklist
- [x] ✅ Admin notifications working
- [x] ✅ Customer auto-replies working  
- [x] ✅ Settings UI functional
- [x] ✅ Email logging working
- [x] ✅ All classes autoloaded
- [ ] ⚠️  Production SMTP configured
- [ ] ⚠️  Real emails being sent

---

**Created**: 2025-10-18
**Last Updated**: 2025-10-18
**Status**: Development (File Logging)
