# 📧 At Your Services - Email System

## ⚡ In 30 Seconds

✅ **Email system is fully functional and ready for testing**

- Admin gets notified when leads arrive
- Customers get auto-reply emails
- All emails logged to file (development mode)
- Settings UI at: **WordPress Admin → Leads → Settings**
- View logs: `C:\xampp\htdocs\projectstudios\wp-content\email-log.txt`

---

## 📚 Documentation

**Start with this** (pick one):
- **5-min quick reference**: `EMAIL_SYSTEM_QUICKREF.md`
- **15-min system overview**: `SYSTEM_SETUP_SUMMARY.md`
- **Full technical guide**: `EMAIL_SYSTEM_DOCUMENTATION.md`
- **Navigation guide**: `DOCUMENTATION_INDEX.md`

---

## 🔍 View Emails

```powershell
# View last 50 emails
Get-Content C:\xampp\htdocs\projectstudios\wp-content\email-log.txt -Tail 50
```

---

## 🧪 Test System

```bash
cd c:\xampp\htdocs\projectstudios\wp-content\plugins\At-Your-Services
php test-email-debug.php
```

---

## ⚙️ Configure Email Templates

Go to: **WordPress Admin → Leads → Settings**

Use these tokens: `{name}` `{email}` `{phone}` `{service}` `{booking_date}` `{booking_time}` `{notes}` `[all-fields]`

---

## 🚀 For Production

1. Read: `EMAIL_SYSTEM_QUICKREF.md` → For Production Deployment
2. Configure: Mail server or external SMTP
3. Update: `includes/notifications/transport/MailTransportFactory.php` (line ~25)
4. Change: `'file_logging'` → `'wp_mail'`
5. Test: Submit form, verify emails sent

---

## 🐛 Troubleshooting

**Emails not working?**
→ Read: `EMAIL_SYSTEM_DOCUMENTATION.md` → Troubleshooting section

**Need to understand the code?**
→ Read: `SYSTEM_SETUP_SUMMARY.md` → Key Components

**Want deployment steps?**
→ Read: `EMAIL_SYSTEM_QUICKREF.md` → For Production Deployment

---

## 📁 Quick File Reference

```
DOCUMENTATION_INDEX.md              Navigation guide (start here)
EMAIL_SYSTEM_QUICKREF.md           Quick reference card  
SYSTEM_SETUP_SUMMARY.md            System overview
EMAIL_SYSTEM_DOCUMENTATION.md      Full technical guide

includes/notifications/
├── AYS_Notification_Router.php     Hook listener
├── AYS_Notifier.php                Sends emails
├── transport/
│   └── MailTransportFactory.php    ⭐ Production switch here
└── transport/FileLoggingTransport.php  Current: File logging

/wp-content/email-log.txt           Email log file

test-email-debug.php                Test script
```

---

## ✅ What's Working

- ✅ Admin notifications
- ✅ Customer auto-replies  
- ✅ Email templates with tokens
- ✅ Settings management UI
- ✅ Database logging
- ✅ File logging (development)
- ✅ Full documentation

---

## ⚠️ What Needs Production Setup

- ⚠️ Mail server configuration (Postfix, Sendmail, etc.)
- ⚠️ Or external SMTP (Gmail, SendGrid, Mailgun, etc.)
- ⚠️ Switch from file logging to real email sending

---

**Status**: ✅ Development (File Logging Active)
**Next Step**: Choose production email method (see `EMAIL_SYSTEM_QUICKREF.md`)

For more details, see: `DOCUMENTATION_INDEX.md`
