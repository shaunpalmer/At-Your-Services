# Email Notification System Documentation

## Overview

The At Your Services plugin implements a dual-channel email notification system for lead capture. This document explains how it works, how to configure it, and how to troubleshoot issues.

**Current Status**: System is fully functional and logging emails to a local file for development testing.

---

## Architecture

### Core Components

1. **AYS_Notification_Router** (`includes/notifications/AYS_Notification_Router.php`)
   - Listens for the `ays_lead_created` hook
   - Triggers admin notifications and customer auto-replies
   - Routes to appropriate notification methods

2. **AYS_Notifier** (`includes/notifications/AYS_Notifier.php`)
   - Sends admin notifications about new leads
   - Sends customer auto-reply emails
   - Builds email tokens from lead data
   - Logs all sending attempts

3. **Mailer** (`includes/notifications/Mailer.php`)
   - Singleton facade for mail transport
   - Abstracts away the underlying mail mechanism
   - Allows switching between different mail transports

4. **MailTransportFactory** (`includes/notifications/transport/MailTransportFactory.php`)
   - Factory pattern for creating mail transport instances
   - Currently defaults to `FileLoggingTransport` for development
   - Can be switched via `ays_mail_transport` filter

5. **Transport Implementations**:
   - `FileLoggingTransport` – Logs emails to `/wp-content/email-log.txt` (development)
   - `WPMailTransport` – Uses WordPress `wp_mail()` (requires mail server)
   - `NullTransport` – Discards emails (for testing)

6. **AYS_Notification_Settings** (`includes/notifications/AYS_Notification_Settings.php`)
   - Manages plugin options via WordPress Settings API
   - Stores admin notification, customer auto-reply, and API key settings
   - Key: `ays_notifications_settings`

7. **AYS_Notification_Logger** (`includes/notifications/AYS_Notification_Logger.php`)
   - Logs all notification events to database table `wp_ays_notification_log`
   - Tracks sent, failed, and error states
   - Useful for auditing and debugging

---

## Configuration

### Settings Page

Navigate to: **WordPress Admin → Leads → Settings**

#### Admin Notification Settings
- **Enable**: Toggle to enable/disable admin notifications
- **To Recipients**: Email addresses to notify (comma or newline separated)
- **Subject Template**: Email subject with token support
- **Body Template**: Email body HTML with token support
- **From Name & Email**: Sender details
- **Send HTML**: Whether to send as HTML or plain text

#### Customer Auto-Reply Settings
- **Enable**: Toggle to enable/disable auto-replies
- **Subject Template**: Customer email subject
- **Body Template**: Customer email body
- **From Name & Email**: Who the auto-reply appears to come from

#### Available Tokens
All templates support these tokens:
- `{name}` – Lead's name
- `{email}` – Lead's email address
- `{phone}` – Lead's phone number
- `{service}` – Service requested
- `{booking_date}` – Preferred booking date
- `{booking_time}` – Preferred booking time
- `{notes}` – Additional notes from lead
- `{lead_id}` – Internal lead post ID
- `{admin_url}` – Direct link to lead in WordPress admin
- `[all-fields]` – All form fields formatted as HTML list

Example template:
```
Hi {name},

Thank you for your inquiry about {service}. We received your request on {created_at} and will get back to you shortly.

Best regards,
Project Studios
```

---

## Email Delivery Modes

### Current Setup: File Logging (Development)

**Location**: `C:\xampp\htdocs\projectstudios\wp-content\email-log.txt`

**How to view emails**:
```powershell
Get-Content C:\xampp\htdocs\projectstudios\wp-content\email-log.txt -Tail 50
```

**Advantages**:
- No mail server required
- Easy to review what would have been sent
- No external dependencies
- Safe for development (nothing actually sends)

**When to use**: During development and testing

---

### Production Setup Options

#### Option 1: WordPress wp_mail() with Configured Mail Server (Recommended)

**What it does**: Uses WordPress's built-in `wp_mail()` function with a properly configured mail server.

