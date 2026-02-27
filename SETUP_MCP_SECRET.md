# 🔐 MCP Secret Setup Instructions

**IMPORTANT:** This file contains sensitive information and is ignored by Git.

---

## 🚀 Quick Setup

### Step 1: Copy Example File
```bash
cp .env.mcp.example .env.mcp
```

### Step 2: Generate GitHub Token

1. Go to: https://github.com/settings/tokens
2. Click "Generate new token (classic)" or "Generate new token (fine-grained)"
3. Select scopes:
   - ✅ `repo` (Full control of private repositories)
   - ✅ `workflow` (Update GitHub Action workflows)
   - ✅ `project` (Manage GitHub Projects)
   - ✅ `read:org` (Read organization membership)
   - ✅ `user:email` (Access user email addresses)
4. Click "Generate token"
5. **Copy the token immediately** (you won't see it again!)

### Step 3: Add Token to .env.mcp

Open `.env.mcp` and replace:
```
GITHUB_PERSONAL_ACCESS_TOKEN=github_pat_YOUR_ACTUAL_TOKEN_HERE
```

With your actual token:
```
GITHUB_PERSONAL_ACCESS_TOKEN=github_pat_11ABC...your_real_token
```

### Step 4: Update .agent/mcp-servers.json

The file `.agent/mcp-servers.json` uses environment variable substitution.

**Option A: Use Environment Variable**
```bash
# Set environment variable
export GITHUB_PERSONAL_ACCESS_TOKEN=github_pat_...

# Or add to .env.mcp and source it
source .env.mcp
```

**Option B: Replace Placeholder Directly**
Edit `.agent/mcp-servers.json` and replace:
```json
"GITHUB_PERSONAL_ACCESS_TOKEN": "${GITHUB_PERSONAL_ACCESS_TOKEN}"
```

With:
```json
"GITHUB_PERSONAL_ACCESS_TOKEN": "github_pat_11ABC...your_real_token"
```

⚠️ **WARNING:** If you use Option B, NEVER commit `.agent/mcp-servers.json`!

---

## ✅ Verify Setup

### Test GitHub Connection
```bash
# Check if MCP server can connect
npx -y @modelcontextprotocol/server-github
```

### Test with AI Assistant
Ask AI:
```
"Check GitHub repository status"
```

Expected: Repository info should be displayed.

---

## 🔐 Security Best Practices

### DO ✅
- Store tokens in `.env.mcp` (ignored by Git)
- Use fine-grained tokens with minimal permissions
- Rotate tokens periodically
- Revoke tokens when no longer needed
- Use different tokens for development/production

### DON'T ❌
- Never commit tokens to Git
- Never share tokens publicly
- Never use production tokens in development
- Never hardcode tokens in source code
- Never log tokens to console

---

## 🚨 If Token is Exposed

If you accidentally commit a token:

### 1. Revoke Token Immediately
```
Go to: https://github.com/settings/tokens
Find the token → Click "Delete"
```

### 2. Remove from Git History
```bash
# If just committed, reset
git reset --hard HEAD~1

# If already pushed, contact GitHub support
# and force push after resetting
```

### 3. Generate New Token
Create a new token and update `.env.mcp`

### 4. Scan for Leaks
```bash
# Use GitHub secret scanning
# Check: https://github.com/Aswar12/Antarkanma.my.id/security/secret-scanning
```

---

## 📝 Token Rotation Schedule

| Token Type | Rotation Period | Last Rotated |
|------------|----------------|--------------|
| GitHub PAT | Every 90 days | [Date] |

**Reminder:** Set calendar reminder to rotate tokens!

---

## 🧪 Testing

### Test MCP GitHub
```bash
# Should return repository info
curl -H "Authorization: token YOUR_TOKEN" \
  https://api.github.com/repos/Aswar12/Antarkanma.my.id
```

### Test MySQL Helper
```bash
php db-query.php "users" --json
```

---

## 📚 Related Documentation

- [GitHub MCP Guide](docs/AntarkanMa/github-mcp-project-guide.md)
- [MCP Setup Guide](docs/AntarkanMa/mcp-setup-guide.md)
- [MySQL Setup Guide](docs/AntarkanMa/mysql-setup-guide.md)

---

## 🆘 Troubleshooting

### Error: "Token expired or revoked"
**Solution:** Generate new token and update `.env.mcp`

### Error: "Permission denied"
**Solution:** Check token scopes, add required permissions

### Error: "Repository not found"
**Solution:** Verify repository name and token access

---

**Last Updated:** 27 Februari 2026
**Status:** ✅ Ready for Setup
