# 🧩 MCP SETUP GUIDE - ANTARKANMA

**Last Updated:** 27 Februari 2026
**Status:** ✅ MCP Tools Configured (Secure)

---

## 📦 INSTALLED MCP TOOLS

### 1. MCP GitHub Server
**Package:** `@modelcontextprotocol/server-github`
**Status:** ✅ Installed
**Token:** Stored securely in `.env.mcp`

**Capabilities:**
- Repository management
- Commits, pulls, issues
- Releases management
- File operations

### 2. MCP Filesystem Server
**Package:** `@modelcontextprotocol/server-filesystem`
**Status:** ✅ Installed
**Path:** `C:\laragon\www\Antarkanma`

**Capabilities:**
- Read/write project files
- File search
- Directory operations

### 3. MySQL Custom Tool
**Type:** Custom PHP Script
**Status:** ✅ Created
**Path:** `db-query.php`

**Capabilities:**
- Database queries
- Table inspection
- Data manipulation

---

## 🔧 QUICK START

### Step 1: Setup GitHub Token
```bash
# Copy example file
cp .env.mcp.example .env.mcp

# Edit .env.mcp and add your GitHub token
# See: SETUP_MCP_SECRET.md for detailed instructions
```

### Step 2: Test MySQL
```bash
php db-query.php "users" --json
```

---

## 📖 USAGE EXAMPLES

### MySQL Queries
```bash
# List table
php db-query.php "users" --json

# Custom SQL
php db-query.php --sql "SELECT COUNT(*) FROM transactions" --json
```

### GitHub Operations
AI Assistant can now:
- Create/manage issues
- Create/manage PRs
- Manage branches
- Search code
- Trigger workflows

---

## 🔐 SECURITY

**GitHub token stored in:** `.env.mcp` (ignored by Git)

**See:** [SETUP_MCP_SECRET.md](SETUP_MCP_SECRET.md) for setup instructions.

---

## 📚 Documentation

- [GitHub MCP Project Guide](github-mcp-project-guide.md)
- [MySQL Setup Guide](mysql-setup-guide.md)
- [MySQL Quick Reference](mysql-quick-reference.md)
- [Project Board Setup](project-board-setup.md)

---

**MCP Setup Version:** 1.0
**Last Tested:** 27 Februari 2026
**Status:** ✅ Ready (after token setup)