**Requirements**:
- Mail server installed and running (Postfix, Sendmail, Exim, etc.)
- PHP `mail()` function working correctly

**How to enable**:
```php
// Add to wp-config.php or create a mu-plugin
add_filter('ays_mail_transport', function() { 
    return 'wp_mail'; 
});
```

**Configuration**:
No additional configuration needed. Uses server's default mail configuration.

---

#### Option 2: External SMTP Service (Gmail, SendGrid, Mailgun, etc.)

**What it does**: Routes emails through a third-party SMTP service.

**Recommended services**:
- **Gmail** – Free tier available, 500/day limit
- **SendGrid** – 100/day free, scalable
- **Mailgun** – Developer friendly, pay-as-you-go
- **AWS SES** – Cheap, professional

**How to enable (Gmail example)**:

1. Enable "Less secure apps" on your Gmail account or create an App Password
2. Add to mu-plugin or wp-config.php:

```php
add_action('phpmailer_init', function($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host = 'smtp.gmail.com';
    $phpmailer->Port = 587;
    $phpmailer->SMTPAuth = true;
    $phpmailer->SMTPSecure = 'tls';
    $phpmailer->Username = 'your-email@gmail.com';
    $phpmailer->Password = 'your-app-password'; // Use App Password, not actual password
}, 10, 1);
```

---

#### Option 3: Mercury/32 Local SMTP Server (For Testing)

**What it does**: Uses Mercury/32 (included with XAMPP) as a local mail server.

**Note**: Mercury requires configuration to allow relay for external addresses. For local development, file logging is simpler.

**Configuration**: 
See Mercury/32 documentation in XAMPP Control Panel.

---

## Database Schema

### Notification Log Table

Table: `wp_ays_notification_log`

```sql
CREATE TABLE wp_ays_notification_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ts DATETIME DEFAULT CURRENT_TIMESTAMP,
    code VARCHAR(20) NOT NULL,
    details LONGTEXT,
    INDEX ts (ts),
    INDEX code (code)
);
```

**Fields**:
- `id` – Unique log entry ID
- `ts` – Timestamp when the event occurred
- `code` – Event type: 'sent', 'fail', 'error'
- `details` – JSON serialized details (lead_id, recipient, subject, error message, etc.)

**Example queries**:
```sql
-- See all failed sends
SELECT * FROM wp_ays_notification_log WHERE code = 'fail' ORDER BY ts DESC;

-- See errors
SELECT * FROM wp_ays_notification_log WHERE code = 'error' ORDER BY ts DESC;

-- Recent activity
SELECT * FROM wp_ays_notification_log ORDER BY ts DESC LIMIT 20;
```

---

## Autoloader Configuration

All notification classes are registered in `includes/helpers/autoloader.php`:

```php
'AYS_Notification_Router'       => '.../AYS_Notification_Router.php',
'AYS_Notifier'                  => '.../AYS_Notifier.php',
'AYS_Notification_Settings'     => '.../AYS_Notification_Settings.php',
'AYS_Notification_Logger'       => '.../AYS_Notification_Logger.php',
'AYS_Validation_Cron'           => '.../AYS_Validation_Cron.php',
'Mailer'                        => '.../Mailer.php',
'MailTransportFactory'          => '.../transport/MailTransportFactory.php',
'MailTransportInterface'        => '.../transport/MailTransportInterface.php',
'WPMailTransport'               => '.../transport/WPMailTransport.php',
'NullTransport'                 => '.../transport/NullTransport.php',
'FileLoggingTransport'          => '.../transport/FileLoggingTransport.php',
'LeadSourceAdapterInterface'    => '.../adapters/LeadSourceAdapterInterface.php',
'LeadArrayAdapter'              => '.../adapters/LeadArrayAdapter.php',
'LeadCPTAdapter'                => '.../adapters/LeadCPTAdapter.php',
```

