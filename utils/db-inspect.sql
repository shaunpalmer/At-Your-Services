-- AYS Database Inspection Script
-- Usage examples (PowerShell):
--   mysql -u <user> -p projectstudios_dev < utils/db-inspect.sql
--   wp db query < utils/db-inspect.sql

-- 1) Basic table counts
SELECT 'wp_ays_company_profile' AS table_name, COUNT(*) AS rows FROM wp_ays_company_profile
UNION ALL
SELECT 'wp_ays_clients', COUNT(*) FROM wp_ays_clients
UNION ALL
SELECT 'wp_ays_service_types', COUNT(*) FROM wp_ays_service_types
UNION ALL
SELECT 'wp_ays_items', COUNT(*) FROM wp_ays_items
UNION ALL
SELECT 'wp_ays_invoices', COUNT(*) FROM wp_ays_invoices
UNION ALL
SELECT 'wp_ays_invoice_items', COUNT(*) FROM wp_ays_invoice_items
UNION ALL
SELECT 'wp_ays_payments', COUNT(*) FROM wp_ays_payments
UNION ALL
SELECT 'wp_ays_invoice_services', COUNT(*) FROM wp_ays_invoice_services
UNION ALL
SELECT 'wp_ays_client_services', COUNT(*) FROM wp_ays_client_services
UNION ALL
SELECT 'wp_ays_email_templates', COUNT(*) FROM wp_ays_email_templates
UNION ALL
SELECT 'wp_ays_email_log', COUNT(*) FROM wp_ays_email_log;

-- 2) Recent invoices with client + service types
SELECT 
  i.id AS invoice_id,
  i.inv_number,
  c.name AS client_name,
  GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS services,
  i.total,
  i.status,
  i.created_at
FROM wp_ays_invoices i
LEFT JOIN wp_ays_clients c ON i.client_id = c.id
LEFT JOIN wp_ays_invoice_services isr ON i.id = isr.invoice_id
LEFT JOIN wp_ays_service_types s ON isr.service_type_id = s.id
GROUP BY i.id
ORDER BY i.created_at DESC
LIMIT 20;

-- 3) Payments per invoice
SELECT 
  i.inv_number,
  SUM(p.amount) AS amount_paid,
  COUNT(*) AS payments_count
FROM wp_ays_invoices i
LEFT JOIN wp_ays_payments p ON p.invoice_id = i.id
GROUP BY i.id
ORDER BY i.id DESC
LIMIT 20;

-- 4) Orphan checks (integrity)
-- Invoices referencing missing clients
SELECT i.id, i.inv_number, i.client_id
FROM wp_ays_invoices i
LEFT JOIN wp_ays_clients c ON i.client_id = c.id
WHERE i.client_id IS NOT NULL AND c.id IS NULL
LIMIT 50;

-- Invoice items referencing missing invoices
SELECT it.id, it.invoice_id
FROM wp_ays_invoice_items it
LEFT JOIN wp_ays_invoices i ON it.invoice_id = i.id
WHERE i.id IS NULL
LIMIT 50;

-- Bridge rows referencing missing entities
SELECT isr.id, isr.invoice_id, isr.service_type_id
FROM wp_ays_invoice_services isr
LEFT JOIN wp_ays_invoices i ON isr.invoice_id = i.id
LEFT JOIN wp_ays_service_types s ON isr.service_type_id = s.id
WHERE (i.id IS NULL OR s.id IS NULL)
LIMIT 50;
