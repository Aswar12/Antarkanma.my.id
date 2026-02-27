# 🐬 MySQL Quick Reference

## Quick Commands

```bash
# List table
php db-query.php "users" --json

# Custom SQL
php db-query.php --sql "SELECT * FROM users LIMIT 5" --json
```

## Test Credentials

| Role | Email | Password |
|------|-------|----------|
| Customer | customer@test.com | customer123 |
| Merchant | koneksirasa@gmail.com | koneksirasa123 |
| Courier | antarkanma@courier.com | kurir12345 |

## Common Queries

```bash
# Users by role
php db-query.php --sql "SELECT roles, COUNT(*) FROM users GROUP BY roles" --json

# Recent transactions
php db-query.php --sql "SELECT * FROM transactions ORDER BY created_at DESC LIMIT 10" --json
```