**Important**: The autoloader checks class prefixes. Classes must start with `Ays_`, `AYS_`, `Lead`, `Mail`, or `Null` to be loaded.

---

## Troubleshooting

### Emails Not Being Sent

**Check 1: Verify settings are enabled**
```sql
SELECT * FROM wp_options WHERE option_name = 'ays_notifications_settings';
```

Look for `admin_notification.enabled = 1` and `customer_auto_reply.enabled = 1`.

**Check 2: Verify transport is working**
```php
// In wp-admin or a test script
if (class_exists('Mailer')) {
    $mailer = Mailer::instance();
    $result = $mailer->send(
        'test@example.com',
        'Test Subject',
        'Test Body',
        ['Content-Type: text/html; charset=UTF-8']
    );
    echo $result ? 'Sent successfully' : 'Failed to send';
}
```

**Check 3: Review email log**
```powershell
Get-Content C:\xampp\htdocs\projectstudios\wp-content\email-log.txt
```

**Check 4: Check database log**
```sql
SELECT * FROM wp_ays_notification_log ORDER BY ts DESC LIMIT 10;
```

**Check 5: Verify classes are loading**
- Make sure autoloader is registered in `ays.php`
- Check that `AYS_Notification_Router::init()` is called
- Verify `ays_lead_created` hook is firing

### Common Errors

| Error | Cause | Solution |
|-------|-------|----------|
| "Class not found" | Autoloader missing or class not registered | Add class to `autoloader.php` class map |
| "Could not instantiate mail function" | PHP `mail()` not configured | Use file logging or configure external SMTP |
| "SMTP Error: We do not relay non-local mail" | Mercury/32 relay restriction | Use file logging or configure Mercury relay settings |
| No emails in log | AYS_Notification_Router not initialized | Check `ays.php` includes `AYS_Notification_Router` and calls `init()` |
| Wrong sender email | Settings not configured | Check **Leads → Settings** page |

---

## Testing the System

### Manual Test (Development)

1. Navigate to the form page in WordPress
2. Submit the form with test data
3. Check the log file:
   ```powershell
   Get-Content C:\xampp\htdocs\projectstudios\wp-content\email-log.txt -Tail 50
   ```

### Programmatic Test

```php
// In a test script or plugin
define('WP_USE_THEMES', false);
require('wp-load.php');

$lead_data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '555-1234',
    'service' => 'Cleaning',
    'notes' => 'Test lead',
    'booking_date' => '2025-10-25',
    'booking_time' => '14:00',
    'page_url' => 'http://example.com',
    'timestamp' => current_time('mysql'),
];

// Trigger the notification system
do_action('ays_lead_created', 0, $lead_data);

// Check if email was logged
$log_file = WP_CONTENT_DIR . '/email-log.txt';
echo file_get_contents($log_file);
```

---

## Code Flow Diagram

```
1. User submits form (ays_shortcodes.php)
                ↓
2. Lead post created (wp_insert_post)
                ↓
3. 'ays_lead_created' hook fires
                ↓
4. AYS_Notification_Router::handle_new_lead() catches hook
                ↓
5. ├─ AYS_Notifier::send_admin_notification()
   │  ├─ Builds tokens from lead data
   │  ├─ Calls Mailer::send()
   │  └─ Logs to database
   │
   ├─ AYS_Notifier::send_customer_auto_reply()
   │  ├─ Builds tokens
   │  ├─ Calls Mailer::send()
   │  └─ Logs to database
   │
   └─ AYS_Validation_Cron::schedule()
      └─ Queues email validation

6. Mailer::send() routes through MailTransportFactory
                ↓
7. Transport handler executes
   ├─ FileLoggingTransport → Writes to /wp-content/email-log.txt
   ├─ WPMailTransport → Calls wp_mail()
   └─ NullTransport → Discards email

8. Result logged to wp_ays_notification_log table
```

---

## Future Enhancements

