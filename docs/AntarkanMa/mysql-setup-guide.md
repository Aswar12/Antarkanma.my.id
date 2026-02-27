# 🐬 MySQL Setup Guide - AntarkanMa

**Status:** ✅ MySQL Helper Created
**Last Updated:** 27 Februari 2026

---

## 📦 OVERVIEW

Custom MySQL helper script for Laravel-based queries.

**Location:** `db-query.php`

---

## 🔧 USAGE

### Quick Query
```bash
php db-query.php "users" --json
php db-query.php "transactions" --json
```

### Custom SQL
```bash
php db-query.php --sql "SELECT COUNT(*) FROM users" --json
php db-query.php --sql "SELECT roles, COUNT(*) FROM users GROUP BY roles" --json
```

---

## 📊 DATABASE STATS

| Metric | Count |
|--------|-------|
| Users | 120 |
| └─ Customers | 106 |
| └─ Merchants | 12 |
| └─ Couriers | 1 |
| └─ Admin | 1 |
| Transactions | 43 |

---

## 🔐 CONNECTION

- **Host:** localhost
- **Database:** antarkanma
- **Username:** root
- **Password:** (empty)

---

**Helper Version:** 1.0
**Status:** ✅ Working
