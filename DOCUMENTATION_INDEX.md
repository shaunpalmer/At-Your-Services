# 📧 Email Notification System - Documentation Index

## 🚀 Start Here (Read First)

### For Quick Answers (5 min read)
**→ Read**: `EMAIL_SYSTEM_QUICKREF.md`
- Current setup summary
- View logs command
- Production checklist
- Troubleshooting quick fixes

### For Understanding the System (15 min read)
**→ Read**: `SYSTEM_SETUP_SUMMARY.md`
- What was built and why
- How everything flows
- Key files and components
- Next steps for production

### For Complete Technical Details (30 min read)
**→ Read**: `EMAIL_SYSTEM_DOCUMENTATION.md`
- Architecture explanation
- Configuration options
- Database schema
- Advanced troubleshooting
- Code flow diagrams

---

## 📁 File Organization

### Documentation (Read These)
```
root/
├── EMAIL_SYSTEM_QUICKREF.md           ⭐ Quick answers
├── SYSTEM_SETUP_SUMMARY.md            ⭐ System overview
├── EMAIL_SYSTEM_DOCUMENTATION.md      ⭐ Full technical guide
└── DOCUMENTATION_INDEX.md             ← You are here
```

### Core Code (Study These)
```
includes/notifications/
├── AYS_Notification_Router.php        Router (has detailed comments)
├── AYS_Notifier.php                   Sends emails (has detailed comments)
├── AYS_Notification_Settings.php      Manages settings
├── Mailer.php                         Mail facade
├── transport/
│   ├── MailTransportFactory.php       ⭐ Choose delivery method
│   ├── FileLoggingTransport.php       ⭐ Current: File logging (has detailed comments)
│   └── WPMailTransport.php            Future: Real email
└── adapters/
    ├── LeadArrayAdapter.php
    └── LeadCPTAdapter.php
```

### Testing & Setup
```
root/
├── test-email-debug.php               Test if system works
├── create-notification-log-table.php  Create DB table
└── mercury-smtp-config.php            Mercury SMTP setup (optional)
```

---

## 🎯 Quick Navigation by Task

### "I need to see what emails were sent"
1. Open: `C:\xampp\htdocs\projectstudios\wp-content\email-log.txt`
2. Or: `Get-Content ...email-log.txt -Tail 50`
3. Or read: `EMAIL_SYSTEM_QUICKREF.md` → View Recent Emails section

### "Emails aren't working"
1. Read: `EMAIL_SYSTEM_QUICKREF.md` → Troubleshooting section
2. Or: `EMAIL_SYSTEM_DOCUMENTATION.md` → Troubleshooting section
3. Run: `test-email-debug.php`
4. Check: `wp_ays_notification_log` table

### "I need to change email templates"
1. Go to: WordPress Admin → Leads → Settings
2. Or read: `EMAIL_SYSTEM_DOCUMENTATION.md` → Configuration → Available Tokens
3. Reference: `EMAIL_SYSTEM_QUICKREF.md` → Settings section

### "I'm preparing for production"
1. Read: `SYSTEM_SETUP_SUMMARY.md` → For Production Deployment
2. Or: `EMAIL_SYSTEM_DOCUMENTATION.md` → Production Setup Options
3. Follow: `EMAIL_SYSTEM_QUICKREF.md` → For Production Deployment checklist

### "I'm getting an error"
1. Check: `EMAIL_SYSTEM_DOCUMENTATION.md` → Troubleshooting → Common Errors
2. Or: `EMAIL_SYSTEM_QUICKREF.md` → Troubleshooting section
3. Search: The specific error message in the documentation files

### "I need to understand the code"
1. Start: `SYSTEM_SETUP_SUMMARY.md` → Key Components table
2. Read: File comments in `includes/notifications/AYS_*.php`
3. Reference: `EMAIL_SYSTEM_DOCUMENTATION.md` → Code Flow Diagram

---

## 🔧 Most Important Files (Bookmark These)

### When Production Email Goes Live
→ `includes/notifications/transport/MailTransportFactory.php`
- Line where you change from 'file_logging' to 'wp_mail'
- Most critical change needed for production

### When You Need to Configure SMTP
→ `wp-content/mu-plugins/mercury-smtp-config.php` (example)
- Copy and modify for your SMTP provider
- Or use: `EMAIL_SYSTEM_DOCUMENTATION.md` → Production Setup Options

### When Troubleshooting
→ `EMAIL_SYSTEM_DOCUMENTATION.md` → Troubleshooting section
- Complete error reference
- Debugging queries
- Common issues

---

## 📊 Documentation Map