- [ ] Resend failed emails via cron
- [ ] Email templates with rich editor
- [ ] Bounce handling
- [ ] Unsubscribe links (GDPR)
- [ ] Email statistics dashboard
- [ ] Support for attachments
- [ ] Custom headers (Reply-To, List-Unsubscribe, etc.)

---

## Quick Reference

| File | Purpose |
|------|---------|
| `includes/notifications/AYS_Notification_Router.php` | Hook listener, routes notifications |
| `includes/notifications/AYS_Notifier.php` | Builds and sends emails |
| `includes/notifications/Mailer.php` | Singleton mail facade |
| `includes/notifications/transport/MailTransportFactory.php` | Creates transport instances |
| `includes/notifications/transport/FileLoggingTransport.php` | Development logging |
| `includes/notifications/AYS_Notification_Settings.php` | Settings management |
| `includes/notifications/AYS_Notification_Logger.php` | Database logging |
| `includes/helpers/autoloader.php` | Class autoloading |
| `admin/settings.php` | WordPress settings UI |
| `/wp-content/email-log.txt` | Development email log |

---

---

## Email Validation System

### Purpose
The email validation system verifies lead email addresses before sending notifications. This reduces wasted emails sent to invalid addresses and improves lead quality.

### How It Works

**Component**: `AYS_Email_Validator` (`includes/helpers/AYS_Email_Validator.php`)

1. **Async Validation Process**:
   - When a lead is created, `AYS_Validation_Cron` schedules validation for that email
   - Uses WordPress cron to validate asynchronously (doesn't block form submission)
   - Results are stored in database via `AYS_Notification_Logger`

2. **Validation Methods** (in order of preference):
   - **Primary**: emaillistvalidation.com API (requires API key)
   - **Fallback**: Local MX record checking (always available)
   - **Result**: Returns status: `ok`, `risky`, or `fail`

### Configuration

**Settings Location**: WordPress Admin → Leads → Settings → Email Validation section

**API Setup**:
1. Register at [emaillistvalidation.com](https://www.emaillistvalidation.com)
2. Get your API key from your account dashboard
3. Paste it into WordPress: **Leads → Settings → Email Validation API Key**
4. Save settings

> ⚠️ **Security**: Never commit your API key to Git. It's stored in `wp_options` table, not in code.

### API Response Codes

The emaillistvalidation.com API returns these codes:

| Code | Meaning | Action |
|------|---------|--------|
| `ok` | Valid email address | Safe to send |
| `risky` | Format valid but risky (catch-all domain, disposable, etc.) | Proceed with caution |
| `fail` | Invalid email address | Consider rejecting or flagging |

### Fallback MX Record Checking

If the API is unavailable or slow:
- Validator automatically falls back to local MX record checking
- Checks if the email's domain has valid MX records
- More lenient but always available

### Testing Validation

**Check validation results**:
```sql
SELECT * FROM wp_ays_notification_log 
WHERE status IN ('validation_ok', 'validation_risky', 'validation_fail')
ORDER BY ts DESC LIMIT 20;
```

**Manual validation test**:
```php
$validator = new AYS_Email_Validator();
$result = $validator->validate('test@example.com');
var_dump($result); // Array with keys: status, method, result
```

### Validation Cron

**File**: `includes/notifications/AYS_Validation_Cron.php`

- Runs on WordPress cron schedule
- Processes pending email validations from database
- Updates validation results in `wp_ays_notification_log`
- Won't affect normal email sending if validation is slow

### Disabling Validation

To disable email validation:
1. Go to WordPress Admin → Leads → Settings
2. Leave the API Key field empty
3. System will only perform MX record checking

---

## Support & Maintenance

**For deployment**:
1. Choose your email delivery method (SMTP or mail server)
2. Update `MailTransportFactory::create()` default from 'file_logging' to 'wp_mail'
3. Configure your mail server or external SMTP service
4. Set up email validation API key (optional but recommended)
5. Test thoroughly before going live
6. Monitor `wp_ays_notification_log` table for any failures

**Questions?**
Check the inline code comments in each class for implementation details.
