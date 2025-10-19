# AYS DB Inspect

Quick ways to query your WordPress dev database from the terminal.

## Prereqs
- MySQL client installed and on PATH
- Or WP-CLI installed (`wp` command)
- DB credentials from `wp-config.php`

## Run with MySQL (PowerShell)

```powershell
# Replace with your user and DB
mysql -u shaun -p projectstudios_dev < utils/db-inspect.sql
```

## Run with WP-CLI (prefix-aware)

```powershell
# From your WordPress root
wp db query < wp-content/plugins/At-Your-Services/utils/db-inspect.sql
```

## What it does
- Shows counts for all AYS tables
- Lists recent invoices with client + service types (via invoice_services bridge)
- Sums payments per invoice
- Flags orphaned rows for quick integrity checks

## Tips
- Export to CSV (MySQL):
```powershell
mysql -u shaun -p projectstudios_dev -e "SELECT * FROM wp_ays_invoices" > invoices.csv
```
- Pretty JSON (WP-CLI):
```powershell
wp db query "SELECT id, inv_number, total FROM wp_ays_invoices" --format=json
```