```
LEVEL 1: Overview
  ↓
  → SYSTEM_SETUP_SUMMARY.md (Start here for big picture)
  → EMAIL_SYSTEM_QUICKREF.md (Quick facts & commands)

LEVEL 2: Detailed Reference  
  ↓
  → EMAIL_SYSTEM_DOCUMENTATION.md (All technical details)
  → Inline comments in PHP files

LEVEL 3: Code Deep Dive
  ↓
  → Individual .php files in includes/notifications/
  → Database queries in EMAIL_SYSTEM_DOCUMENTATION.md
  → Test scripts (test-email-debug.php)

LEVEL 4: Troubleshooting
  ↓
  → See Troubleshooting sections in all docs
  → Run test-email-debug.php
  → Check database tables
  → Review email-log.txt
```

---

## ⏰ Time Commitments

### First Time Setup (30 min)
- Read: `SYSTEM_SETUP_SUMMARY.md` (10 min)
- Read: `EMAIL_SYSTEM_QUICKREF.md` (5 min)
- View: Email log file (2 min)
- Run: `test-email-debug.php` (3 min)
- Review: Files in `includes/notifications/` (10 min)

### For Production Deploy (1-2 hours)
- Read: `EMAIL_SYSTEM_DOCUMENTATION.md` → Production Setup Options (15 min)
- Configure: Mail server or SMTP (30-45 min)
- Test: Full form submission workflow (15 min)
- Monitor: Database logs (10 min)
- Verify: Everything working (10 min)

### For Troubleshooting (15-30 min)
- Read: Relevant section in `EMAIL_SYSTEM_DOCUMENTATION.md` (10 min)
- Run: `test-email-debug.php` (5 min)
- Check: Database or logs (5-10 min)
- Fix: Issue (5-15 min)

---

## 🔍 Key Concepts to Remember

### The Transport Pattern
You can switch between:
- **File logging** (development) - logs to text file
- **wp_mail()** (production) - sends real emails
- **null transport** (testing) - discards emails

Change in: `MailTransportFactory.php`

### The Settings Flow
1. Settings stored in: `wp_options['ays_notifications_settings']`
2. Managed by: `AYS_Notification_Settings` class
3. UI at: WordPress Admin → Leads → Settings
4. Used by: `AYS_Notifier` class

### The Notification Flow
1. Form submitted → `ays_lead_created` hook fired
2. Router catches hook → calls `AYS_Notifier`
3. Notifier builds email from templates
4. Mailer sends through transport
5. Transport logs/sends email
6. Result logged to database

### Email Tokens
Use `{token_name}` in email templates:
- `{name}` `{email}` `{phone}` `{service}`
- `{booking_date}` `{booking_time}` `{notes}`
- `[all-fields]` for all fields as list

---

## ✅ Current System Status

| Component | Status | Notes |
|-----------|--------|-------|
| Lead capture form | ✅ Working | Form submission → lead created |
| Admin notifications | ✅ Working | Sent via file logging |
| Customer auto-replies | ✅ Working | Sent via file logging |
| Settings UI | ✅ Working | Admin → Leads → Settings |
| Database logging | ✅ Working | wp_ays_notification_log table |
| Email log file | ✅ Working | /wp-content/email-log.txt |
| Class autoloading | ✅ Working | All classes properly registered |
| File logging | ✅ Working | Current transport method |
| wp_mail() transport | ⚠️ Ready | Requires mail server configuration |
| External SMTP | ⚠️ Ready | Requires credentials & mu-plugin |

---

## 🚀 Quick Start Commands

### View Email Logs
```powershell
Get-Content C:\xampp\htdocs\projectstudios\wp-content\email-log.txt -Tail 50
```

### Check Database Logs
```sql
SELECT * FROM wp_ays_notification_log ORDER BY ts DESC LIMIT 20;
```

### Test System
```bash
cd c:\xampp\htdocs\projectstudios\wp-content\plugins\At-Your-Services
php test-email-debug.php
```

### View Settings
```sql
SELECT option_value FROM wp_options 
WHERE option_name = 'ays_notifications_settings';
```

---

## 📞 When in Doubt

1. **Is something broken?** 
   → Check: `EMAIL_SYSTEM_DOCUMENTATION.md` → Troubleshooting

2. **How do I deploy this?**
   → Check: `EMAIL_SYSTEM_QUICKREF.md` → For Production Deployment

3. **What was built and why?**
   → Check: `SYSTEM_SETUP_SUMMARY.md` → What Was Built

4. **I need technical details**
   → Check: `EMAIL_SYSTEM_DOCUMENTATION.md` (entire file)

5. **I need quick answers**
   → Check: `EMAIL_SYSTEM_QUICKREF.md` (concise reference)

---

## 📝 Documentation Version Info

- **Created**: October 18, 2025
- **Last Updated**: October 18, 2025
- **System Status**: Development (File Logging)
- **Current Transport**: FileLoggingTransport
- **Next Phase**: Production SMTP Configuration

---

**You are reading**: `DOCUMENTATION_INDEX.md`
**Purpose**: Navigation guide for all email system documentation
**Recommended Next Read**: `EMAIL_SYSTEM_QUICKREF.md` (5 min)
